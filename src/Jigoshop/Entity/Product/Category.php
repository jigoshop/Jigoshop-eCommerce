<?php
namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product\Attribute\Multiselect;
use Jigoshop\Helper\Attribute as AttributeHelper;
use Jigoshop\Helper\ProductCategory;
use Jigoshop\Integration;

class Category {
	private $id = 0;
	private $name = '';
	private $slug = '';
	private $description = '';
	private $parentId = 0;
	private $childCategories = [];
	private $level = 0;
	private $count = 0;
	private $thumbnailId = 0;
	private $attributesInheritEnabled = null;
	private $attributesInheritMode = null;
	private $attributes = [];
	private $attributesStates = [];
	private $removedAttributesIds = [];
	private $orderOfAttributes = [];

	private $categoryService = null;
	private $options = null;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function setSlug($slug) {
		$this->slug = $slug;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getParentId() {
		return $this->parentId;
	}

	public function setParentId($parentId) {
		$this->parentId = $parentId;
	}

	public function getChildCategories() {
		if(!is_array($this->childCategories)) {
			return [];
		}

		return $this->childCategories;
	}

	public function setChildCategories($childCategories) {
		$this->childCategories = $childCategories;
	}

	public function getLevel() {
		return $this->level;
	}

	public function setLevel($level) {
		$this->level = $level;
	}

	public function getCount() {
		return $this->count;
	}

	public function setCount($count) {
		$this->count = $count;
	}

	public function getThumbnailId() {
		return $this->thumbnailId;
	}

	public function setThumbnailId($thumbnailId) {
		$this->thumbnailId = $thumbnailId;
	}

	public function getAttributesInheritEnabled() {
		if($this->attributesInheritEnabled === null) {
			$this->getOptions();

			$this->attributesInheritEnabled = $this->options->get('products.categoryAttributes.inheritance.defaultEnabled');
		}

		return $this->attributesInheritEnabled;
	}

	public function setAttributesInheritEnabled($attributesInheritEnabled) {
		$this->attributesInheritEnabled = $attributesInheritEnabled;
	}

	public function getAttributesInheritMode() {
		if($this->attributesInheritMode === null) {
			$this->getOptions();

			$this->attributesInheritMode = $this->options->get('products.categoryAttributes.inheritance.defaultMode');
		}

		return $this->attributesInheritMode;
	}

	public function setAttributesInheritMode($attributesInheritMode) {
		$this->attributesInheritMode = $attributesInheritMode;
	}

	public function getAttributes() {
		return AttributeHelper::sortAttributesByOrder($this->attributes, $this->getOrderOfAttributes());
	}

	public function setAttributes(array $attributes) {
		$this->attributes = [];

		foreach($attributes as $attribute) {
			$attribute->setCategoryId($this->getId());

			$this->attributes[] = $attribute;
		}
	}

	public function getAllAttributes($filterOwnAttributes = false) {
		if($this->categoryService === null) {
			$this->categoryService = Integration::getProductCategoryService();
		}

		$allCategories = $this->categoryService->findAll();

		$categories = ProductCategory::generateCategoryTreeFromIdToTopParent($this->getId(), $allCategories);
		$attributes = [];
		foreach($categories as $category) {
			if($filterOwnAttributes && $category->getId() == $this->getId()) {
				continue;
			}

			foreach($category->getAttributes() as $attribute) {
				if(isset($attributes[$attribute->getId()])) {
					continue;
				}

				$attributes[$attribute->getId()] = $attribute;
			}
		}

		$attributes = array_values($attributes);
		$attributes = AttributeHelper::sortAttributesByOrder($attributes, $this->getOrderOfAttributes());

		return $attributes;
	}

	public function getAttributesStates() {
		return $this->attributesStates;
	}

	public function setAttributesStates($attributesStates) {
		$this->attributesStates = $attributesStates;
	}

	public function getRemovedAttributesIds() {
		return $this->removedAttributesIds;
	}

	public function setRemovedAttributesIds($removedAttributesIds) {
		$this->removedAttributesIds = $removedAttributesIds;
	}

	public function getOrderOfAttributes() {
		return $this->orderOfAttributes;
	}

	public function setOrderOfAttributes($orderOfAttributes) {
		$this->orderOfAttributes = $orderOfAttributes;
	}

	private function getOptions() {
		if($this->options === null) {
			$this->options = Integration::getOptions();
		}
	}

	public function toMeta() {
		return [
			'attributesInheritEnabled' => $this->attributesInheritEnabled,
			'attributesInheritMode' => $this->attributesInheritMode,
			'attributes' => $this->attributes,
			'attributesStates' => $this->attributesStates,
			'removedAttributesIds' => $this->removedAttributesIds,
			'orderOfAttributes' => $this->orderOfAttributes
		];
	}

	public function fromMeta($meta) {
		if(!is_array($meta)) {
			return false;
		}

		if(isset($meta['attributesInheritEnabled'])) {
			$this->attributesInheritEnabled = $meta['attributesInheritEnabled'];
		}

		if(isset($meta['attributesInheritMode'])) {
			$this->attributesInheritMode = $meta['attributesInheritMode'];
		}

		if(isset($meta['attributes'])) {
			$this->attributes = $meta['attributes'];
		}

		if(isset($meta['attributesStates'])) {
			$this->attributesStates = $meta['attributesStates'];
		}

		if(isset($meta['removedAttributesIds'])) {
			$this->removedAttributesIds = $meta['removedAttributesIds'];
		}

		if(isset($meta['orderOfAttributes'])) {
			$this->orderOfAttributes = $meta['orderOfAttributes'];
		}
	}
}
