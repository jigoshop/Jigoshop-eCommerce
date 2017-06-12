<?php

namespace Jigoshop\Service\Cache\Product;

use Jigoshop\Core\Options;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Service\ProductServiceInterface;

/**
 * Simple cache class for Jigoshop products service.
 *
 * @package Jigoshop\Service\Cache\Product
 */
class Simple implements ProductServiceInterface
{
	private $objects = [];
	private $queries = [];
	private $states = [];
	private $reviews = [];
	private $attachments = [];
	private $attributes;
	private $productAttributes = [];
	private $attributesCount;

	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $service;

	public function __construct(ProductServiceInterface $service)
	{
		$this->service = $service;
	}

	/**
	 * Adds new type to managed types.
	 *
	 * @param $type string Unique type name.
	 * @param $class string Class name.
	 * @throws \Jigoshop\Exception When type already exists.
	 */
	public function addType($type, $class)
	{
		$this->service->addType($type, $class);
	}

    /**
     * Creates empty product.
     * 
     * @param $type string The product type to create.
     * 
     * @return \Jigoshop\Entity\Product Created product.
     */
    public function create($type) {
        return $this->service->create($type);
    }

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Product
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
	 * @return Product Item found.
	 */
	public function findForPost($post)
	{
		if (!isset($this->objects[$post->ID])) {
			$this->objects[$post->ID] = $this->service->findForPost($post);
		}

		return $this->objects[$post->ID];
	}

	/**
	 * Finds items by trying to match their name.
	 *
	 * @param $name string Post name to match.
	 * @return array List of matched products.
	 */
	public function findLike($name)
	{
		return $this->service->findLike($name);
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
     * @return int
     */
    public function getProductsCount()
    {
        return $this->service->getProductsCount();
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
		unset($this->thumbnails[$object->getId()]);
		unset($this->productAttributes[$object->getId()]);
		$this->service->save($object);
	}

    /**
     * updates and saves entity with its meta
     *
     * @param EntityInterface $object
     */
	public function updateAndSavePost(EntityInterface $object)
	{
		$this->service->updateAndSavePost($object);
	}

	/**
	 * @param $number int Number of products to find.
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock($number)
	{
		if (!isset($this->queries['out_of_stock'])) {
			$this->queries['out_of_stock'] = $this->service->findOutOfStock($number);
		}

		return $this->queries['out_of_stock'];
	}

	/**
	 * @param $threshold int Threshold where to assume product is low in stock.
	 * @param $number int Number of products to find.
	 * @return array List of products that are low in stock.
	 */
	public function findLowStock($threshold, $number)
	{
		if (!isset($this->queries['low_stock_'.$threshold])) {
			$this->queries['low_stock_'.$threshold] = $this->service->findLowStock($threshold, $number);
		}

		return $this->queries['low_stock_'.$threshold];
	}

    /**
     * @param Product $product Product to find attachments for.
     * @return array List of attachments attached to the product.
     */
    public function getReviews(Product $product)
    {
        if (!isset($this->reviews[$product->getId()])) {
            $this->reviews[$product->getId()] = $this->service->getReviews($product);
        }

        return $this->reviews[$product->getId()];
    }
	/**
	 * @param Product $product Product to find attachments for.
	 * @param string $size Size for images.
	 * @return array List of attachments attached to the product.
	 */
	public function getAttachments(Product $product, $size = Options::IMAGE_THUMBNAIL)
	{
		if (!isset($this->attachments[$product->getId()])) {
			$this->attachments[$product->getId()] = $this->service->getAttachments($product, $size);
		}

		return $this->attachments[$product->getId()];
	}

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the product to be found.
	 * @return Product|Product\Purchasable Item found.
	 */
	public function findForState(array $state)
	{
		// TODO: For simple products state and ID is exactly the same - worth to try to integrate (lower DB queries).
		$key = serialize($state);
		if (!isset($this->states[$key])) {
			$this->states[$key] = $this->service->findForState($state);
		}

		return $this->states[$key];
	}

	/**
	 * Finds and returns list of available attributes.
	 *
	 * @return array List of available product attributes
	 */
	public function findAllAttributes()
	{
		if ($this->attributes === null) {
			$this->attributes = $this->service->findAllAttributes();
		}

		return $this->attributes;
	}

	/**
	 * Finds and returns list of attributes associated with selected product by it's ID.
	 *
	 * @param $productId int Product ID.
	 * @return array List of attributes attached to selected product.
	 */
	public function getAttributes($productId)
	{
		if (!isset($this->productAttributes[$productId])) {
			$this->productAttributes[$productId] = $this->service->getAttributes($productId);
		}

		return $this->productAttributes[$productId];
	}

	/**
	 * Finds attribute for selected ID.
	 * If attribute is not found - returns null.
	 *
	 * @param int $id Attribute ID.
	 * @return Product\Attribute
	 */
	public function getAttribute($id)
	{
		if (!isset($this->attributes[$id])) {
			$this->attributes[$id] = $this->service->getAttribute($id);
		}

		return $this->attributes[$id];
	}

	/**
	 * Creates new attribute for selected type.
	 *
	 * @param int $type Attribute type.
	 * @return Product\Attribute
	 */
	public function createAttribute($type)
	{
		return $this->service->createAttribute($type);
	}

	/**
	 * Saves attribute to database.
	 *
	 * @param Product\Attribute $attribute Attribute to save.
	 * @return \Jigoshop\Entity\Product\Attribute Saved attribute.
	 */
	public function saveAttribute(Product\Attribute $attribute)
	{
		$result = $this->service->saveAttribute($attribute);
		$this->attributes[$attribute->getId()] = $attribute;
		return $result;
	}

	/**
	 * Removes attribute from database.
	 *
	 * @param int $id Attribute ID.
	 */
	public function removeAttribute($id)
	{
		unset($this->attributes[$id]);
		return $this->service->removeAttribute($id);
	}

	/**
	 * Returns unique key for product in the cart.
	 *
	 * @param $item Item Item to get key for.
	 * @return string
	 */
	public function generateItemKey(Item $item)
	{
		return $this->service->generateItemKey($item);
	}

	/**
	 * Finds and returns number of available attributes.
	 *
	 * @return int Number of available product attributes
	 */
	public function countAttributes()
	{
		if (!empty($this->attributes)) {
			return count($this->attributes);
		}

		if ($this->attributesCount === null) {
			$this->attributesCount = $this->service->countAttributes();
		}

		return $this->attributesCount;
	}
}
