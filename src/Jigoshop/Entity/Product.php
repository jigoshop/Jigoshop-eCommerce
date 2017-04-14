<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Product\Attribute;

/**
 * Product class.
 *
 * @package Jigoshop\Entity
 * @author  Amadeusz Starzykiewicz
 */
abstract class Product implements EntityInterface, Product\Taxable, \JsonSerializable
{
    const VISIBILITY_CATALOG = 1;
    const VISIBILITY_SEARCH = 2;
    const VISIBILITY_PUBLIC = 3; // CATALOG | SEARCH
    const VISIBILITY_NONE = 0;

    private $id = 0;
    private $name;
    private $description;
    private $categories;
    private $tags;
    private $sku;
    private $brand;
    private $gtin;
    private $mpn;
    private $taxable;
    private $taxClasses = [];
    /** @var Product\Attributes\Size */
    private $size;

    private $visibility = self::VISIBILITY_PUBLIC;
    private $featured;
    /** @var  Attribute[] */
    private $attributes;
    private $attributeOrder;
    private $attachments;
    private $crossSells = [];
    private $upSells = [];

    protected $dirtyFields = [];

    public function __construct()
    {
        $this->size = new Product\Attributes\Size();
    }

    /**
     * @param int $id New ID for the product.
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->dirtyFields[] = 'id';
    }

    /**
     * @return int Product ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name New product name.
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->dirtyFields[] = 'name';
    }

    /**
     * @return string Product name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description New product description.
     */
    public function setDescription($description)
    {
        $this->description = $description;
        $this->dirtyFields[] = 'description';
    }

    /**
     * @return string Description of the product.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array Categories assigned to the product.
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array Tags assigned to the product.
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $sku New SKU (Stock-Keeping Unit).
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        $this->dirtyFields[] = 'sku';
    }

    /**
     * @return string Product's SKU (Stock-Keeping Unit).
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        $this->dirtyFields[] = 'brand';
    }

    /**
     * @return string
     */
    public function getGtin()
    {
        return $this->gtin;
    }

    /**
     * @param string $gtin
     */
    public function setGtin($gtin)
    {
        $this->gtin = $gtin;
        $this->dirtyFields[] = 'gtin';
    }

    /**
     * @return string
     */
    public function getMpn()
    {
        return $this->mpn;
    }

    /**
     * @param string $mpn
     */
    public function setMpn($mpn)
    {
        $this->mpn = $mpn;
        $this->dirtyFields[] = 'mpn';
    }

    /**
     * @param boolean $featured Whether product is featured.
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;
        $this->dirtyFields[] = 'featured';
    }

    /**
     * @return boolean Is product featured?
     */
    public function isFeatured()
    {
        return $this->featured;
    }

    /**
     * Sets product visibility.
     *
     * Please, use provided constants to set value properly:
     *   * Product::VISIBILITY_CATALOG - visible only in catalog
     *   * Product::VISIBILITY_SEARCH - visible only in search
     *   * Product::VISIBILITY_PUBLIC - visible in search and catalog
     *   * Product::VISIBILITY_NONE - hidden
     *
     * @param int $visibility Product visibility.
     */
    public function setVisibility($visibility)
    {
        $visibility = intval($visibility);

        if (in_array($visibility,
            [self::VISIBILITY_PUBLIC, self::VISIBILITY_SEARCH, self::VISIBILITY_CATALOG, self::VISIBILITY_NONE])) {
            $this->visibility = $visibility;
            $this->dirtyFields[] = 'visibility';
        }
    }

    /**
     * Returns bitwise value of product visibility.
     * Do determine if product is visible in specified type simply check it with "&" bit operator.
     *
     * @return int Current product visibility.
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Returns whether product is visible to any of sources
     *
     * @return boolean Is product visible?
     */
    public function isVisible()
    {
        return $this->visibility && self::VISIBILITY_PUBLIC != 0;
    }

    /**
     * @return string Product type.
     */
    public abstract function getType();

    /**
     * @param $type string Type name.
     *
     * @return bool Is product of specified type?
     */
    public function isType($type)
    {
        return $this->getType() === $type;
    }

    /**
     * Sets product size.
     * Applies `jigoshop\product\set_size` filter to allow plugins to modify size data. When filter returns false size is not modified at all.
     *
     * @param Product\Attributes\Size $size New product size.
     */
    public function setSize(Product\Attributes\Size $size)
    {
        $size = apply_filters('jigoshop\product\set_size', $size, $this);

        if ($size !== false) {
            $this->size = $size;
            $this->dirtyFields[] = 'size';
        }
    }

