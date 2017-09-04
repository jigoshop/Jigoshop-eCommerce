<?php

namespace Jigoshop;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * Class ApiDeprecated
 * @package Jigoshop
 */
class Endpoint
{
	const ENDPOINT = 'jigoshop_endpoint';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Container */
	private $di;

	public function __construct(Wordpress $wp, Container $di)
	{
		$this->wp = $wp;
		$this->di = $di;
	}

	public function run()
	{
		$this->wp->addFilter('query_vars', [$this, 'addQueryVars'], 0);
		$this->wp->addAction('init', [$this, 'addEndpoint'], 1);
		$this->wp->addAction('parse_request', [$this, 'parseRequest'], 0);
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
		$vars[] = self::ENDPOINT;

		return $vars;
	}

	/**
	 * Adds rewrite endpoint for processing Jigoshop APIs
	 */
	public function addEndpoint()
	{
		$this->wp->addRewriteEndpoint(self::ENDPOINT, EP_ALL);
	}

    /**
     * @param \WP_Query $query
     */
	public function parseRequest($query)
	{
        $endpoint = isset($query->query_vars[self::ENDPOINT]) ? $query->query_vars[self::ENDPOINT] : null;
		if (!empty($endpoint)) {
            if ($this->di->services->detailsExists('jigoshop.api.'.$endpoint) || $this->di->services->detailsExists('jigoshop.endpoint.'.$endpoint)) {
                ob_start();
                // Due to backward compatibility.
                if($this->di->services->detailsExists('jigoshop.endpoint.'.$endpoint)) {
                    $processor = $this->di->get('jigoshop.endpoint.'.$endpoint);
                } elseif ($this->di->services->detailsExists('jigoshop.api.'.$endpoint)) {
                    $processor = $this->di->get('jigoshop.api.'.$endpoint);
                }

				if (!($processor instanceof Endpoint\Processable) && !($processor instanceof Api\Processable) ) {
					if (WP_DEBUG) {
						throw new Exception(__('Provided Endpoint is not processable.', 'jigoshop-ecommerce'));
					}

					return;
				}

				$processor->processResponse();
			} else {
				$this->wp->doAction('jigoshop\endpoint\\'.$endpoint);
			}

			exit;
		}
	}
}
