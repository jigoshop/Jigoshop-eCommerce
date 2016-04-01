<?php

namespace WPAL\Wordpress;

class Helpers
{
	public function trailingslashit($string)
	{
		return trailingslashit($string);
	}

	public function untrailingslashit($string)
	{
		return untrailingslashit($string);
	}

	public function maybeUnserialize($original)
	{
		return maybe_unserialize($original);
	}

	public function createNonce($action = -1)
	{
		return wp_create_nonce($action);
	}

	public function verifyNonce($nonce, $action = -1)
	{
		return wp_verify_nonce($nonce, $action);
	}

	public function stripSlashesDeep($value)
	{
		return stripslashes_deep($value);
	}

	public function addQueryArg()
	{
		return call_user_func_array('add_query_arg', func_get_args());
	}

	public function currentTime($type, $gmt = 0)
	{
		return current_time($type, $gmt);
	}

	public function mysql2date($format, $date, $translate = true)
	{
		return mysql2date($format, $date, $translate);
	}

	public function numberFormatI18n($number, $decimals = 0)
	{
		return number_format_i18n($number, $decimals);
	}

	public function dateI18n($dateformatstring, $unixtimestamp = false, $gmt = false)
	{
		return date_i18n($dateformatstring, $unixtimestamp, $gmt);
	}

	public function wptexturize($text)
	{
		return wptexturize($text);
	}

	public function wpautop($pee, $br = true)
	{
		return wpautop($pee, $br);
	}

	public function wpParseArgs($args, $defaults = '')
	{
		return wp_parse_args($args, $defaults);
	}

	public function sanitizeTitle($title, $fallback_title = '', $context = 'save')
	{
		return sanitize_title($title, $fallback_title, $context);
	}

	public function escSql($data)
	{
		return esc_sql($data);
	}

	/**
	 * Parses post body with HTML parser.
	 *
	 * Calls `wptexturize` and `wpautop` functions.
	 *
	 * @param $text string Text to parse.
	 * @return string HTML result.
	 */
	public function parsePostBody($text)
	{
		return $this->wpautop($this->wptexturize($text));
	}
}
