<?php
namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Attribute\Option;
use Jigoshop\Helper\Attribute as HelperAttribute;
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
            Styles::add('jigoshop.vendors.bs-switch', \JigoshopInit::getUrl() . '/assets/css/vendors/bs_switch.css');
			Styles::add('jigoshop.admin.product_categories', \JigoshopInit::getUrl().'/assets/css/admin/product_categories.css');

			Styles::add('jigoshop.vendors.magnific_popup', \JigoshopInit::getUrl() . '/assets/css/vendors/magnific_popup.css');

            Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl() . '/assets/js/vendors/select2.js', ['jquery', 'jigoshop.admin.product']);
            Scripts::add('jigoshop.vendors.bs-switch', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_switch.js', ['jquery']);
            Scripts::add('jigoshop.media', \JigoshopInit::getUrl() . '/assets/js/media.js', ['jquery']);
			Scripts::add('jigoshop.vendors.magnific_popup', \JigoshopInit::getUrl() . '/assets/js/vendors/magnific_popup.js', ['jquery']);            
            Scripts::add('jigoshop.admin.product_categories', \JigoshopInit::getUrl() . '/assets/js/admin/product_categories.js', [
            	'jquery',
            	'jquery-ui-sortable'
            ]);
            Scripts::localize('jigoshop.admin.product_categories', 'jigoshop_categories', [
                'i18n' => [
                    'yes' => __('Yes', 'jigoshop-ecommerce'),
                    'no' => __('No', 'jigoshop-ecommerce'),
                ],
            ]);

            $localization = [
            	'thumbnailPlaceholder' => ProductCategory::getImage(0)['image'],
            	'lang' => [
            		'categoryRemovalConfirmation' => __('Do you really want to remove this category?', 'jigoshop-ecommerce')
            	]
            ];

            if(isset($_GET['edit']) && $_GET['edit'] > 0) {
            	$localization['forceEdit'] = $_GET['edit'];
            }

            Scripts::localize('jigoshop.admin.product_categories', 'jigoshop_admin_product_categories_data', $localization); 
		});			

		$wp->addAction('wp_ajax_jigoshop_product_categories_updateCategory', [$this, 'ajaxUpdateCategory']);
		$wp->addAction('wp_ajax_jigoshop_product_categories_getEditForm', [$this, 'ajaxGetEditForm']);	
		$wp->addAction('wp_ajax_jigoshop_product_categories_removeCategory', [$this, 'ajaxRemoveCategory']);
		$wp->addAction('wp_ajax_jigoshop_product_categories_getAttributes', [$this, 'ajaxGetAttributes']);
		$wp->addAction('wp_ajax_jigoshop_product_categories_saveAttribute', [$this, 'ajaxSaveAttribute']);

		$this->wp->addAction('admin_print_footer_scripts', function() {
		});
	}

	public function getTitle() {
		return __('Categories', 'jigoshop-ecommerce');
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
			'category' => $this->categoryService->find(0),
			'categories' => $this->renderCategories($categories),
			'parentOptions' => $this->getParentOptions($categories),
			'categoryImage' => ProductCategory::getImage(0),
			'attributes' => $this->renderAttributes(),
			'attributesTypes' => Attribute::getTypes()
		]);
	}

	private function renderCategories($categories, $visibleCategories = []) {
		$render = '';

		foreach($categories as $category) {
			$render .= Render::get('admin/product_categories/category', [
				'category' => $category,
				'visibleCategories' => $visibleCategories
			]);

			if(!empty($category->getChildCategories())) {
				$render .= $this->renderCategories($category->getChildCategories(), $visibleCategories);
			}
		}

		return $render;
	}

	private function getParentOptions($categories) {
		$options = [
			0 => __('None', 'jigoshop-ecommerce')
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
		$attributesStates = [];
		$orderOfAttributes = [];
		if(isset($_POST['attributes']) && is_array($_POST['attributes'])) {
			foreach($_POST['attributes'] as $attributeId => $isAttributedInherited) {
				if(isset($_POST['attributesEnabled'][$attributeId])) {
					if($_POST['attributesEnabled'][$attributeId] === 'true') {
						$attributesStates[$attributeId] = true;
					}
					else {
						$attributesStates[$attributeId] = false;
					}
				}

				$orderOfAttributes[] = $attributeId;

				if($isAttributedInherited) {
					continue;
				}

				$attribute = $this->productService->getAttribute($attributeId);

				if(!$attribute instanceof Attribute) {
					continue;
				}

				$attributes[] = $attribute;
			}
		}

		$category->setAttributes($attributes);
		$category->setAttributesStates($attributesStates);
		$category->setOrderOfAttributes($orderOfAttributes);

		try {
			$this->categoryService->save($category);
            do_action('jigoshop\admin\product\category\save', $category);

            $categories = $this->categoryService->findFromParent(0);
            $categoriesRender = $this->renderCategories($categories, $_POST['visibleCategories']);

			if($updatingCategory) {
				$message = __('Category updated.', 'jigoshop-ecommerce');
			}
			else {	
				$message = __('Category added successfully.', 'jigoshop-ecommerce');
			}

			echo json_encode([
				'status' => 1,
				'id' => $category->getId(),
				'categoriesTable' => $categoriesRender,
				'info' => $message
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
			]),
			'categoryLink' => get_term_link($category->getId(), 'product_category')
		]);

		exit;
	}

	public function ajaxRemoveCategory() {
		if(!isset($_POST['categoryId'])) {
			exit;
		}

		$this->categoryService->remove($_POST['categoryId']);

		$this->messages->addNotice(__('Category removed successfully.', 'jigoshop-ecommerce'));

		echo json_encode([
			'status' => 1
		]);

		exit;
	}

	public function ajaxGetAttributes() {
		$categories = $this->categoryService->findAll();
		$category = ProductCategory::findInTree($_POST['id'], $categories);

		$allAttributes = $this->productService->findAllAttributes();

		$removedAttributes = [];
		if(is_object($category)) {
			$removedAttributes = $category->getRemovedAttributesIds();
		}
		if(isset($_POST['removedAttributeId']) && $_POST['removedAttributeId']) {
			$removedAttributes[] = $_POST['removedAttributeId'];
		}

		$inheritedAttributes = [];
		$inheritedAttributesOrder = [];
		if($_POST['inheritEnabled'] === 'true' && $_POST['parentId'] > 0) {
			$parentCategory = ProductCategory::findInTree($_POST['parentId'], $categories);
			if($parentCategory !== false) {
				if($_POST['inheritMode'] == 'direct') {
					$inheritedAttributes = $parentCategory->getAttributes();
				}
				else {
					$inheritedAttributes = $parentCategory->getAllAttributes();
				}

				$inheritedAttributesOrder = HelperAttribute::getOrderOfAttributes($inheritedAttributes);
			}
		}

		$attributesStates = [];
		if(isset($_POST['attributesStates']) && is_array($_POST['attributesStates'])) {
			foreach($_POST['attributesStates'] as $attributeId => $attributeState) {
				if($attributeState['state'] === 'true') {
					$attributesStates[$attributeId] = true;
				}
				else {
					$attributesStates[$attributeId] = false;
				}
			}
		}
		if(is_object($category)) {
			$attributesStates = $attributesStates + $category->getAttributesStates();
		}

		$existingAttributes = [];
		if(isset($_POST['existingAttributes']) && is_array($_POST['existingAttributes'])) {
			foreach($_POST['existingAttributes'] as $existingAttributeId) {
				$existingAttributes[$existingAttributeId] = [
					'enabled' => $this->getAttributeState($existingAttributeId, $attributesStates),
					'inherited' => false
				];
			}
		}

		if(isset($_POST['addedAttributes']) && is_array($_POST['addedAttributes'])) {
			foreach($_POST['addedAttributes'] as $addedAttributeId) {
				if(in_array($addedAttributeId, $removedAttributes)) {
					$removedAttributes = array_diff($removedAttributes, [$addedAttributeId]);
				}

				$existingAttributes[$addedAttributeId] = [
					'enabled' => true,
					'inherited' => false
				];
			}
		}

		foreach($inheritedAttributes as $inheritedAttribute) {
			if(in_array($inheritedAttribute->getId(), $removedAttributes) || isset($existingAttributes[$inheritedAttribute->getId()])) {
				continue;
			} 
		
			$existingAttributes[$inheritedAttribute->getId()] = [
				'enabled' => $this->getAttributeState($inheritedAttribute->getId(), $attributesStates),
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
					'enabled' => $this->getAttributeState($attribute->getId(), $attributesStates),
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
		if(is_object($category)) {
			$allAttributes = HelperAttribute::sortAttributesByOrder($allAttributes, array_unique(array_merge($category->getOrderOfAttributes(), $inheritedAttributesOrder)));
		}
		else {
			$allAttributes = HelperAttribute::sortAttributesByOrder($allAttributes, $inheritedAttributesOrder);
		}

		foreach($allAttributes as $attribute) {
			if(!isset($existingAttributes[$attribute->getId()])) {
				continue;
			}

			if($existingAttributes[$attribute->getId()]['enabled']) {
				$attributeEnabled = true;
			}
			else {
				$attributeEnabled = false;
			}

			if(isset($existingAttributes[$attribute->getId()]['inheritedFrom'])) {
				$inheritedFrom = ProductCategory::findInTree($existingAttributes[$attribute->getId()]['inheritedFrom'], $categories);
			}
			else {
				$inheritedFrom = null;
			}

			$attributesRender .= Render::get('admin/product_categories/attribute', [
				'attribute' => $attribute,
				'attributeEnabled' => $attributeEnabled,
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

	public function ajaxSaveAttribute() {
		$attribute = null;		
		if(isset($_POST['attributeId']) && $_POST['attributeId']) {
			$attribute = $this->productService->getAttribute($_POST['attributeId']);
		}

		if($attribute === null) {
			$attribute = $this->productService->createAttribute($_POST['type']);
		}

		$attribute->setLabel(trim(htmlspecialchars(strip_tags($_POST['label']))));
		if(isset($_POST['slug']) && $_POST['slug']) {
			$attribute->setSlug(trim(htmlspecialchars(strip_tags($_POST['slug']))));
		}
		else {
			$attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($attribute->getLabel()));
		}

		if(isset($_POST['options']) && is_array($_POST['options'])) { 
			foreach($_POST['options'] as $optionInput) {
				$option = new Option();
				$option->setLabel(trim(htmlspecialchars(strip_tags($optionInput['label']))));
				$option->setValue(trim(htmlspecialchars(strip_tags($optionInput['value']))));

				$attribute->addOption($option);
			}
		}

		$attribute->setVisible(true);

		$attribute = $this->productService->saveAttribute($attribute);

		echo json_encode([
			'status' => 1,
			'attributeId' => $attribute->getId()
		]);

		exit;
	}

	private function getAttributeState($attributeId, $states) {
		if(isset($states[$attributeId])) {
			return $states[$attributeId];
		}

		return true;
	}
}