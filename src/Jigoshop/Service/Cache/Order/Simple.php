<?php

namespace Jigoshop\Service\Cache\Order;

use Jigoshop\Entity\Cart;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order;
use Jigoshop\Service\OrderServiceInterface;

/**
 * Simple cache class for Jigoshop orders service.
 *
 * @package Jigoshop\Service\Cache\Order
 */
class Simple implements OrderServiceInterface
{
	private $objects = [];
	private $queries = [];

	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $service;

	public function __construct(OrderServiceInterface $service)
	{
		$this->service = $service;
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Order
	 */
	public function find($id)
	{
		if (!isset($this->objects[$id])) {
			$this->objects[$id] = $this->service->find($id);
		}

		return $this->objects[$id];
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Order Item found.
	 */
	public function findForPost($post)
	{
		if (!isset($this->objects[$post->ID])) {
			$this->objects[$post->ID] = $this->service->findForPost($post);
		}

		return $this->objects[$post->ID];
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		$hash = hash('md5', serialize($query->query_vars));

		if (!isset($this->queries[$hash])) {
			$this->queries[$hash] = $this->service->findByQuery($query);
		}

		return $this->queries[$hash];
	}

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object)
	{
		$this->queries = [];
		$this->objects[$object->getId()] = $object;
		$this->service->save($object);
	}

	/**
	 * Prepares order based on cart.


*
*@param \Jigoshop\Entity\Cart $cart Cart to fetch data from.
	 * @return Order Prepared order.
	 */
	public function createFromCart(Cart $cart)
	{
		return $this->service->createFromCart($cart);
	}

	/**
	 * @param $month int Month to find orders from.
	 * @param $year int Year to find orders from.
	 * @return array List of orders from selected month.
	 */
	public function findFromMonth($month, $year)
	{
		return $this->service->findFromMonth($month, $year);
	}

	/**
	 * @return array List of orders that are too long in Pending status.
	 */
	public function findOldPending()
	{
		return $this->service->findOldPending();
	}

	/**
	 * @return array List of orders that are too long in Processing status.
	 */
	public function findOldProcessing()
	{
		return $this->service->findOldProcessing();
	}

	/**
	 * Finds orders for specified user.
	 *
	 * @param $userId int User ID.
	 * @return array Orders found.
	 */
	public function findForUser($userId)
	{
		return $this->service->findForUser($userId);
	}

	/**
	 * Saves item meta value to database.
	 *
	 * @param $item Order\Item Item of the meta.
	 * @param $meta Order\Item\Meta Meta to save.
	 */
	public function saveItemMeta($item, $meta)
	{
		$this->service->saveItemMeta($item, $meta);
	}

	/**
	 * Adds a note to the order.
	 *
	 * @param $order \Jigoshop\Entity\Order The order.
	 * @param $note string Note text.
	 * @param $private bool Is note private?
	 * @return int Note ID.
	 */
	public function addNote($order, $note, $private = true)
	{
		$this->service->addNote($order, $note, $private);
	}

}
