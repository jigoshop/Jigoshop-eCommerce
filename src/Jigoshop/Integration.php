<?php
namespace Jigoshop;

/**
 * Class Integration
 * @package Jigoshop
 * @autor Krzysztof Kasowski
 */
class Integration
{
    /** @var Container  */
    private static $di;
    /** @var  \Composer\Autoload\ClassLoader */
    private static $classLoader;

    /**
     * Integration constructor.
     * @param \Jigoshop\Container $di
     */
    public function __construct(Container $di)
    {
        add_action('jigoshop\page_resolver\before', function (){
            Integration::initializeGateways();
            Integration::initializeShipping();
        });
        add_action('jigoshop\admin\page_resolver\before', function (){
            Integration::initializeGateways();
            Integration::initializeSettings();
        });
    }

    /**
     * Get settings tabs from plugins.
     */
    public static function initializeSettings()
    {
        $service = self::getAdminSettings();
        $tabs = apply_filters('jigoshop\admin\settings', []);

        foreach ($tabs as $tab) {
            $service->addTab($tab);
        }
    }

    /**
     * Get gateways from plugins
     */
    public static function initializeGateways()
    {
        $service = self::getPaymentService();
        $gateways = apply_filters('jigoshop\payment\gateways', []);

        foreach ($gateways as $gateway) {
             $service->addMethod($gateway);
        }
    }

    /**
     * Get shippings methods from plugins.
     */
    public static function initializeShipping()
    {
        $service = self::getShippingService();
        $methods = apply_filters('Jigoshop\shipping\methods', []);

        foreach ($methods as $method) {
            $service->addMethod($method);
        }

    }

    /**
     * @param $service
     *
     * @return object
     */
    public static function getService($service)
    {
        return self::$di->get($service);
    }

    /**
     * @return \Jigoshop\Container
     */
    public static function getContainer()
    {
        return self::$di;
    }

    /**
     * @return \Jigoshop\Service\PaymentServiceInterface
     */
    public static function getPaymentService()
    {
        return self::$di->get('jigoshop.service.payment');
    }

    /**
     * @return \Jigoshop\Service\ShippingServiceInterface
     */
    public static function getShippingService()
    {
        return self::$di->get('jigoshop.service.shipping');
    }

    /**
     * @return \Jigoshop\Admin\Pages
     */
    public static function getAdminPages()
    {
        return self::$di->get('jigoshop.admin.pages');
    }

    /**
     * @return \Jigoshop\Core\Options
     */
    public static function getOptions()
    {
        return self::$di->get('jigoshop.options');
    }

    /**
     * @return \Jigoshop\Entity\Cart
     */
    public static function getCart()
    {
        return self::$di->get('jigoshop.service.cart')->getCurrent();
    }

    /**
     * @return int
     */
    public static function getShippingRate()
    {
        return self::$shippingRate;
    }

    /**
     * @param int $shippingRate
     */
    public static function setShippingRate($shippingRate)
    {
        self::$shippingRate = $shippingRate;
    }

    /**
     * @return \Jigoshop\Service\OrderServiceInterface
     */
    public static function getOrderService()
    {
        return self::$di->get('jigoshop.service.order');
    }

    /**
     * @return \Jigoshop\Service\TaxServiceInterface
     */
    public static function getTaxService()
    {
        return self::$di->get('jigoshop.service.tax');
    }

    /**
     * @return \Jigoshop\Service\ProductServiceInterface
     */
    public static function getProductService()
    {
        return self::$di->get('jigoshop.service.product');
    }

    public static function getProductCategoryService() {
        return self::$di->get('jigoshop.service.product.category');
    }

    public static function getVariableService() {
        return self::$di->get('jigoshop.service.product.variable');
    }

    /**
     * @return \Jigoshop\Service\CartServiceInterface
     */
    public static function getCartService()
    {
        return self::$di->get('jigoshop.service.cart');
    }

    /**
     * @return \Jigoshop\Service\CouponServiceInterface
     */
    public static function getCouponService()
    {
        return self::$di->get('jigoshop.service.coupon');
    }

    /**
     * @return \Jigoshop\Service\CustomerServiceInterface
     */
    public static function getCustomerService()
    {
        return self::$di->get('jigoshop.service.customer');
    }

    /**
     * @return \Jigoshop\Service\Cron
     */
    public static function getCronService() {
        return self::$di->get('jigoshop.service.cron');
    }

    /**
     * @return \Jigoshop\Core
     */
    public static function getCore()
    {
        return self::$di->get('jigoshop');
    }

    /**
     * @return \Jigoshop\Core\Emails
     */
    public static function getEmails()
    {
        return self::$di->get('jigoshop.emails');
    }

    /**
     * @return \Jigoshop\Core\Messages
     */
    public static function getMessages()
    {
        return self::$di->get('jigoshop.messages');
    }

    /**
     * @return \Jigoshop\Admin\Settings
     */
    public static function getAdminSettings()
    {
        return self::$di->get('jigoshop.admin.settings');
    }

    public static function addPsr4Autoload($namespace, $dir)
    {
        self::$classLoader->addPsr4($namespace, [$dir]);
    }

    public static function addComposerFiles($dir)
    {
        if(file_exists($dir.'/vendor/composer/autoload_namespaces.php')) {
            $map = require $dir.'/vendor/composer/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                self::$classLoader->set($namespace, $path);
            }
        }

        if(file_exists($dir.'/vendor/composer/autoload_psr4.php')) {
            $map = require $dir.'/vendor/composer/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                self::$classLoader->setPsr4($namespace, $path);
            }
        }

        if(file_exists($dir.'/vendor/composer/autoload_classmap.php')) {
            $classMap = require $dir.'/vendor/composer/autoload_classmap.php';
            if ($classMap) {
                self::$classLoader->addClassMap($classMap);
            }
        }
    }

    public static function setClassLoader($classLoader)
    {
        self::$classLoader = $classLoader;
    }

    public static function setContainer($container)
    {
        self::$di = $container;
    }
}
