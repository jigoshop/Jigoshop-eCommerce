<?php

if (!defined('JIGOSHOP_LOGGER')) {
    /**
     * @deprecated 2.0:2.1 use Jigoshop::getLogger() instead
     */
    define('JIGOSHOP_LOGGER', 'jigoshop');
}
if (!defined('JIGOSHOP_LOG_DIR')) {
    /**
     * @deprecated 2.0:2.1 use Jigoshop::getLogDir() instead
     */
    define('JIGOSHOP_LOG_DIR', JIGOSHOP_DIR . '/log');
}

/**
 * Class Jigoshop
 * @author Krzysztof Kasowski
 */
class Jigoshop
{
    /** @var  string  */
    private static $dir;
    /** @var  string  */
    private static $url;
    /** @var  string  */
    private static $baseName;
    /** @var  string  */
    private static $logger;
    /** @var  string  */
    private static $logDir;
    /** @var \Jigoshop\Container */
    private $container;
    /** @var \Jigoshop\Extensions */
    private $extensions;
    /** @var \Composer\Autoload\ClassLoader */
    private $classLoader;

    public function __construct($file)
    {
        require_once __DIR__.'/functions.php';
        $this->setStaticVariables($file);
        $this->classLoader = $this->getClassLoader();
        $this->classLoader->addPsr4('WPAL\\', array(self::getDir().'/vendor/megawebmaster/wpal/WPAL'));
        $this->initLoggers();
        $this->initCache();

        $this->container = new \Jigoshop\Container();
        $this->extensions = new \Jigoshop\Extensions();
        $this->container->getServices()->set('jigoshop.extensions', $this->extensions);
        $this->container->getServices()->set('class_loader', $this->classLoader);
        \Jigoshop\Integration::setClassLoader($this->classLoader);
        \Jigoshop\Integration::setContainer($this->container);

        $this->addConfigurations();
        $this->addCompilers();

        add_filter('admin_footer_text', array($this, 'footer'));
        add_action('admin_bar_menu', array($this, 'toolbar'), 35);
        add_action('jigoshop\extensions\install', array($this, 'installExtension'));
    }

    /**
     * @return string
     */
    public static function getDir()
    {
        return self::$dir;
    }

    /**
     * @return string
     */
    public static function getUrl()
    {
        return self::$url;
    }

    /**
     * @return string
     */
    public static function getBaseName()
    {
        return self::$baseName;
    }

    /**
     * @return string
     */
    public static function getLogger()
    {
        return self::$logger;
    }

    /**
     * @return string
     */
    public static function getLogDir()
    {
        return self::$logDir;
    }

    public function onLoad()
    {
        $this->extensions->init($this->container, $this->classLoader);

        $this->initConfigurations();
        $this->initCompilers();
        $this->initExtensionsTools();
        $this->loadTextDomain();
        $this->loadTextDomain();
        $this->initSession();
        $this->disableRelationLinks();
        $this->rewriteRules();

        add_filter('plugin_action_links_'.self::getBaseName(), array($this, 'pluginLinks'));
    }

    /**
     *
     */
    public function onInit()
    {
        $this->initQueryInterceptor();
        $this->addOptionsToStaticClasses();
        $this->addWpalToStaticClasses();
        $this->initCoreServices();
        $this->container->get('jigoshop.core')->run($this->container);
    }

    /**
     *
     */
    private function initConfigurations()
    {
        foreach($this->container->getConfigurations()->getAll() as $configuration) {
            $configuration->addServices($this->container->getServices());
            $configuration->addTags($this->container->getTags());
            $configuration->addTriggers($this->container->getTriggers());
            $configuration->addFactories($this->container->getFactories());
        }
    }

    /**
     *
     */
    private function initCompilers()
    {
        foreach($this->container->getCompiler()->getAll() as $compiler) {
            $compiler->process($this->container);
        }
    }

    /**
     *
     */
    private function initExtensionsTools()
    {
        do_action('jigoshop\plugins\configure', $this->container);
        $this->container->get('jigoshop.integration');
    }

    /**
     *
     */
    private function loadTextDomain()
    {
        load_textdomain('jigoshop', WP_LANG_DIR.'/jigoshop/'.get_locale().'.mo');
        load_plugin_textdomain('jigoshop', false, basename(self::getDir()).'/languages/');
    }

    /**
     *
     */
    private function initSession()
    {
        if (!session_id()) {
            session_start();
            session_register_shutdown();
        }
        add_action('wp_logout', function (){
            session_destroy();
            session_regenerate_id();
        });
    }

    /**
     *
     */
    private function disableRelationLinks()
    {
        $disable = function ($value){
            if (\Jigoshop\Frontend\Pages::isProduct()) {
                return false;
            }

            return $value;
        };

        add_filter('index_rel_link', $disable);
        add_filter('parent_post_rel_link', $disable);
        add_filter('start_post_rel_link', $disable);
        add_filter('previous_post_rel_link', $disable);
        add_filter('next_post_rel_link', $disable);
    }

