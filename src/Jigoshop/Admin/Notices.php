<?php

namespace Jigoshop\Admin;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

/**
 * Class Notices
 * @package Jigoshop\Admin;
 * @author Krzysztof Kasowski
 */
class Notices
{
    CONST SLUG = 'notices';

    CONST INFO = 'info';
    CONST WARNING = 'warning';
    CONST ERROR = 'error';

    CONST ONCE = 0;
    CONST DAILY = 1;
    CONST ALL_TIME = 2;
    CONST UNTIL_DISABLE = 3;

    /** @var  Options */
    private $options;
    /** @var array  */
    private $notices = [];

    /**
     * Notices constructor.
     * @param Wordpress $wp
     * @param Options $options
     */
    public function __construct(Wordpress $wp, Options $options)
    {
        $this->options = $options;

        $wp->addAction('admin_notices', [$this, 'display']);
    }

    public function init()
    {
        $this->addNotice(self::INFO, Render::get('admin/notice/thanks_for_using_jigoshop', []), self::UNTIL_DISABLE);
    }

    /**
     * @return array
     */
    public function getNotices()
    {
        return $this->notices;
    }

    /**
     * @param string $type
     * @param string $message
     * @param int $interval
     */
    public function addNotice($type, $message, $interval)
    {
        $this->notices[] = [
            'type' => $type,
            'message' => $message,
            'interval' => $interval
        ];
    }

    /**
     * Display Notices
     */
    public function display()
    {
        foreach($this->notices as $notice) {
            if($this->canDisplay($notice)) {
                Render::output('admin/notice', $notice);
            }
        }
        $this->options->saveOptions();
    }

    private function canDisplay($notice)
    {
        if($notice['interval'] == self::ONCE && $this->options->get(self::SLUG.'.'.md5($notice['message']), false) == false) {
            $this->options->update(self::SLUG.'.'.md5($notice['message']), true);
            return true;
        } elseif($notice['interval'] == self::DAILY && $this->options->get(self::SLUG.'.'.md5($notice['message']), time()) <= time()) {
            $this->options->update(self::SLUG.'.'.md5($notice['message']), time() + 60 * 60 * 24);
            return true;
        } elseif($notice['interval'] == self::ALL_TIME) {
            return true;
        } elseif($notice['interval'] == self::UNTIL_DISABLE && $this->options->get(self::SLUG.'.'.md5($notice['message']), false) == false) {
            return true;
        }

        return false;
    }
}