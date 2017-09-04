<?php
namespace Jigoshop\Service\Product;

interface CategoryServiceInterface {
	public function find($id);

	public function findAll();

	public function findFromParent($parentId);
}