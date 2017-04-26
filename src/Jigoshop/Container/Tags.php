<?php
namespace Jigoshop\Container;

/**
 * Class Tags
 *
 * @package Jigoshop\Container
 * @author  Krzysztof Kasowski
 */
class Tags
{
	/** @var array */
	private $tags = [];

	public function __construct()
	{
	}

	/**
	 * @param string $key
	 * @param string $tag
	 */
	public function add($tag, $key)
	{
		$this->tags[$tag][] = $key;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->tags[$key];
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function exists($key)
	{
		return isset($this->tags[$key]);
	}
}