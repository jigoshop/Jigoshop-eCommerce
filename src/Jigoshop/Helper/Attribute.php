<?php
namespace Jigoshop\Helper;

class Attribute {
	public static function sortAttributesByOrder(array $attributes, array $order) {
		usort($attributes, function($a1, $a2) use ($order) {
			$index1 = array_search($a1->getId(), $order);
			$index2 = array_search($a2->getId(), $order);

			if($index1 < $index2) {
				return -1;
			}
			elseif($index1 == $index2) {
				return 0;
			}
			else {
				return 1;
			}
		});

		return $attributes;
	}

	public static function getOrderOfAttributes($attributes) {
		$order = [];
		foreach($attributes as $attribute) {
			$order[] = $attribute->getId();
		}

		return $order;
	}

	public static function sortOptionsByOrder(array $options, array $order) {
		$sortedOptions = [];
		foreach($order as $orderId) {
			foreach($options as $optionId => $data) {
				if($orderId == $optionId) {
					$sortedOptions[$optionId] = $data;

					break;
				}
			}
		}

		return $sortedOptions;
	}
}