<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * Compiler pass for loading all post types
 *
 * @package Jigoshop\Core\Types
 * @author  Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject post types and taxonomies into Types instance.
	 *
	 * @param \Jigoshop\Container $container
	 *
	 * @api
	 */
	public function process(Container $container)
	{
		if (!$container->services->detailsExists('jigoshop.types')) {
			return;
		}

		$postTypes = $container->tags->get('jigoshop.type.post');
		foreach ($postTypes as $postType) {
			$container->triggers->add('jigoshop.types', 'jigoshop.types', 'addPostType', [$postType]);
		}

		$taxonomyTypes = $container->tags->get('jigoshop.type.taxonomy');
		foreach ($taxonomyTypes as $taxonomyType) {
			$container->triggers->add('jigoshop.types', 'jigoshop.types', 'addTaxonomy', [$taxonomyType]);
		}
	}
}
