<?php

namespace Jigoshop;

use Jigoshop\Api\Format;
use Jigoshop\Api\InvalidResponseObject;
use Jigoshop\Api\Response\ResponseInterface;
use Jigoshop\Api\ResponseClassNotFound;
use Jigoshop\Api\Routing;
use Jigoshop\Api\UnsupportedHttpMethod;
use Jigoshop\Api\Validation;
use WPAL\Wordpress;

/**
 * Class Api
 * @author Krzysztof Kasowski
 */
class Api
{
    const QUERY_URI = 'jigoshop_rest';
    const QUERY_VERSION = 'rest_version';
    const QUERY_FORMAT = 'rest_format';

    /** @var Wordpress */
    private $wp;
    /** @var Container */
    private $di;

    /**
     * Api constructor.
     * @param Wordpress $wp
     * @param Container $di
     */
    public function __construct(Wordpress $wp, Container $di)
    {
        $this->wp = $wp;
        $this->di = $di;
    }

    public function run()
    {
        $this->wp->addFilter('query_vars', array($this, 'addQueryVars'), 0);
        $this->wp->addAction('init', array($this, 'addRewrite'), 1);
        $this->wp->addAction('parse_request', array($this, 'parseRequest'), 0);
    }

    /**
     * Adds Jigoshop API query var to available vars.
     *
     * @param $vars array Current list of variables.
     *
     * @return array Updated list of variables.
     */
    public function addQueryVars($vars)
    {
        $vars[] = self::QUERY_URI;
        $vars[] = self::QUERY_VERSION;
        $vars[] = self::QUERY_FORMAT;

        return $vars;
    }

    /**
     * Adds rewrite endpoint for processing Jigoshop APIs
     */
    public function addRewrite()
    {
        $this->wp->addRewriteRule(
            $this->wp->getRewrite()->root.'API/V([0-9])/([0-9a-zA-Z/]+)(\.json|\.xml)?$',
            sprintf('index.php?%s=$matches[1]&%s=/$matches[2]&%s=$matches[3]', self::QUERY_VERSION, self::QUERY_URI, self::QUERY_FORMAT),
            'top'
        );
    }

    /**
     * @param \WP_Query $query
     */
    public function parseRequest($query)
    {
        $version = isset($query->query_vars[self::QUERY_VERSION]) ? $query->query_vars[self::QUERY_VERSION] : null;
        $uri = isset($query->query_vars[self::QUERY_URI]) ? $query->query_vars[self::QUERY_URI] : null;
        $format = isset($query->query_vars[self::QUERY_FORMAT]) && $query->query_vars[self::QUERY_FORMAT] ? $query->query_vars[self::QUERY_FORMAT] : '.json';
        $format = trim($format, '.');

        if ($version && $uri && $format) {
            $response = '';
            $status = true;
            try {
                $response = $this->route($version, $uri);
            } catch(Api\Routing\NotFound $e) {
                $status = false;
            } catch(UnsupportedHttpMethod $e) {
                $status = false;
                $response = sprintf(__('Unsupported Http Method: %s', 'jigoshop'), $e->getMessage());
            } catch(Api\ResponseClassNotFound $e) {
                $status = false;
                $response = sprintf(__('Response class not found: %s', 'jigoshop'), $e->getMessage());
            } catch(InvalidResponseObject $e) {
                $status = false;
                if($e->getMethodName()) {
                    $response = sprintf(
                        __('Class `%s` does not have method: %s', 'jigoshop'),
                        $e->getMessage(), $e->getMethodName()
                    );
                } else {
                    $response = sprintf(__('Invalid object: %s', 'jigoshop'), $e->getMessage());
                }
            } catch(Exception $e) {
                $status = false;
                $response = $e->getMessage();
            }

            echo $this->getFormattedResponse($format, array('status' => $status, 'data' => $response));
            exit;
        }
    }

    /**
     * @param $version
     * @param $uri
     *
     * @throws Api\RouteNotFound
     *
     * @return string
     */
    private function route($version, $uri)
    {
        $routing = new Routing();
        $validation = new Validation($this->di->get('jigoshop.options'));
        $action = '';
        if($this->getHttpMethod() == 'GET') {
            $action = 'onGet';
        } elseif($this->getHttpMethod() == 'PUT') {
            $action = 'onPut';
        } elseif($this->getHttpMethod() == 'POST') {
            $action = 'onPost';
        } elseif ($this->getHttpMethod() == 'DELETE') {
            $action = 'onDelete';
        }

        if(empty($action)) {
            throw new UnsupportedHttpMethodException($this->getHttpMethod());
        }

        foreach($this->getControllers() as $controller) {
            $controller->$action($routing, $version);
        }

        $result = $routing->match($uri);
        list($className, $methodName) = explode('@', $result['action']);
        $response = $this->getResponseObject($className);
        $this->validateResponseObject($response, $methodName);

        $response->init($this->di);

        return call_user_func_array(array($response, $methodName), $result['params']);
    }

    /**
     * @return Routing\ControllerInterface[]
     */
    private function getControllers()
    {
        /** @var Extensions $extensions */
        $extensions = $this->di->get('jigoshop.extensions');
        $controllers = array(new Routing\Controller());
        foreach($extensions->getExtensions() as $extension) {
            $controllers[] = $extension->getApiController();
        }

        return array_filter($controllers);
    }

    /**
     * @return string
     */
    private function getHttpMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param string $className
     * @return ResponseInterface
     */
    private function getResponseObject($className)
    {
        if(class_exists($className) == false) {
            throw new ResponseClassNotFound($className);
        }

        return new $className();
    }

    /**
     * @param ResponseInterface $object
     * @param $requiredMethod
     */
    private function validateResponseObject($object, $requiredMethod)
    {
        if(($object instanceof ResponseInterface) == false) {
            throw new InvalidResponseObject(get_class($object));
        }
        if(method_exists($object, $requiredMethod) == false) {
            throw new InvalidResponseObject(get_class($object), 0, null, $requiredMethod);
        }
    }

    /**
     * @param $format
     * @param $responseToFormat
     * @return string
     */
    private function getFormattedResponse($format, $responseToFormat)
    {
        $parser = new Format($format);

        return $parser->get($responseToFormat);
    }
}