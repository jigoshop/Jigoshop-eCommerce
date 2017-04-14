<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\ProductCategory;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

class ProductTags
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

		$wp->addAction(sprintf('%s_add_form_fields', Types::PRODUCT_TAG), [
			$this,
			'showThumbnail'
        ]);
		$wp->addAction(sprintf('%s_edit_form_fields', Types::PRODUCT_TAG), [
			$this,
			'showThumbnail'
        ]);
		$wp->addAction('created_term', [$this, 'saveThumbnail'], 10, 3);
		$wp->addAction('edit_term', [$this, 'saveThumbnail'], 10, 3);
		$wp->addAction(sprintf('delete_%s', Types::PRODUCT_TAG), [$this, 'delete']);

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			$wp->wpEnqueueMedia();
			Scripts::add('jigoshop.admin.product_tags', \JigoshopInit::getUrl().'/assets/js/admin/product_tags.js', [
				'jquery',
				'jigoshop.media'
            ]);
			Scripts::localize('jigoshop.admin.product_tags', 'jigoshop_admin_product_tags', [
				'tag_name' => Types::PRODUCT_TAG,
				'placeholder' => \JigoshopInit::getUrl().'/assets/images/placeholder.png',
            ]);

			$wp->doAction('jigoshop\admin\product_tags\assets', $wp);
		});
	}

	public function showThumbnail($term)
	{
		$termId = 0;
		if (is_object($term)) {
			$termId = $term->term_id;
		}

		$image = ProductCategory::getImage($termId);
		Render::output('admin/product_tags/thumbnail', [
			'image' => $image,
        ]);
	}

	public function saveThumbnail($termId, $ttId, $taxonomy)
	{
		if ($taxonomy != Types::PRODUCT_TAG) {
			return;
		}

		$thumbnail = isset($_POST[Types::PRODUCT_TAG.'_thumbnail_id']) ? $_POST[Types::PRODUCT_TAG.'_thumbnail_id'] : false;
		if (!is_numeric($thumbnail)) {
			return;
		}

		update_metadata(Core::TERMS, $termId, 'thumbnail_id', (int)$thumbnail);
	}

	public function delete($termId)
	{
		$termId = (int)$termId;

		if (!$termId) {
			return;
		}

		$wpdb = $this->wp->getWPDB();
		/** @noinspection PhpUndefinedFieldInspection */
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->jigoshop_termmeta} WHERE `jigoshop_term_id` = %d", $termId));
	}
}
