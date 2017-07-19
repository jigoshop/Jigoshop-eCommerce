<?php
namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;
use Jigoshop\Core\Messages;
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
            Scripts::add('jigoshop.admin.product_categories', \JigoshopInit::getUrl() . '/assets/js/admin/product_categories.js', ['jquery']);

            Scripts::localize('jigoshop.admin.product_categories', 'jigoshop_admin_product_categories_lang', [
            	'categoryRemovalConfirmation' => __('Do you really want to remove this category?', 'jigoshop')
            ]);
		});	

		$wp->addAction('wp_ajax_jigoshop_product_categories_updateCategory', [$this, 'ajaxUpdateCategory']);
		$wp->addAction('wp_ajax_jigoshop_product_categories_getEditForm', [$this, 'ajaxGetEditForm']);	
		$wp->addAction('wp_ajax_jigoshop_product_categories_removeCategory', [$this, 'ajaxRemoveCategory']);
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
			'parentOptions' => $this->getParentOptions($categories)
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

		try {
			$this->categoryService->save($category);

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
				'category' => $category
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
}