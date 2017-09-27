<?php

namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Exception;
use Jigoshop\Helper\Attribute as AttributeHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Product attributes admin page.
 *
 * @package Jigoshop\Product\Admin
 * @author  Amadeusz Starzykiewicz
 */
class Attributes implements PageInterface
{
	const NAME = 'jigoshop_product_attributes';

	/** @var Wordpress */
	private $wp;
	/** @var Messages */
	private $messages;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, Messages $messages, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->messages = $messages;
		$this->productService = $productService;

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['edit.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['product_page_'.Attributes::NAME])) {
				return;
			}

			Styles::add('jigoshop.admin.product_attributes', \JigoshopInit::getUrl().'/assets/css/admin/product_attributes.css');
			Scripts::add('jigoshop.admin.product_attributes', \JigoshopInit::getUrl().'/assets/js/admin/product_attributes.js', [
				'jquery',
				'jigoshop.helpers',
				'jquery-ui-sortable'
            ]);
			Scripts::localize('jigoshop.admin.product_attributes', 'jigoshop_admin_product_attributes', [
				'i18n' => [
					'saved' => __('Changes saved.', 'jigoshop-ecommerce'),
					'removed' => __('Attribute has been successfully removed.', 'jigoshop-ecommerce'),
					'option_removed' => __('Attribute option has been successfully removed.', 'jigoshop-ecommerce'),
					'confirm_remove' => __('Are you sure?', 'jigoshop-ecommerce'),
                ],
            ]);
		});

		$wp->addAction('wp_ajax_jigoshop.admin.product_attributes.save', [$this, 'ajaxSaveAttribute']);
		$wp->addAction('wp_ajax_jigoshop.admin.product_attributes.remove', [$this, 'ajaxRemoveAttribute']);
		$wp->addAction('wp_ajax_jigoshop.admin.product_attributes.save_option', [$this, 'ajaxSaveAttributeOption']);
		$wp->addAction('wp_ajax_jigoshop.admin.product_attributes.remove_option', [$this, 'ajaxRemoveAttributeOption']);
	}

	public function ajaxSaveAttribute()
	{
		try {
			$errors = [];
			if (!isset($_POST['label']) || empty($_POST['label'])) {
				$errors[] = __('Attribute label is not set.', 'jigoshop-ecommerce');
			}
			if (!isset($_POST['type']) || !in_array($_POST['type'], array_keys(Attribute::getTypes()))) {
				$errors[] = __('Attribute type is not valid.', 'jigoshop-ecommerce');
			}

			if (!empty($errors)) {
				throw new Exception(join('<br/>', $errors));
			}

			$attribute = $this->productService->createAttribute((int)$_POST['type']);

			if (isset($_POST['id']) && is_numeric($_POST['id'])) {
				$baseAttribute = $this->productService->getAttribute((int)$_POST['id']);
				$attribute->setId($baseAttribute->getId());
				$attribute->setOptions($baseAttribute->getOptions());
			}

			$attribute->setLabel(trim(htmlspecialchars(strip_tags($_POST['label']))));

			if (isset($_POST['slug']) && !empty($_POST['slug'])) {
				$attribute->setSlug(trim(htmlspecialchars(strip_tags($_POST['slug']))));
			} else {
				$attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($attribute->getLabel()));
			}

			if(isset($_POST['optionsOrder']) && is_array($_POST['optionsOrder'])) {
				$attribute->setOptions(AttributeHelper::sortOptionsByOrder($attribute->getOptions(), $_POST['optionsOrder']));
			}

			$this->productService->saveAttribute($attribute);

			echo json_encode([
				'success' => true,
				'html' => Render::get('admin/product_attributes/attribute', [
					'id' => $attribute->getId(),
					'attribute' => $attribute,
					'types' => Attribute::getTypes(),
                ]),
            ]);
		} catch (Exception $e) {
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage(),
            ]);
		}

		exit;
	}

	public function ajaxRemoveAttribute()
	{
		$errors = [];
		if (!isset($_POST['id']) || empty($_POST['id'])) {
			$errors[] = __('Attribute does not exist.', 'jigoshop-ecommerce');
		}

		if (!empty($errors)) {
			echo json_encode([
				'success' => false,
				'error' => join('<br/>', $errors),
            ]);
			exit;
		}

		$this->productService->removeAttribute((int)$_POST['id']);

		echo json_encode([
			'success' => true,
        ]);
		exit;
	}

	public function ajaxSaveAttributeOption()
	{
		$errors = [];
		if (!isset($_POST['attribute_id']) || !is_numeric($_POST['attribute_id'])) {
			$errors[] = __('Respective attribute is not set.', 'jigoshop-ecommerce');
		}
		if (!isset($_POST['label']) || empty($_POST['label'])) {
			$errors[] = __('Option label is not set.', 'jigoshop-ecommerce');
		}

		if (!empty($errors)) {
			echo json_encode([
				'success' => false,
				'error' => join('<br/>', $errors),
            ]);
			exit;
		}

		$attribute = $this->productService->getAttribute((int)$_POST['attribute_id']);
		if (isset($_POST['id'])) {
			$option = $attribute->removeOption($_POST['id']);
		} else {
			$option = new Attribute\Option();
		}

		$option->setLabel(trim(htmlspecialchars(strip_tags($_POST['label']))));

		if (isset($_POST['value']) && !empty($_POST['value'])) {
			$option->setValue(trim(htmlspecialchars(strip_tags($_POST['value']))));
		} else {
			$option->setValue($this->wp->getHelpers()->sanitizeTitle($option->getLabel()));
		}

		$attribute->addOption($option);
		$this->productService->saveAttribute($attribute);

		echo json_encode([
			'success' => true,
			'html' => Render::get('admin/product_attributes/option', ['id' => $attribute->getId(), 'option_id' => $option->getId(), 'option' => $option]),
        ]);
		exit;
	}

	public function ajaxRemoveAttributeOption()
	{
		$errors = [];
		if (!isset($_POST['attribute_id']) || !is_numeric($_POST['attribute_id'])) {
			$errors[] = __('Respective attribute is not set.', 'jigoshop-ecommerce');
		}
		if (!isset($_POST['id']) || empty($_POST['id'])) {
			$errors[] = __('Option does not exist.', 'jigoshop-ecommerce');
		}

		if (!empty($errors)) {
			echo json_encode([
				'success' => false,
				'error' => join('<br/>', $errors),
            ]);
			exit;
		}

		$attribute = $this->productService->getAttribute((int)$_POST['attribute_id']);
		$attribute->removeOption($_POST['id']);
		$this->productService->saveAttribute($attribute);

		echo json_encode([
			'success' => true,
        ]);
		exit;
	}

	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Attributes', 'jigoshop-ecommerce');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return 'products';
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_product_terms';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/product_attributes', [
			'messages' => $this->messages,
			'attributes' => $this->productService->findAllAttributes(),
			'types' => Attribute::getTypes(),
        ]);
	}
}