    /**
     *
     */
    private function rewriteRules()
    {
        if(get_option('jigoshop_force_flush_rewrite', 1) == 1) {
            add_action('shutdown', function(){
                flush_rewrite_rules();
                update_option('jigoshop_force_flush_rewrite', 2);
            });
        }
    }

    /**
     *
     */
    private function initQueryInterceptor()
    {
        $interceptor = $this->container->get('jigoshop.query.interceptor');

        if (!($interceptor instanceof Jigoshop\Query\Interceptor)) {
            if (is_admin()) {
                add_action('admin_notices', function (){
                    echo '<div class="error"><p>';
                    echo __('Invalid query interceptor instance in Jigoshop. The shop will remain inactive until configured properly.', 'jigoshop');
                    echo '</p></div>';
                });
            }

            \Monolog\Registry::getInstance(self::getLogger())->addEmergency('Invalid query interceptor instance in Jigoshop. Unable to proceed.');

            return;
        }

        $interceptor->run();
    }

    /**
     *
     */
    private function addOptionsToStaticClasses()
    {
        /** @var \Jigoshop\Core\Options $options */
        $options = $this->container->get('jigoshop.options');
        Jigoshop\Helper\Country::setOptions($options);
        Jigoshop\Helper\Currency::setOptions($options);
        Jigoshop\Helper\Product::setOptions($options);
        Jigoshop\Helper\Options::setOptions($options);
        Jigoshop\Helper\Order::setOptions($options);
        Jigoshop\Helper\Address::setOptions($options);
        Jigoshop\Frontend\Pages::setOptions($options);
    }

    /**
     *
     */
    private function addWpalToStaticClasses()
    {
        /** @var \WPAL\Wordpress $wp */
        $wp = $this->container->get('wpal');
        Jigoshop\Entity\Order\Status::setWordpress($wp);
    }

    /**
     *
     */
    private function initCoreServices()
    {
        $this->container->get('jigoshop.core');
        // Initialize post types and roles
        $this->container->get('jigoshop.types');
        //$this->container->get('jigoshop.roles');
        // Initialize Cron
        $this->container->get('jigoshop.cron');
        if(is_admin()) {
            $this->container->get('jigoshop.admin');
            if(defined('DOING_AJAX') && DOING_AJAX) {
                $this->container->get('jigoshop.frontend.page_resolver')->resolve($this->container);
            }
        } else {
            $this->container->get('jigoshop.frontend');
        }
    }

    /**
     * @param $file
     */
    private function setStaticVariables($file)
    {
        self::$dir = dirname($file);
        self::$url = plugins_url('', $file);
        self::$baseName =plugin_basename($file);
        self::$logger = 'jigoshop';
        self::$logDir = self::$dir . '/log';
    }

    /**
     * @return mixed
     */
    private function getClassLoader()
    {
        return require_once(JIGOSHOP_DIR . '/vendor/autoload.php');
    }

