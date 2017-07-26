<?php
namespace Jigoshop\Helper;

class Attribute {
	public static function sortAttributesByOrder(array $attributes, array $order) {
		usort($attributes, function($a1, $a2) use ($order) {
			$index1 = array_search($a1->getId(), $order);
			$index2 = array_search($a2->getId(), $order);

			if($index1 === false || $index2 === false) {
				return 0;
			}

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
}