<?php

namespace Jigoshop\Helper;

use Jigoshop\Core;
use Jigoshop\Entity\Product\Category as Entity;

class ProductCategory
{
	/**
	 * Returns thumbnail data for selected category ID.
	 *
	 * @param int $id Category term ID.
	 *
	 * @return array `image` and `thumbnail_id` fields.
	 */
	public static function getImage($id)
	{
		if (empty($id)) {
			return [
				'image' => \JigoshopInit::getUrl().'/assets/images/placeholder.png',
				'thumbnail_id' => false,
            ];
		}

		$thumbnail = get_metadata(Core::TERMS, $id, 'thumbnail_id', true);
		$image = $thumbnail ? wp_get_attachment_url($thumbnail) : \JigoshopInit::getUrl().'/assets/images/placeholder.png';

		return [
			'image' => $image,
			'thumbnail_id' => $thumbnail,
        ];
	}

	public static function findInTree($id, array $categories) {
		foreach($categories as $category) {
			if($id == $category->getId()) {
				return $category;
			}

			if(!empty($category->getChildCategories())) {
				$result = self::findInTree($id, $category->getChildCategories());
				if($result instanceof Entity) {
					return $result;
				}
			}
		}

		return false;
	}

	public static function generateCategoryTreeFromIdToTopParent($id, array $categories) {
		$category = self::findInTree($id, $categories);
		if(!is_object($category)) {
			return $categories;
		}

		$output = [];
		$output[] = $category;
		if($category->getParentId() == 0) {
			return $output;
		}

		$output = array_merge($output, self::generateCategoryTreeFromIdToTopParent($category->getParentId(), $categories));

		return $output;
	}
}