    /**
     *
     */
    private function initLoggers()
    {
        $logger = new \Monolog\Logger(self::$logger);
        if (WP_DEBUG) {
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(self::$logDir . '/jigoshop.debug.log',
                \Monolog\Logger::DEBUG));
        }
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(self::$logDir . '/jigoshop.log',
            \Monolog\Logger::WARNING));
        $logger->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());
        $logger->pushProcessor(new \Monolog\Processor\WebProcessor());
        \Monolog\Registry::addLogger($logger);
    }

    /**
     *
     */
    private function initCache()
    {
        \phpFastCache\CacheManager::setDefaultConfig(array(
            'path' => Jigoshop::getLogDir(),
        ));
    }

    /**
     *
     */
    private function addConfigurations()
    {
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\AdminConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\FactoriesConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\MainConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\PagesConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\PaymentConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\ServicesConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\ShippingConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\Admin\MigrationConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\Admin\ReportsConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\Admin\PagesConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\Admin\SettingsConfiguration());
        $this->container->getConfigurations()->add(new Jigoshop\Container\Configurations\Admin\SystemInfoConfiguration());
    }

    /**
     *
     */
    private function addCompilers()
    {
        $this->container->getCompiler()->add(new \Jigoshop\Admin\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Admin\Migration\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Admin\Reports\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Admin\Settings\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Admin\SystemInfo\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Core\Installer\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Core\Types\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Payment\CompilerPass());
        $this->container->getCompiler()->add(new \Jigoshop\Shipping\CompilerPass());
    }

    /**
     * @param $text
     * @return string
     */
    public function footer($text)
    {
        $screen = get_current_screen();

        if (strpos($screen->base, 'jigoshop') === false && strpos($screen->parent_base, 'jigoshop') === false && !in_array($screen->post_type, array('product', 'shop_order'))) {
            return $text;
        }

        return sprintf(
            '<a target="_blank" href="https://www.jigoshop.com/support/">%s</a> | %s',
            __('Contact support', 'jigoshop'),
            str_replace(
                array('[stars]', '[link]', '[/link]'),
                array(
                    '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jigoshop#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
                    '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jigoshop#postform" >',
                    '</a>'
                ),
                __('Add your [stars] on [link]wordpress.org[/link] and keep this plugin essentially free.', 'jigoshop')
            )
        );
    }

    /**
     * Adds Jigoshop items to admin bar.
     */
    public function toolbar()
    {
        /** @var WP_Admin_Bar $wp_admin_bar */
        global $wp_admin_bar;
        $manage_products = current_user_can('manage_jigoshop_products');
        $manage_orders = current_user_can('manage_jigoshop_orders');
        $manage_jigoshop = current_user_can('manage_jigoshop');
        $view_reports = current_user_can('view_jigoshop_reports');

        if (!is_admin() && ($manage_jigoshop || $manage_products || $manage_orders || $view_reports)) {
            $wp_admin_bar->add_node(array(
                'id' => 'jigoshop',
                'title' => __('Jigoshop', 'jigoshop'),
                'href' => $manage_jigoshop ? admin_url('admin.php?page=jigoshop') : '',
                'parent' => false,
                'meta' => array(
                    'class' => 'jigoshop-toolbar'
                ),
            ));

            if ($manage_jigoshop) {
                $wp_admin_bar->add_node(array(
                    'id' => 'jigoshop_dashboard',
                    'title' => __('Dashboard', 'jigoshop'),
                    'parent' => 'jigoshop',
                    'href' => admin_url('admin.php?page=jigoshop'),
                ));
            }

            if ($manage_products) {
                $wp_admin_bar->add_node(array(
                    'id' => 'jigoshop_products',
                    'title' => __('Products', 'jigoshop'),
                    'parent' => 'jigoshop',
                    'href' => admin_url('edit.php?post_type=product'),
                ));
            }

            if ($manage_orders) {
                $wp_admin_bar->add_node(array(
                    'id' => 'jigoshop_orders',
                    'title' => __('Orders', 'jigoshop'),
                    'parent' => 'jigoshop',
                    'href' => admin_url('edit.php?post_type=shop_order'),
                ));
            }

            if ($manage_jigoshop) {
                $wp_admin_bar->add_node(array(
                    'id' => 'jigoshop_settings',
                    'title' => __('Settings', 'jigoshop'),
                    'parent' => 'jigoshop',
                    'href' => admin_url('admin.php?page=jigoshop_settings'),
                ));
            }
        }
    }

    public function pluginLinks($links)
    {
        return array_merge(array(
            '<a href="'.admin_url('admin.php?page=jigoshop_settings').'">'.__('Settings', 'jigoshop').'</a>',
            '<a href="https://www.jigoshop.com/documentation/">'.__('Docs', 'jigoshop').'</a>',
            '<a href="https://www.jigoshop.com/support/">'.__('Support', 'jigoshop').'</a>',
        ), $links);
    }

    /**
     * Installs or updates Jigoshop.
     *
     * @param bool $network_wide
     */
    public function update($network_wide = false)
    {
        // Require upgrade specific files
        require_once(ABSPATH.'/wp-admin/includes/upgrade.php');

        $this->initConfigurations();
        $this->initCompilers();

        /** @var $wp \WPAL\Wordpress */
        $wp = $this->container->get('wpal');
        /** @var $options \Jigoshop\Core\Installer */
        $installer = $this->container->get('jigoshop.installer');

        if (!$network_wide) {
            $installer->install();

            return;
        }

        $blog = $wp->getWPDB()->blogid;
        $ids = $wp->getWPDB()->get_col("SELECT blog_id FROM {$wp->getWPDB()->blogs}");

        foreach ($ids as $id) {
            switch_to_blog($id);
            $installer->install();
        }
        switch_to_blog($blog);
    }

    /**
     * @param \Jigoshop\Extensions\Extension $extension
     */
    public function installExtension($extension)
    {
        $this->classLoader->addPsr4($extension->getNamespace() . '\\', $extension->getPath());

        $configuration = $extension->getConfiguration();
        if($configuration && $configuration instanceof \Jigoshop\Container\Configurations\ConfigurationInterface) {
            $this->container->configurations->add($configuration);
        }

        $installer = $extension->getInstaller();
        if($installer && $installer instanceof \Jigoshop\Extensions\InstallerInterface) {
            $installer->init($this->container);
            $installer->install();
        }
    }
}