    /**
     * @return Product\Attributes\Size Product size.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Adds new attribute to the product.
     * If attribute already exists - it is replaced.
     * Calls `jigoshop\product\add_attribute` filter before adding. If filter returns false - attribute is not added.
     *
     * @param Product\Attribute $attribute New attribute for product.
     */
    public function addAttribute(Product\Attribute $attribute)
    {
        $attribute = apply_filters('jigoshop\product\add_attribute', $attribute, $this);

        if ($attribute !== false) {
            $this->attributes[$attribute->getId()] = $attribute;
        }
    }

    /**
     * Removes attribute from the product.
     * Calls `jigoshop\product\delete_attribute` filter before removing. If filter returns false - attribute is not removed.
     *
     * @param Product\Attribute|int $attribute Attribute to remove.
     *
     * @return Product\Attribute|null Removed attribute or null.
     */
    public function removeAttribute($attribute)
    {
        if ($attribute instanceof Product\Attribute) {
            $attribute = $attribute->getId();
        }

        $key = apply_filters('jigoshop\product\delete_attribute', $attribute, $this);

        if ($key !== false) {
            $attribute = $this->attributes[$key];
            unset($this->attributes[$key]);

            return $attribute;
        }

        return null;
    }

    /**
     * @param int $id Attribute ID.
     *
     * @return bool Attribute exists?
     */
    public function hasAttribute($id)
    {
        return isset($this->attributes[$id]);
    }

    /**
     * Returns attribute of the product.
     * If attribute is not found - returns {@code null}.
     *
     * @param $id int Attribute ID.
     *
     * @return Product\Attribute|null Attribute found or null.
     */
    public function getAttribute($id)
    {
        if (!isset($this->attributes[$id])) {
            return null;
        }

        return $this->attributes[$id];
    }

    /**
     * @return Attribute[] List of product attributes.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return Attribute[] List of product attributes.
     */
    public function getVisibleAttributes()
    {
        return array_filter($this->attributes, function ($item) {
            /** @var $item Product\Attribute */
            return $item->isVisible();
        });
    }

    /**
     * @return bool Is this product taxable?
     */
    public function isTaxable()
    {
        return $this->taxable;
    }

    /**
     * Sets the product to be taxable or not.
     *
     * @param $taxable bool New taxable status.
     */
    public function setTaxable($taxable)
    {
        $this->taxable = (bool)$taxable;
        $this->dirtyFields[] = 'taxable';
    }

    /**
     * @param array $tax New tax classes of the product.
     */
    public function setTaxClasses(array $tax)
    {
        $this->taxClasses = $tax;
        $this->dirtyFields[] = 'tax_classes';
    }

    /**
     * @param string $tax New tax class for the product.
     */
    public function addTaxClass($tax)
    {
        $this->taxClasses[] = $tax;
        $this->dirtyFields[] = 'tax_classes';
    }

