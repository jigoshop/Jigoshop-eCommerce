<?php

namespace Jigoshop\Helper;

use Jigoshop\Admin\Page\ProductCategories;
use Jigoshop\Core\Options as CoreOptions;
use Jigoshop\Core\Types;
use Jigoshop\Entity;

class Product
{
    /** @var CoreOptions */
    private static $options;

    /**
     * @param CoreOptions $options Options object.
     */
    public static function setOptions($options)
    {
        self::$options = $options;
    }

    public static function dimensionsUnit()
    {
        return self::$options->get('products.dimensions_unit');
    }

    public static function weightUnit()
    {
        return self::$options->get('products.weight_unit');
    }

    public static function getButtonType()
    {
        return self::$options->get('shopping.catalog_product_button_type');
    }

    /**
     * Returns options array for select form element based on provided options list.
     *
     * It also allows to add empty item (placeholder for Select2) at the beginning.
     *
     * @param array $options Options to use.
     * @param bool|string $emptyItem Empty item name or false to disable.
     *
     * @return array List of options.
     */
    public static function getSelectOption(array $options, $emptyItem = false)
    {
        $result = [];

        if ($emptyItem !== false) {
            $result = ['' => $emptyItem];
        }

        foreach ($options as $item) {
            /** @var $item Entity\Product\Attribute\Option */
            $result[$item->getId()] = $item->getLabel();
        }

        return $result;
    }

