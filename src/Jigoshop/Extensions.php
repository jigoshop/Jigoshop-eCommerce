<?php

namespace Jigoshop;

use Composer\Autoload\ClassLoader;
use Jigoshop\Container\Configurations;
use Jigoshop\Extensions\Extension;
use Jigoshop\Extensions\License;

/**
 * Class Extensions
 * @package Jigoshop;
 * @author Krzysztof Kasowski
 */
class Extensions
{
    /** @var  Extension[] */
    private static $extensions = [];
    /** @var  Extension */
    private static $install;
    /** @var  License */
    private $license;

    /**
     * Extensions constructor.
     */
    public function __construct()
    {
        $this->license = new License();
    }

    /**
     * @param Extension $extension
     */
    public static function register(Extension $extension)
    {
        self::$extensions[get_class($extension)] = $extension;
    }

    /**
     * @return Extension[]
     */
    public static function getExtensions()
    {
        return self::$extensions;
    }

    /**
     * @param Extension $extension
     */
    public static function install(Extension $extension)
    {
        do_action('jigoshop\extensions\install', $extension);
    }

    /**
     * @param Container $container
     * @param ClassLoader $classLoader
     */
    public function init(Container $container, ClassLoader $classLoader)
    {
        foreach (self::$extensions as $extension) {
            if ($this->validate($extension)) {
                $classLoader->addPsr4($extension->getNamespace() . '\\', $extension->getPath());
                $configuration = $this->getConfigurationFromExtension($extension);
                if($configuration) {
                    $container->configurations->add($configuration);
                }
            }
        }
    }

    /**
     * @param Extension $extension
     * @return bool
     */
    public function validate(Extension $extension)
    {
        $errors = [];
        try {
            if ($extension->getPlugin()->getRequiredVersion() && $this->isMinimumVersion($extension->getPlugin()->getRequiredVersion()) == false) {
                $errors[] = sprintf(
                    __('Required Jigoshop version: %s. Current version: %s. Please upgrade.', 'jigoshop-ecommerce'),
                    $extension->getPlugin()->getVersion(),
                    Core::VERSION);
            }
            if ($extension->getPlugin()->getId() && $this->isLicenseEnabled($extension->getPlugin()->getId()) == false) {
                $errors[] = __('The License is not valid. Please enter your <b>Licence Key</b> on the Jigoshop->Manage Licences Menu with your <b>Order email address</b>.  Until then, the plugin will not be enabled for use.',
                    'jigoshop-ecommerce');
            }
            if (!empty($errors)) {
                throw new Exception(join('</li><li>', $errors));
            }
            return true;
        } catch (Exception $e) {
            $this->showError($e->getMessage(), $extension);
            return false;
        }
    }

    /**
     * @param string $version
     * @return bool
     */
    private function isMinimumVersion($version)
    {
        if (version_compare(Core::VERSION, $version, '<')) {
            return false;
        }

        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    private function isLicenseEnabled($id)
    {
        return $this->license->check($id);
    }

    /**
     * @param string $error
     * @param Extension $extension
     */
    private function showError($error, $extension)
    {
        if (is_admin()) {
            add_action('admin_notices', function () use ($error, $extension) {
                $message = sprintf(
                    __('<strong>%s</strong>: There were some errors on plugin activation. See list below:
                        <ul style="padding-left:15px;list-style: initial"><li>%s</li></ul>', 'jigoshop'),
                    $extension->getPlugin()->getName(),
                    $error
                );
                echo '<div class="error"><p>' . $message . '</p></div>';
            });
        }
    }

    private function getConfigurationFromExtension(Extension $extension) {
        $configuration = '\\'.$extension->getNamespace().'\\Configuration';
        if(class_exists($configuration)) {
            return new $configuration();
        }

        return null;
    }

    public function getRoutesFromExtension(Extension $extension)
    {
        $api = '\\'.$extension->getNamespace().'\\Api';
        if(class_exists($api)) {
            return new $api();
        }

        return null;
    }
}