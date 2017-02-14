<?php

namespace Jigoshop\Core;

use Jigoshop\Container;
use Jigoshop\Core;
use Jigoshop\Core\Upgrade\Upgrader;
use WPAL\Wordpress;

/**
 * Class Upgrade
 * @package Jigoshop\Core;
 * @author Krzysztof Kasowski
 */
class Upgrade
{
    /** @var  Container */
    private $di;
    /** @var  Wordpress */
    private $wp;

    /**
     * Upgrade constructor.
     * @param Container $di
     * @param Wordpress $wp
     */
    public function __construct(Container $di, Wordpress $wp)
    {
        $this->di = $di;
        $this->wp = $wp;
    }

    public function run()
    {
        $db = $this->wp->getOption('jigoshop_database_version');
        if($db !== false && $db != Installer::DB_VERSION) {
            for ($i = $db; $i < Installer::DB_VERSION; $i++) {
                if ($this->di->tags->exists('jigoshop.upgrade.' . ($i+1))) {
                    $upgraders = $this->di->getTaggedServices('jigoshop.upgrade.' . ($i+1));
                    foreach ($upgraders as $upgrader) {
                        /** @var Upgrader $upgrader */
                        $upgrader->up($this->wp, $this->di);
                    }
                }
            }

            // Flush rules on first Jigoshop init after upgrade.
            $this->wp->updateOption('jigoshop_force_flush_rewrite', 1);
            $this->wp->updateSiteOption('jigoshop_database_version', Installer::DB_VERSION);
        }

        $version = $this->wp->getOption('jigoshop_version');
        if($version == false || $version != Core::VERSION) {
            // Flush rules on first Jigoshop init after upgrade.
            $this->wp->updateOption('jigoshop_force_flush_rewrite', 1);
            $this->wp->updateSiteOption('jigoshop_version', Core::VERSION);
        }
    }
}