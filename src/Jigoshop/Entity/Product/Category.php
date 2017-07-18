<?php
namespace Jigoshop\Entity\Product;

class Category {
	private $id;
	private $label;
	private $slug;
	private $description;
	private $parentId;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
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
}