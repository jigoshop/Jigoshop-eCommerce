<?php
namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product\Attribute\Multiselect;

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
	private $attributesInheritEnabled = false;
	private $attributesInheritMode = 'all';
	private $attributes = [];
	private $removedAttributesIds = [];

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
		return $this->attributesInheritEnabled;
	}

	public function setAttributesInheritEnabled($attributesInheritEnabled) {
		$this->attributesInheritEnabled = $attributesInheritEnabled;
	}

	public function getAttributesInheritMode() {
		return $this->attributesInheritMode;
	}

	public function setAttributesInheritMode($attributesInheritMode) {
		$this->attributesInheritMode = $attributesInheritMode;
	}

	public function getAttributes() {
		return $this->attributes;
	}

	public function setAttributes(array $attributes) {
		$this->attributes = [];

		foreach($attributes as $attribute) {
			$attribute->setCategoryId($this->getId());

			$this->attributes[] = $attribute;
		}
	}

	public function getRemovedAttributesIds() {
		return $this->removedAttributesIds;
	}

	public function setRemovedAttributesIds($removedAttributesIds) {
		$this->removedAttributesIds = $removedAttributesIds;
	}

	public function toMeta() {
		return [
			'attributesInheritEnabled' => $this->attributesInheritEnabled,
			'attributesInheritMode' => $this->attributesInheritMode,
			'attributes' => $this->attributes,
			'removedAttributesIds' => $this->removedAttributesIds
		];
	}

	public function fromMeta($meta) {
		if(!is_array($meta))
			return false;

		if(isset($meta['attributesInheritEnabled'])) {
			$this->attributesInheritEnabled = $meta['attributesInheritEnabled'];
		}

		if(isset($meta['attributesInheritMode'])) {
			$this->attributesInheritMode = $meta['attributesInheritMode'];
		}

		if(isset($meta['attributes'])) {
			$this->attributes = $meta['attributes'];
		}

		if(isset($meta['removedAttributesIds'])) {
			$this->removedAttributesIds = $meta['removedAttributesIds'];
		}
	}
}