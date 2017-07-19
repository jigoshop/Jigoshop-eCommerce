<?php
namespace Jigoshop\Entity\Product;

class Category {
	private $id = 0;
	private $name = '';
	private $slug = '';
	private $description = '';
	private $parentId = 0;
	private $childCategories = [];
	private $level = 0;
	private $count = 0;

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
}