    /**
     * @return array Tax classes of the product.
     */
    public function getTaxClasses()
    {
        return $this->taxClasses;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @return mixed
     */
    public function getAttributeOrder()
    {
        return $this->attributeOrder;
    }

    /**
     * @param mixed $attributeOrder
     */
    public function setAttributeOrder($attributeOrder)
    {
        $this->attributeOrder = $attributeOrder;
    }

    /**
     * @param string $attribute Attribute name to find.
     *
     * @return int Key in attributes array.
     */
    protected function _findAttribute($attribute)
    {
        return array_search($attribute, array_map(function ($item) {
            /** @var $item \Jigoshop\Entity\Product\Attribute */
            return $item->getId();
        }, $this->attributes));
    }

    /**
     * @return array
     */
    public function getCrossSells()
    {
        return $this->crossSells;
    }

    /**
     * @param array $crossSells
     */
    public function setCrossSells($crossSells)
    {
        $this->crossSells = $crossSells;
        $this->dirtyFields[] = 'cross_sells';
    }

    /**
     * @return array
     */
    public function getUpSells()
    {
        return $this->upSells;
    }

    /**
     * @param array $upSells
     */
    public function setUpSells($upSells)
    {
        $this->upSells = $upSells;
        $this->dirtyFields[] = 'up_sells';
    }

    /**
     * @return array List of fields to update with according values.
     */
    public function getStateToSave()
    {
        $toSave = [];

        foreach ($this->dirtyFields as $field) {
            switch ($field) {
                case 'name':
                    $toSave['name'] = $this->name;
                    break;
                case 'description':
                    $toSave['description'] = $this->description;
                    break;
                case 'sku':
                    $toSave['sku'] = $this->sku;
                    break;
                case 'brand':
                    $toSave['brand'] = $this->brand;
                    break;
                case 'gtin':
                    $toSave['gtin'] = $this->gtin;
                    break;
                case 'mpn':
                    $toSave['mpn'] = $this->mpn;
                    break;
                case 'featured':
                    $toSave['featured'] = $this->featured;
                    break;
                case 'visibility':
                    $toSave['visibility'] = $this->visibility;
                    break;
                case 'type':
                    $toSave['type'] = $this->getType();
                    break;
                case 'is_taxable':
                    $toSave['is_taxable'] = $this->taxable;
                    break;
                case 'tax_classes':
                    $toSave['tax_classes'] = $this->taxClasses;
                    break;
                case 'cross_sells':
                    $toSave['cross_sells'] = $this->crossSells;
                    break;
                case 'up_sells':
                    $toSave['up_sells'] = $this->upSells;
                    break;
            }
        }

        $toSave['size_weight'] = $this->size->getWeight();
        $toSave['size_width'] = $this->size->getWidth();
        $toSave['size_height'] = $this->size->getHeight();
        $toSave['size_length'] = $this->size->getLength();

        $toSave['attributes'] = $this->attributes;
        $toSave['attribute_order'] = $this->attributeOrder;
        $toSave['attachments'] = $this->attachments;

        return $toSave;
    }

    /**
     * @param array $state State to restore entity to.
     */
    public function restoreState(array $state)
    {
        $state = apply_filters('jigoshop\product\restore_state', $state);

        if (isset($state['id'])) {
            $this->id = $state['id'];
        }
        if (isset($state['name'])) {
            $this->name = $state['name'];
        }
        if (isset($state['description'])) {
            $this->description = $state['description'];
        }
        if (isset($state['categories'])) {
            $this->categories = $state['categories'];
        }
        if (isset($state['tags'])) {
            $this->tags = $state['tags'];
        }
        if (isset($state['sku'])) {
            $this->sku = $state['sku'];
        }
        if (isset($state['brand'])) {
            $this->brand = $state['brand'];
        }
        if (isset($state['gtin'])) {
            $this->gtin = $state['gtin'];
        }
        if (isset($state['mpn'])) {
            $this->mpn = $state['mpn'];
        }
        if (isset($state['featured'])) {
            $this->featured = is_numeric($state['featured']) ? (bool)$state['featured'] : $state['featured'] == 'on';
        }
        if (isset($state['visibility'])) {
            $this->visibility = (int)$state['visibility'];
        }
        if (isset($state['is_taxable'])) {
            $this->taxable = is_numeric($state['is_taxable']) ? (bool)$state['is_taxable'] : $state['is_taxable'] == 'on';
        }
        if (isset($state['tax_classes'])) {
            $this->taxClasses = $state['tax_classes'];
        }
        if (isset($state['size_weight'])) {
            $this->size->setWeight($state['size_weight']);
        }
        if (isset($state['size_width'])) {
            $this->size->setWidth($state['size_width']);
        }
        if (isset($state['size_height'])) {
            $this->size->setHeight($state['size_height']);
        }
        if (isset($state['size_length'])) {
            $this->size->setLength($state['size_length']);
        }
        if (isset($state['attributes'])) {
            $this->attributes = $state['attributes'];
        }
        if (isset($state['attribute_order'])) {
            $this->attributeOrder = $state['attribute_order'];
        }
        if (isset($state['attachments'])) {
            $this->attachments = $state['attachments'];
        }
        if (isset($state['cross_sells'])) {
            $this->crossSells = $state['cross_sells'];
        }
        if (isset($state['up_sells'])) {
            $this->upSells = $state['up_sells'];
        }
    }

    /**
     * Marks values provided in the state as dirty.
     *
     * @param array $state Product state.
     */
    public function markAsDirty(array $state)
    {
        $this->dirtyFields[] = 'size';
        $this->dirtyFields = array_merge($this->dirtyFields, array_keys($state));
    }

    /**
     * Returns link for a product.
     *
     * @return bool|string
     */
    public function getLink()
    {
        return get_permalink($this->getId());
    }

    /**
     * @return array Minimal state to identify the product.
     */
    public abstract function getState();

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->getType(),
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'brand' => $this->brand,
            'gtin' => $this->gtin,
            'mpn' => $this->mpn,
            'featured' => $this->featured,
            'visibility' => $this->visibility,
            'is_taxable' => $this->taxable,
            'tax_classes' => $this->taxClasses,
            'size' => $this->size,
            'attributes' => array_values($this->attributes),
            'attribute_order' => $this->attributeOrder,
            'attachments' => \Jigoshop\Helper\Product::getAttachmentsData($this),
            'categories' => array_values(array_map(function($category) {
                return [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'link' => $category['link'],
                ];
            }, $this->categories)),
            'tags' => array_values(array_map(function($tag) {
                return [
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'link' => $tag['link'],
                ];
            }, $this->tags)),
            'link' => $this->getLink(),
            'cross_sells' => $this->crossSells,
            'up_sells' => $this->upSells
        ];
    }
}
