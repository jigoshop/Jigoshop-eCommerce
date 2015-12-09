<?php

namespace WpDebugBar;

use DebugBar\DataCollector;
use WPAL\Wordpress;
use DebugBar\StandardDebugBar;
use DebugBar\Storage;

/**
 * Class Debugger
 * @package WpDebugbar
 * @Author Krzysztof Kasowski
 */
class Debugger
{
    /** @var Debugger */
    private static $instance;
    /** @var Wordpress  */
    private $wp;
    /** @var StandardDebugBar  */
    private $debugBar;

    /**
     * @param Wordpress|null $wp
     */
    public static function init(Wordpress $wp = null)
    {
        self::setInstance(new Debugger($wp));
    }

    /**
     * @return null|Debugger
     */
    public static function getInstance()
    {
        if(self::$instance){
            return self::$instance;
        }
        return null;
    }

    /**
     * @param Debugger $instance
     */
    public static function setInstance(Debugger $instance)
    {
        self::$instance = $instance;
    }

    /**
     * @param mixed $message
     * @param string $label
     */
    public static function addMessage($message, $label)
    {
        self::getDebugbar()['messages']->addMessage($message, $label);
    }

    /**
     * @param string $id
     * @param string $label
     */
    public static function startMeasure($id, $label)
    {
        self::getDebugbar()['time']->startMeasure($id, $label);
    }

    /**
     * @param string $id
     */
    public static function stopMeasure($id)
    {
        self::getDebugbar()['time']->stopMeasure($id);
    }

    /**
     * @param \Exception $e
     */
    public static function addException($e)
    {
        self::getDebugbar()['exceptions']->addException($e);
    }

    /**
     *
     */
    public static function initDatabaseDebug()
    {
        define('SAVEQUERIES', true);
        self::getDebugbar()->addCollector(new Collector\Database(self::$instance->wp->getWPDB()));
    }

    /**
     * Debugger constructor.
     * @param Wordpress $wp
     */
    public function __construct(Wordpress $wp = null)
    {
        $this->wp = $wp ? $wp : new Wordpress();
        $this->debugBar = new StandardDebugBar();
        $reflector = new \ReflectionClass($this->debugBar);

        $this->debugBar->getJavascriptRenderer()->setBaseUrl(plugin_dir_url($reflector->getFileName()).'Resources/');
        $this->debugBar->getJavascriptRenderer()->setBasePath($reflector->getFileName().'Resources/');

        // Render debugbar on frontend
        $this->wp->addAction('wp_print_scripts', array($this, 'renderScripts'), 999);
        $this->wp->addAction('wp_footer', array($this, 'renderBar'), 999);
        // Render debugbar on all admin pages
        $this->wp->addAction('admin_print_scripts', array($this, 'renderScripts'), 999);
        $this->wp->addAction('admin_footer', array($this, 'renderBar'), 999);
    }
    /**
     * @return StandardDebugBar
     */
    public function getDebugbar()
    {
        return $this->debugBar;
    }

    /**
     * Render DebugBar headers
     */
    public function renderScripts()
    {
        echo $this->debugBar->getJavascriptRenderer()->renderHead();
    }

    /**
     * Render DebugBar
     */
    public function renderBar()
    {
        echo $this->debugBar->getJavascriptRenderer()->render();
    }

    public function initCollectors()
    {
        //$this->debugBar->addCollector(new )
    }

    /**
     * @param string $pathToDir
     */
    public function setStorage($pathToDir)
    {
        $this->debugBar->setStorage(new Storage\FileStorage($pathToDir));
    }

    /**
     * @param string $autoloadPath
     */
    public function initAjaxDebug($autoloadPath)
    {
        $uploadDir = $this->getUploadDir();

        $this->initOpenHandler($uploadDir['path'], $autoloadPath);
        $this->setStorage($uploadDir['path']);
        $this->debugBar->getJavascriptRenderer()->setOpenHandlerUrl($uploadDir['url'].'/open.php');
        $this->debugBar->sendDataInHeaders(true);
    }

    public function getUploadDir()
    {
        $wpUploadDir = $this->wp->wpUploadDir();
        $dirPath = $wpUploadDir['basedir'].'/debugbar';
        $dirUrl = $wpUploadDir['baseurl'].'/debugbar';
        if(!is_dir($dirPath)){
            echo $dirPath;
            mkdir($dirPath);
        }

        return array(
            'path' => $dirPath,
            'url' => $dirUrl
        );
    }
    /**
     * @param string $pathToDir
     * @param string $autoloadPath
     */
    public function initOpenHandler($pathToDir, $autoloadPath)
    {
        if(!file_exists($pathToDir.'/open.php')) {
            $content = array();
            $content[] = '<?php';
            $content[] = sprintf('require_once(%s);', $autoloadPath);
            $content[] = '$debugBar = new \\DebugBar\\StandardDebugBar();';
            $content[] = '$debugBar->setStorage(new \DebugBar\Storage\FileStorage(__DIR__));';
            $content[] = '$openHandler = new \\DebugBar\\OpenHandler($debugBar);';
            $content[] = '$openHandler->handle();';

            file_put_contents($pathToDir.'/open.php', implode(PHP_EOL, $content));
        }
    }
}