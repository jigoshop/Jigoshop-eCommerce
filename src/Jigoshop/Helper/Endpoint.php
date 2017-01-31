<?php

namespace Jigoshop\Helper;

/**
 * Class Endpoint
 * @package Jigoshop\Helper;
 * @author Krzysztof Kasowski
 */
class Endpoint
{
    /**
     * Returns cleaned and schemed Jigoshop API URL for given API endpoint.
     * Null $forceSsl causes function to determine whether to use SSL based on default shop home URL.
     *
     * @param $value     string API value.
     * @param $permalink string|null Base address to use.
     *
     * @return string Prepared URL.
     */
    public static function getUrl($value, $permalink = null)
    {
        return self::getEndpointUrl(\Jigoshop\Endpoint::ENDPOINT, $value, $permalink);
    }

    /**
     * Returns cleaned and schemed Jigoshop API URL for given API endpoint.
     * Null $forceSsl causes function to determine whether to use SSL based on default shop home URL.
     *
     * @param $endpoint  string Endpoint name.
     * @param $value     string Endpoint value.
     * @param $permalink string|null Base address to use.
     *
     * @return string Prepared URL.
     */
    public static function getEndpointUrl($endpoint, $value = '', $permalink = null)
    {
        if (!$permalink) {
            $permalink = home_url('/');
        }

        if (get_option('permalink_structure')) {
            if (strstr($permalink, '?')) {
                $query_string = '?'.parse_url($permalink, PHP_URL_QUERY);
                $permalink = current(explode('?', $permalink));
            } else {
                $query_string = '';
            }
            $url = trailingslashit($permalink).$endpoint.'/'.$value.$query_string;
        } else {
            $url = add_query_arg($endpoint, $value, $permalink);
        }

        return apply_filters('jigoshop_endpoint_get_url', $url, $endpoint, $value, $permalink);
    }
}