    /**
     * Formats price appropriately to the product type and returns a string.
     *
     * @param Entity\Product $product
     *
     * @return string
     */
    public static function getPriceHtml(Entity\Product $product) {
        $price = null;
        $priceWithTax = null;
        $regularPrice = null;
        $regularPriceWithTax = null;
        $result = '';
        $taxAlreadyIncluded = self::$options->get('tax.prices_entered', 'without_tax');

        switch($product->getType()) {
            case Entity\Product\Simple::TYPE:
            case Entity\Product\Virtual::TYPE:
            case Entity\Product\External::TYPE:
            case Entity\Product\Downloadable::TYPE:
                $price = $product->getPrice();

                if(self::isOnSale($product)) {
                    $regularPrice = $product->getRegularPrice();
                    $regularPrice = ($taxAlreadyIncluded == 'with_tax'?Tax::getPriceWithoutTax($regularPrice, $product->getTaxClasses()):$regularPrice);
                    $regularPriceWithTax = $regularPrice + Tax::getForProduct($regularPrice, $product);

                    $salePrice = $product->getPrice();
                    $salePrice = ($taxAlreadyIncluded == 'with_tax'?Tax::getPriceWithoutTax($salePrice, $product->getTaxClasses()):$salePrice);
                    $salePriceWithTax = $salePrice + Tax::getForProduct($salePrice, $product);

                    $pricesRegular = self::generatePrices($regularPrice, $regularPriceWithTax);
                    $salePrices = self::generatePrices($salePrice, $salePriceWithTax);

                    if(count($pricesRegular) == 2) {
                        if(strpos($product->getSales()->getPrice(), '%') !== false) {
                            $result = sprintf('
                                <del>%s 
                                %s</del>
                                %s 
                                %s
                                <ins>%s</ins>
                                ', $pricesRegular[0], $pricesRegular[1],
                                $salePrices[0], $salePrices[1], sprintf(__('%s off!', 'jigoshop'), $product->getSales()->getPrice()));
                        } 
                        else {
                            $result = sprintf('
                                <del>%s 
                                %s</del>
                                %s 
                                %s
                                ', $pricesRegular[0], $pricesRegular[1],
                                $salePrices[0], $salePrices[1]);
                        }                       
                    }
                    else {
                        $result = $pricesRegular[0];
                    }

                    break;
                }    

                $price = ($taxAlreadyIncluded == 'with_tax'?Tax::getPriceWithoutTax($price, $product->getTaxClasses()):$price);
                $priceWithTax = $price + Tax::getForProduct($price, $product);
                $prices = self::generatePrices($price, $priceWithTax);

                if(count($prices) == 2) {
                    $result = sprintf('%s 
                        (%s)', $prices[0], $prices[1]);
                }
                else {
                    $result = $prices[0];
                }

            break;
            case Entity\Product\Variable::TYPE:
                $price = $product->getLowestPrice();
                $price = ($taxAlreadyIncluded == 'with_tax'?Tax::getPriceWithoutTax($price, $product->getTaxClasses()):$price);
                $priceWithTax = $price + Tax::getForProduct($price, $product); 

                $prices = self::generatePrices($price, $priceWithTax);

                if($price !== '' && $product->getLowestPrice() < $product->getHighestPrice()) {
                    if(count($prices) == 2) {
                        $result = sprintf(__('From: %s 
                            (%s)', 'jigoshop'), $prices[0], $prices[1]);
                    }
                    else {
                        $result = sprintf(__('From: %s', 'jigoshop'), $prices[0]);
                    }
                }
                else {
                    $result = self::formatPrice($prices[0]);
                }

            break;
            default:
                $result = apply_filters('jigoshop\helper\product\get_price', '', $product);
        }

        return apply_filters('jigoshop\helper\product\get_price_html', $result, $price, $product);
    }

    public static function generatePrices($price, $priceWithTax, $cart = 0) {
        if($cart) {
            $showWithTax = self::$options->get('tax.item_prices', 'excluding_tax');
        }
        else {
            $showWithTax = self::$options->get('tax.product_prices', 'excluding_tax');
        }

        $suffixExcludingTax = '';
        $suffixIncludingTax = '';

        if(self::$options->get('tax.show_suffix', false) === 'everywhere') {
            $suffixExcludingTax = self::$options->get('tax.suffix_for_excluded', '');
            $suffixIncludingTax = self::$options->get('tax.suffix_for_included', '');
        }
        if($price === '') {
            return [
                ''
            ];
        }
        if($showWithTax == 'both') {
            if($price == 0.00) {
                return [
                    self::formatPrice(0.00)
                ];
            }
            else {
                return [
                    self::formatPrice(round($price, 2), $suffixExcludingTax),
                    self::formatPrice(round($priceWithTax, 2), $suffixIncludingTax)
                ];
            }
        }
        else {
            if($showWithTax == 'excluding_tax') {
                return [
                    self::formatPrice(round($price, 2), $suffixExcludingTax)
                ];
            }
            elseif($showWithTax == 'including_tax') {
                return [
                    self::formatPrice(round($priceWithTax, 2), $suffixIncludingTax)
                ];
            }
        }
    }

    /**
     * Check whether selected product is on sale.
     *
     * @param Entity\Product $product
     *
     * @return boolean
     */
    public static function isOnSale(Entity\Product $product)
    {
        $status = false;

        switch ($product->getType()) {
            case Entity\Product\Simple::TYPE:
            case Entity\Product\Virtual::TYPE:
            case Entity\Product\External::TYPE:
            case Entity\Product\Downloadable::TYPE:
                /** @var $product Entity\Product\Simple */
                $status = self::isSale($product->getSales());
                break;
            case Entity\Product\Variable::TYPE:
                /** @var $product Entity\Product\Variable */
                $status = array_reduce($product->getVariations(), function($value, $variation) {
                    /** @var $variation Entity\Product\Variable\Variation */
                    return $value || self::isSale($variation->getProduct()->getSales());
                });
                break;
        }

        return apply_filters('jigoshop\helper\product\is_on_sales', $status, $product);
    }

    private static function isSale(Entity\Product\Attributes\Sales $sale)
    {
        $time = time();
        return $sale->isEnabled() &&
            ($sale->getFrom()->getTimestamp() == 0 || $sale->getFrom()->getTimestamp() <= $time) &&
            ($sale->getTo()->getTimestamp() == 0 || $sale->getTo()->getTimestamp() >= $time);
    }

    /**
     * @param $price float Price to format.
     * @param $suffix string
     *
     * @return string Formatted price with currency symbol.
     */
    public static function formatPrice($price, $suffix = '')
    {
        if ($price === 0.00) {
            return __('Free', 'jigoshop');
        }

        if ($price !== '') {
            $formatted = sprintf(Currency::format(), Currency::symbol(), Currency::code(), self::formatNumericPrice($price));

            return $suffix ? sprintf('%s %s', $formatted, $suffix) : $formatted;
        }

        return __('Price not announced.', 'jigoshop');
    }

    /**
     * @param $price float Price to format.
     *
     * @return string Formatted price as numeric value.
     */
    public static function formatNumericPrice($price)
    {
        return number_format($price, Currency::decimals(), Currency::decimalSeparator(),
            Currency::thousandsSeparator());
    }

    /**
     * Formats stock status appropriately to the product type and returns a string.
     *
     * @param Entity\Product $product
     *
     * @return string
     */
    public static function getStock(Entity\Product $product)
    {
        if (!($product instanceof Entity\Product\Purchasable)) {
            return '';
        }

        /**@var $product Entity\Product */
        switch ($product->getType()) {
            case Entity\Product\Simple::TYPE:
            case Entity\Product\Virtual::TYPE:
            case Entity\Product\Downloadable::TYPE:
                /** @var $product Entity\Product\Simple */
                $stock = $product->getStock()->getStatus() == Entity\Product\Attributes\StockStatus::IN_STOCK ?
                    _x('In stock', 'product', 'jigoshop') :
                    '<strong class="attention">' . _x('Out of stock', 'product', 'jigoshop') . '</strong>';

                if (!self::$options->get('products.show_stock') || !$product->getStock()->getManage()) {
                    break;
                }

                $stock = sprintf(_x('%s <strong>(%d available)</strong>', 'product', 'jigoshop'), $stock,
                    $product->getStock()->getStock());
                break;
            default:
                $stock = apply_filters('jigoshop\helper\product\get_stock', '', $product);
                break;
        }

        return apply_filters('jigoshop\helper\product\get_stock\stock', $stock, $product);
    }

    /**
     * Gets thumbnail <img> tag for the product.
     *
     * @param Entity\Product $product
     * @param string $size
	 * @param array $attributes
     *
     * @return string
     */
    public static function getFeaturedImage(Entity\Product $product, $size = CoreOptions::IMAGE_SMALL, $attributes = [])
    {
        if (self::hasFeaturedImage($product)) {
            $thumbnail = apply_filters('jigoshop\helper\product\get_featured_image',
                get_the_post_thumbnail($product->getId(), $size, $attributes), $product, $size);
            if ($thumbnail) {
                return $thumbnail;
            }
        }

        return self::getImagePlaceholder($size);
    }

    /**
     * Checks if product has a thumbnail.
     *
     * @param Entity\Product $product
     *
     * @return boolean
     */
    public static function hasFeaturedImage(Entity\Product $product)
    {
        return has_post_thumbnail($product->getId());
    }

    /**
     * Gets placeholder <img> tag for products.
     *
     * @param string $size
     *
     * @return string
     */
    public static function getImagePlaceholder($size = CoreOptions::IMAGE_SMALL)
    {
        $size = self::getImageSize($size);

        return '<img src="' . \JigoshopInit::getUrl() . '/assets/images/placeholder.png" alt="" width="' . $size['width'] . '" height="' . $size['height'] . '" />';
    }

    /**
     * Returns width and height for images of given size.
     *
     * @param $size string Size name to fetch.
     *
     * @return array Width and height values.
     */
    public static function getImageSize($size)
    {
        $width = 70;
        $height = 70;

        global $_wp_additional_image_sizes;
        if (isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])) {
            $width = intval($_wp_additional_image_sizes[$size]['width']);
            $height = intval($_wp_additional_image_sizes[$size]['height']);
        }

        return ['width' => $width, 'height' => $height];
    }

    /**
     * Formats stock status appropriately to the product type and returns a string.
     *
     * @param Entity\Product $product
     *
     * @return string
     */
    public static function isFeatured(Entity\Product $product)
    {
        return sprintf(
            '<a href="#" data-id="%d" class="product-featured"><span class="glyphicon %s" aria-hidden="true"></span> <span class="sr-only">%s</span></a>',
            $product->getId(),
            $product->isFeatured() ? 'glyphicon-star' : 'glyphicon-star-empty',
            $product->isFeatured() ? __('Yes', 'jigoshop') : __('No', 'jigoshop')
        );
    }

    /**
     * Prints add to cart form for product list.
     *
     * @param $product  \Jigoshop\Entity\Product Product to display.
     * @param $template string Template base to use.
     */
    public static function printAddToCartForm($product, $template)
    {
        do_action('jigoshop\helper\product\cart_form\before', $product, $template);
        $type = apply_filters('jigoshop\helper\product\cart_form\type', $product->getType(), $product, $template);

        $buttonType = $template == 'list' ? self::getButtonType() : 'add_to_cart';
        if ($product instanceof Entity\Product\Purchasable) {
            $price = $product->getRegularPrice();
            if ($price === '') {
                if($template == 'list') {
                    $buttonType = 'view_product';
                } else {
                    return;
                }
            }
        }

        if ($buttonType == 'add_to_cart') {
            self::renderAddToCartForm($type, $product, $template);
        } elseif ($buttonType == 'view_product') {
            self::renderViewProductButton($product);
        }

        do_action('jigoshop\helper\product\cart_form\after', $product, $template);
    }

    private static function renderAddToCartForm($type, $product, $template)
    {
        switch ($type) {
            case Entity\Product\Simple::TYPE:
                Render::output("shop/{$template}/cart/simple", ['product' => $product]);
                break;
            case Entity\Product\Downloadable::TYPE:
                Render::output("shop/{$template}/cart/downloadable", ['product' => $product]);
                break;
            case Entity\Product\External::TYPE:
                Render::output("shop/{$template}/cart/external", ['product' => $product]);
                break;
            case Entity\Product\Virtual::TYPE:
                Render::output("shop/{$template}/cart/virtual", ['product' => $product]);
                break;
            case Entity\Product\Variable::TYPE:
                /** @var $product Entity\Product\Variable */
                $price = $product->getLowestPrice();
                if (empty($price)) {
                    return;
                }

                Render::output("shop/{$template}/cart/variable", ['product' => $product]);
                break;
            default:
                do_action('jigoshop\helper\product\print_cart_form', $type, $product, $template);
        }
    }

    private static function renderViewProductButton($product)
    {
        Render::output("shop/list/cart/default", ['product' => $product]);
    }

    /**
     * Returns HTML for product data.
     *
     * Calls `jigoshop\helper\product\item_data` filter with current data and order item.
     *
     * @param Entity\Order\Item $item Item to display data for.
     *
     * @return string HTML data of the item.
     */
    public static function getItemData(Entity\Order\Item $item)
    {
        $data = '';

        if ($item->getType() == Entity\Product\Variable::TYPE) {
            /** @var Entity\Product\Variable $product */
            $product = $item->getProduct();
            $variation = $product->getVariation($item->getMeta('variation_id')->getValue());
            $data .= self::getVariation($variation, $item);
        }

        return apply_filters('jigoshop\helper\product\item_data', $data, $item);
    }

    /**
     * @param Entity\Product\Variable\Variation $variation Variation to format.
     * @param Entity\Order\Item $item Order item.
     *
     * @return string Formatted variation data in HTML.
     */
    public static function getVariation(Entity\Product\Variable\Variation $variation, Entity\Order\Item $item)
    {
        return Render::get('helper/product/variation', [
            'variation' => $variation,
            'item' => $item,
        ]);
    }

    public static function getRating(Entity\Product $product)
    {
        /** @var $wpdb \wpdb */
        global $wpdb;

        // TODO: Join count and ratings query
        $count = $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = %d
			AND comment_approved = '1'
			AND meta_value > 0
		", $product->getId()));

        $ratings = $wpdb->get_var($wpdb->prepare("
			SELECT SUM(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = %d
			AND comment_approved = '1'
		", $product->getId()));

        // If we don't have any posts
        if (!(bool)$count) {
            return false;
        }

        return round($ratings / $count, 2);
    }

    public static function getRatingHtml($rating, $location = '')
    {
        if ($location) {
            $location = '_' . $location;
        }
        $star_size = apply_filters('jigoshop_star_rating_size' . $location, 16);

        return '<div class="star-rating" title="' . sprintf(__('Rated %s out of 5', 'jigoshop'),
            $rating) . '"><span style="width:' . ($rating * $star_size) . 'px"><span class="rating">' . $rating . '</span> ' . __('out of 5',
            'jigoshop') . '</span></div>';
    }

    public static function getRelated(Entity\Product $product, $limit = 5)
    {
        $cats = array_map(function ($category) {
            return $category['id'];
        }, $product->getCategories());
        $tags = array_map(function ($tag) {
            return $tag['id'];
        }, $product->getTags());

        // Only get related posts that are in stock & visible
        $query = [
            'posts_per_page' => $limit,
            'post__not_in' => [$product->getId()],
            'post_type' => Types::PRODUCT,
            'orderby' => 'rand',
            'meta_query' => [
                [
                    'key' => 'visibility',
                    'value' => [Entity\Product::VISIBILITY_CATALOG, Entity\Product::VISIBILITY_PUBLIC],
                    'compare' => 'IN',
                ],
            ],
            'tax_query' => [
                'relation' => 'OR',
            ],
        ];

        if (!empty($cats)) {
            $query['tax_query'][] = [
                'taxonomy' => Types::PRODUCT_CATEGORY,
                'terms' => $cats,
                'operator' => 'IN',
            ];
        }
        if (!empty($tags)) {
            $query['tax_query'][] = [
                'taxonomy' => Types::PRODUCT_TAG,
                'terms' => $tags,
                'operator' => 'IN',
            ];
        }
        return new \WP_Query($query);
    }

    /**
     * Get billing fields used across the project and plugins with default options.
     *
     * @param array $fields (optional) An array of data fields if you want to change the type, label or add new fields.
     * @param array $except (optional) List of fields that you do not want to show.
     *
     * @return array
     */
    public static function getBasicBillingFields($fields = [], $except = [])
    {
        $fields = array_replace_recursive([
            'first_name' => [
                'label' => __('First Name', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][first_name]',
            ],
            'last_name' => [
                'label' => __('Last Name', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][last_name]',
            ],
            'company' => [
                'label' => __('Company', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][company]',
            ],
            'euvatno' => [
                'label' => __('EU VAT Number', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][euvatno]',
            ],
            'address' => [
                'label' => __('Address', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][address]',
            ],
            'city' => [
                'label' => __('City', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][city]',
            ],
            'postcode' => [
                'label' => __('Postcode', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][postcode]',
            ],
            'country' => [
                'label' => __('Country', 'jigoshop'),
                'type' => 'select',
                'name' => 'jigoshop_order[billing_address][country]',
            ],
            'state' => [
                'label' => __('State/Province', 'jigoshop'),
                'type' => 'select',
                'name' => 'jigoshop_order[billing_address][state]',
            ],
            'phone' => [
                'label' => __('Phone', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][phone]',
            ],
            'email' => [
                'label' => __('Email Address', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[billing_address][email]',
            ],
        ], $fields);

        foreach ($except as $key) {
            unset($fields[$key]);
        }

        return $fields;
    }

    /**
     * Get shipping fields used across the project and plugins with default options.
     *
     * @param array $fields (optional) An array of data fields if you want to change the type, label or add new fields.
     * @param array $except (optional) List of fields that you do not want to show.
     *
     * @return array
     */
    public static function getBasicShippingFields($fields = [], $except = [])
    {
        $fields = array_replace_recursive([
            'first_name' => [
                'label' => __('First Name', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[shipping_address][first_name]',
            ],
            'last_name' => [
                'label' => __('Last Name', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[shipping_address][last_name]',
            ],
            'company' => [
                'label' => __('Company', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[shipping_address][company]',
            ],
            'address' => [
                'label' => __('Address', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[shipping_address][address]',
            ],
            'city' => [
                'label' => __('City', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[shipping_address][city]',
            ],
            'postcode' => [
                'label' => __('Postcode', 'jigoshop'),
                'type' => 'text',
                'name' => 'jigoshop_order[shipping_address][postcode]',
            ],
            'country' => [
                'label' => __('Country', 'jigoshop'),
                'type' => 'select',
                'name' => 'jigoshop_order[shipping_address][country]',
            ],
            'state' => [
                'label' => __('State/Province', 'jigoshop'),
                'type' => 'select',
                'name' => 'jigoshop_order[shipping_address][state]',
            ],
        ], $fields);

        foreach ($except as $key) {
            unset($fields[$key]);
        }

        return $fields;
    }

    public static function getAttachmentsData(Entity\Product $product)
    {
        $attachments = [];
        $types = array_unique(array_map(function($attachment) {
            return $attachment['type'];
        }, $product->getAttachments()));
        $uploadDir = wp_upload_dir(null, false);
        $uploadDir = $uploadDir['baseurl'];
        foreach($types as $type) {
            $attachments[$type] = array_values(array_map(function($attachment) use ($uploadDir) {
                $meta = get_post_meta($attachment['id'], '_wp_attachment_metadata', true);
                $meta['file'] = $uploadDir . '/' . $meta['file'];
                if(isset($meta['sizes'])) {
                    $meta['sizes'] = array_map(function($size) use ($uploadDir) {
                        $size['file'] = $uploadDir . '/' . $size['file'];
                        return $size;
                    }, $meta['sizes']);
                }
                return $meta;
            }, array_filter($product->getAttachments(), function($attachment) use ($type) {
                return $attachment['type'] == $type;
            })));
        }

        return $attachments;
    }

    /**
     * @param Entity\Product\Variable $product
     * @param Entity\Product\Variable\Variation $variation
     * @return array
     */
    public static function getVariationAttributes($product, $variation)
    {
        $attributes = [];

        if($product instanceof Entity\Product\Variable && $variation instanceof Entity\Product\Variable\Variation) {
            foreach ($variation->getAttributes() as $attribute) {
                /** @var Entity\Product\Variable\Attribute $attribute */
                if($attribute->getValue()) {
                    $attributes[$attribute->getAttribute()->getId()] = $attribute->getValue();
                } else {
                    //For 'any of' use first option
                    $unusedOptions = array_filter($attribute->getAttribute()->getOptions(), function($option) use ($product, $attribute) {
                        foreach($product->getVariations() as $_variation) {
                            /** @var  Entity\Product\Variable\Variation $_variation*/
                            if($_variation->getAttribute($attribute->getAttribute()->getId())->getValue() == $option->getId()) {
                                return false;
                            }
                        }

                        return true;
                    });

                    if(count($unusedOptions)) {
                        $unusedOption = array_shift($unusedOptions);
                        $attributes[$attribute->getAttribute()->getId()] = $unusedOption->getId();
                    }
                }
            }
        }

        return $attributes;
    }

    /**
     * @param Entity\Product\Attachment[] $attachments
     * @param string $type
     * @return Entity\Product\Attachment[]
     */
    public static function filterAttachments($attachments, $type)
    {
        return array_filter($attachments, function($attachment) use ($type) {
            return $attachment->getType() == $type;
        });
    }
}
