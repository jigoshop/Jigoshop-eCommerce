<?php
namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\ProductCategory;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\Product\CategoryServiceInterface;
use WPAL\Wordpress;

class Categories implements PageInterface {
	const NAME = 'jigoshop_product_categories';

	private $wp;
	private $messages;
	private $productService;
	private $productCategoryService;

	public function __construct(Wordpress $wp, Messages $messages, ProductServiceInterface $productService, CategoryServiceInterface $categoryService) {
		$this->wp = $wp;
		$this->messages = $messages;
		$this->productService = $productService;
		$this->categoryService = $categoryService;

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['edit.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['product_page_' . self::NAME])) {
				return;
			}

			$wp->wpEnqueueMedia();
            Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', ['jigoshop.admin.product']);
            Styles::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/css/vendors/datepicker.css', ['jigoshop.admin.product']);
			Styles::add('jigoshop.admin.product_categories', \JigoshopInit::getUrl().'/assets/css/admin/product_categories.css');

            Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl() . '/assets/js/vendors/select2.js', ['jquery', 'jigoshop.admin.product']);
            Scripts::add('jigoshop.vendors.bs-switch', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_switch.js', ['jquery']);
            Scripts::add('jigoshop.media', \JigoshopInit::getUrl() . '/assets/js/media.js', ['jquery']);

            Scripts::add('jigoshop.admin.product_categories', \JigoshopInit::getUrl() . '/assets/js/admin/product_categories.js', ['jquery']);

            Scripts::localize('jigoshop.admin.product_categories', 'jigoshop_admin_product_categories_data', [
            	'thumbnailPlaceholder' => ProductCategory::getImage(0)['image'],
            	'lang' => [
            		'categoryRemovalConfirmation' => __('Do you really want to remove this category?', 'jigoshop')
            	]
            ]);
		});	

		$wp->addAction('wp_ajax_jigoshop_product_categories_updateCategory', [$this, 'ajaxUpdateCategory']);
		$wp->addAction('wp_ajax_jigoshop_product_categories_getEditForm', [$this, 'ajaxGetEditForm']);	
		$wp->addAction('wp_ajax_jigoshop_product_categories_removeCategory', [$this, 'ajaxRemoveCategory']);
		$wp->addAction('wp_ajax_jigoshop_product_categories_getAttributes', [$this, 'ajaxGetAttributes']);
	}

	public function getTitle() {
		return __('Categories', 'jigoshop');
	}

	public function getParent() {
		return 'products';
	}

	public function getCapability() {
		return 'manage_product_terms';
	}

	public function getMenuSlug() {
		return self::NAME;
	}

	public function display() {
		$categories = $this->categoryService->findFromParent(0);

		Render::output('admin/product_categories', [
			'messages' => $this->messages,
			'categories' => $this->renderCategories($categories),
			'parentOptions' => $this->getParentOptions($categories),
			'categoryImage' => ProductCategory::getImage(0),
			'attributes' => $this->renderAttributes()
		]);
	}

	private function renderCategories($categories) {
		$render = '';

		foreach($categories as $category) {
			$render .= Render::get('admin/product_categories/category', [
				'category' => $category
			]);

			if(!empty($category->getChildCategories())) {
				$render .= $this->renderCategories($category->getChildCategories());
			}
		}

		return $render;
	}

	private function getParentOptions($categories) {
		$options = [
			0 => __('None', 'jigoshop')
		];

		foreach($categories as $category) {
			$options[$category->getId()] = sprintf('%s%s', str_repeat('- ', $category->getLevel()), $category->getName());

			if(!empty($category->getChildCategories())) {
				$options = $options + $this->getParentOptions($category->getChildCategories());
			}
		}

		return $options;
	}

	private function renderAttributes() {

	}

	public function ajaxUpdateCategory() {
		if(isset($_POST['id']) && $_POST['id'] > 0) {
			$category = $this->categoryService->find($_POST['id']);

			$updatingCategory = 1;
		}
		else {
			$category = $this->categoryService->find(0);

			$updatingCategory = 0;
		}

		$category->setName($_POST['name']);
		$category->setDescription($_POST['description']);
		$category->setSlug($_POST['slug']);
		$category->setParentId($_POST['parentId']);
		$category->setThumbnailId($_POST['thumbnailId']);

		$category->setAttributesInheritEnabled(($_POST['attributesInheritEnabled'] === 'true' || $_POST['attributesInheritEnabled'] === true?true:false));
		$category->setAttributesInheritMode($_POST['attributesInheritMode']);

		$attributes = [];
		if(isset($_POST['attributes']) && is_array($_POST['attributes'])) {
			foreach($_POST['attributes'] as $attributeId => $value) {
				$attribute = $this->productService->getAttribute($attributeId);

				if(!$attribute instanceof Attribute) {
					continue;
				}

				if(isset($_POST['attributesEnabled'][$attribute->getId()]) && ($_POST['attributesEnabled'][$attribute->getId()] === 'true') || $_POST['attributesEnabled'][$attribute->getId()] === true) {
					$attribute->setCategoryEnabled(true);
				}

				$attributes[] = $attribute;
			}
		}
		$category->setAttributes($attributes);

		try {
			$this->categoryService->save($category, true);

			if($updatingCategory) {
				$this->messages->addNotice(__('Category updated.', 'jigoshop'));
			}
			else {	
				$this->messages->addNotice(__('Category added successfully.', 'jigoshop'));
			}

			echo json_encode([
				'status' => 1
			]);
		}
		catch(\Exception $e) {
			echo json_encode([
				'status' => 0,
				'error' => $e->getMessage()
			]);
		}

		exit;
	}

	public function ajaxGetEditForm() {
		if(!isset($_POST['categoryId'])) {
			exit;
		}

		$categories = $this->categoryService->findFromParent(0);
		$category = $this->categoryService->find($_POST['categoryId']);

		echo json_encode([
			'status' => 1,
			'form' => Render::get('admin/product_categories/form', [
				'parentOptions' => $this->getParentOptions($categories),
				'category' => $category,
				'categoryImage' => ProductCategory::getImage($category->getId())
			])
		]);

		exit;
	}

	public function ajaxRemoveCategory() {
		if(!isset($_POST['categoryId'])) {
			exit;
		}

		$this->categoryService->remove($_POST['categoryId']);

		$this->messages->addNotice(__('Category removed successfully.', 'jigoshop'));

		echo json_encode([
			'status' => 1
		]);

		exit;
	}

	public function ajaxGetAttributes() {
		$categories = $this->categoryService->findAll();
		$category = ProductCategory::findInTree($_POST['id'], $categories);

		$allAttributes = $this->productService->findAllAttributes();

		if(is_object($category)) {
			$removedAttributes = $category->getRemovedAttributesIds();
		}
		if(isset($_POST['removedAttributeId']) && $_POST['removedAttributeId']) {
			$removedAttributes[] = $_POST['removedAttributeId'];
		}

		$inheritedAttributes = [];
		if($_POST['inheritEnabled'] === 'true' && $_POST['parentId'] > 0) {
			$parentCategory = ProductCategory::findInTree($_POST['parentId'], $categories);

			if($parentCategory !== false) {
				if($_POST['inheritMode'] == 'direct') {
					$inheritedAttributes = $parentCategory->getAttributes();
				}
				else {
					$inheritedAttributes = $this->getAttributesFromAll($parentCategory, $categories);
				}
			}
		}

		$existingAttributes = [];
		if(isset($_POST['existingAttributes']) && is_array($_POST['existingAttributes'])) {
			$existingAttributes = $_POST['existingAttributes'];
		}

		if(isset($_POST['addedAttributes']) && is_array($_POST['addedAttributes'])) {
			foreach($_POST['addedAttributes'] as $addedAttributeId) {
				if(in_array($addedAttributeId, $removedAttributes)) {
					$removedAttributes = array_diff($removedAttributes, [$addedAttributeId]);
				}

				$existingAttributes[$addedAttributeId] = [
					'enabled' => false,
					'inherited' => false
				];
			}
		}

		foreach($inheritedAttributes as $inheritedAttribute) {
			if(in_array($inheritedAttribute->getId(), $removedAttributes) || isset($existingAttributes[$inheritedAttribute->getId()])) {
				continue;
			} 
		
			$existingAttributes[$inheritedAttribute->getId()] = [
				'enabled' => false,
				'inherited' => true,
				'inheritedFrom' => $inheritedAttribute->getCategoryId()
			];
		}

		if(is_object($category)) {
			foreach($category->getAttributes() as $attribute) {
				if(in_array($attribute->getId(), $removedAttributes)) {
					continue;
				}

				$existingAttributes[$attribute->getId()] = [
					'enabled' => $attribute->getCategoryEnabled(),
					'inherited' => false
				];
			}

			$category->setRemovedAttributesIds($removedAttributes);
			$this->categoryService->save($category);
		}

		$attributesPossibleToAdd = [];
		foreach($allAttributes as $attribute) {
			if(isset($existingAttributes[$attribute->getId()])) {
				continue;
			}

			$attributesPossibleToAdd[$attribute->getId()] = $attribute->getLabel();
		}		

		$attributesRender = '';
		foreach($allAttributes as $attribute) {
			if(!isset($existingAttributes[$attribute->getId()])) {
				continue;
			}

			if($existingAttributes[$attribute->getId()]['enabled'] === 'true' || $existingAttributes[$attribute->getId()]['enabled'] === true) {
				$attribute->setCategoryEnabled(true);
			}

			if(isset($existingAttributes[$attribute->getId()]['inheritedFrom'])) {
				$inheritedFrom = ProductCategory::findInTree($existingAttributes[$attribute->getId()]['inheritedFrom'], $categories)->getName();
			}
			else {
				$inheritedFrom = '-';
			}

			$attributesRender .= Render::get('admin/product_categories/attribute', [
				'attribute' => $attribute,
				'inherited' => $existingAttributes[$attribute->getId()]['inherited'],
				'inheritedFrom' => $inheritedFrom
			]);
		}

		echo json_encode([
			'status' => 1,
			'attributes' => $attributesRender,
			'attributesPossibleToAdd' => $attributesPossibleToAdd
		]);

		exit;
	}

	private function getAttributesFromAll($category, $categories) {
		$attributes = [];
		$attributes = $category->getAttributes();

		if($category->getParentId() > 0) {
			$attributes = array_merge($attributes, $this->getAttributesFromAll(ProductCategory::findInTree($category->getParentId(), $categories), $categories));
		}

		return $attributes;
	}
}