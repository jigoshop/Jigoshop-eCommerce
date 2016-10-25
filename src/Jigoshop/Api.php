<?php

namespace Jigoshop;

use Jigoshop\Api\Format;
use Jigoshop\Api\InvalidResponseObject;
use Jigoshop\Api\Response\ResponseInterface;
use Jigoshop\Api\ResponseClassNotFound;
use Jigoshop\Api\Routing;
use Jigoshop\Api\Validation;
use Jigoshop\Core\Options;
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
    /** @var Options */
    private $options;
    /** @var Container  */
    private $di;

    /**
     * Api constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param Container $di
     */
    public function __construct(Wordpress $wp, Options $options, Container $di)
    {
        $this->wp = $wp;
        $this->options = $options;
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
            $this->wp->getRewrite()->root.'api/v([0-9])/([0-9a-zA-Z/]+)(\.json|\.xml)?$',
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
            $app = new \Slim\App();
        }
    }
}