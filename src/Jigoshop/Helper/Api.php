<?php

namespace Jigoshop\Helper;

class Api
{
	/**
	 * Returns cleaned and schemed Jigoshop API URL for given API endpoint.
	 * Null $forceSsl causes function to determine whether to use SSL based on default shop home URL.
	 *
	 * @param $value     string API value.
	 * @param $permalink string|null Base address to use.
     * @deprecated
	 *
	 * @return string Prepared URL.
	 */
	public static function getUrl($value, $permalink = null)
	{
		return Endpoint::getUrl($value, $permalink);
	}

	/**
	 * Returns cleaned and schemed Jigoshop API URL for given API endpoint.
	 * Null $forceSsl causes function to determine whether to use SSL based on default shop home URL.
	 *
	 * @param $endpoint  string Endpoint name.
	 * @param $value     string Endpoint value.
	 * @param $permalink string|null Base address to use.
     * @deprecated
     *
	 * @return string Prepared URL.
	 */
	public static function getEndpointUrl($endpoint, $value = '', $permalink = null)
	{
		return Endpoint::getEndpointUrl($endpoint, $value, $permalink);
	}

    public static function getNextPagePath($route, $currentPage, $pageLen, $allResults)
    {
        if($currentPage >= ceil($allResults / $pageLen)) {
            return '';
        }

        if($currentPage < 0) {
            $currentPage = 0;
        }

        return add_query_arg([
            'pagelen' => $pageLen,
            'page' => $currentPage + 1
        ], $route);
	}

    public static function getPreviousPagePath($route, $currentPage, $pageLen, $allResults)
    {
        if($currentPage <= 1) {
            return '';
        }

        if($currentPage > ceil($allResults / $pageLen)) {
            $currentPage = ceil($allResults / $pageLen);
        }

        return add_query_arg([
            'pagelen' => $pageLen,
            'page' => $currentPage - 1
        ], $route);
	}
}
