<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Purchasable;
use Jigoshop\Exception;
use Jigoshop\Factory\Product as ProductFactory;
use Jigoshop\Entity\Product as Entity;
use Jigoshop\Traits\WpPostManageTrait;
use WPAL\Wordpress;

/**
 * Product service.
 * @package Jigoshop\Service
 * @author  Amadeusz Starzykiewicz
 */
class ProductService implements ProductServiceInterface
{
    use WpPostManageTrait;

    /** @var \WPAL\Wordpress */
    private $wp;
    /** @var \Jigoshop\Factory\Product */
    private $factory;

    public function __construct(Wordpress $wp, ProductFactory $factory)
    {
        $this->wp = $wp;
        $this->factory = $factory;
        $wp->addAction('save_post_' . Types\Product::NAME, [$this, 'savePost'], 10);
        $wp->addAction('comment_post', [$this, 'saveReview'], 10, 2);
        $wp->addAction('jigoshop\product\sold', [$this, 'addSoldQuantity'], 10, 2);
        $wp->addAction('jigoshop\product\restore', [$this, 'restoreQuantity'], 10, 2);
    }

    /**
     * Adds new type to managed types.
     *
     * @param $type  string Unique type name.
     * @param $class string Class name.
     *
     * @throws \Jigoshop\Exception When type already exists.
     */
    public function addType($type, $class)
    {
        $this->factory->addType($type, $class);
    }

    /**
     * @param $product  \Jigoshop\Entity\Product|Purchasable The product.
     * @param $quantity int Quantity to add.
     */
    public function addSoldQuantity($product, $quantity)
    {
        $product->getStock()->addSoldQuantity($quantity);
        $this->save($product);
    }

    /**
     * @param $product  \Jigoshop\Entity\Product|Purchasable The product.
     * @param $quantity int Quantity to add.
     */
    public function restoreQuantity($product, $quantity)
    {
        $product->getStock()->restoreStock($quantity);
        $this->save($product);
    }

    /**
     * Creates empty product.
     * 
     * @param $type string The product type to create.
     * 
     * @return \Jigoshop\Entity\Product Created product.
     */
    public function create($type) {
        return $this->factory->get($type);
    }

    /**
     * Finds product specified by ID.
     *
     * @param $id int Product ID.
     *
     * @return \Jigoshop\Entity\Product
     */
    public function find($id)
    {
        $post = null;

        if ($id !== null) {
            $post = $this->wp->getPost($id);
        }

        return $this->wp->applyFilters('jigoshop\service\product\find', $this->factory->fetch($post), $id);
    }

    /**
     * Finds item for specified WordPress post.
     *
     * @param $post \WP_Post WordPress post.
     *
     * @return Product Item found.
     */
    public function findForPost($post)
    {
        return $this->wp->applyFilters('jigoshop\service\product\find_for_post', $this->factory->fetch($post), $post);
    }

    /**
     * Finds item specified by state.
     *
     * @param array $state State of the product to be found.
     *
     * @return \Jigoshop\Entity\Product Item found.
     */
    public function findForState(array $state)
    {
        $post = $this->wp->getPost($state['id']);
        $product = $this->factory->fetch($post);
        $product->restoreState($state);

        return $this->wp->applyFilters('jigoshop\service\product\find_for_state', $product, $state);
    }

    /**
     * Finds items by trying to match their name.
     *
     * @param $name string Post name to match.
     *
     * @return Product[] List of matched products.
     */
    public function findLike($name)
    {
        $query = new \WP_Query([
            'post_type' => Types::PRODUCT,
            's' => $name,
        ]);

        return $this->wp->applyFilters('jigoshop\service\product\find_like', $this->findByQuery($query), $name);
    }

    /**
     * Finds items specified using WordPress query.
     *
     * @param $query \WP_Query WordPress query.
     *
     * @return Product[] Collection of found items.
     */
    public function findByQuery($query)
    {
        $products = [];
        if (isset($query->posts) && count($query->posts)) {
            $results = $query->posts;
        } else {
            $results = $query->get_posts();
        }
        // TODO: Maybe it is good to optimize this to fetch all found products at once?
        foreach ($results as $product) {
            $products[$product->ID] = $this->findForPost($product);
        }

        return $this->wp->applyFilters('jigoshop\service\product\find_by_query', $products, $query);
    }

    /**
     * @return int
     */
    public function getProductsCount()
    {
        $wpdb = $this->wp->getWPDB();
        return (int)$wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'publish' AND post_type = %s", Types::PRODUCT));
    }

    /**
     * Saves product to database.
     *
     * @param \Jigoshop\Entity\EntityInterface $object Product to save.
     *
     * @throws Exception
     */
    public function save(EntityInterface $object)
    {
        if (!($object instanceof \Jigoshop\Entity\Product)) {
            throw new Exception('Trying to save not a product!');
        }

        if (!$object->getId()) {
            //if object does not exist insert new one
            $id = $this->insertPost($this->wp, $object, Types::PRODUCT);
            if (!is_int($id) || $id === 0) {
                throw new Exception(__('Unable to save product. Please try again.', 'jigoshop-ecommerce'));
            }
            $object->setId($id);
        }

        // TODO: Support for transactions!

        $fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['name']) || isset($fields['description'])) {
			$post = [];

		    if(isset($fields['name'])) {
			    $post['post_title'] = $fields['name'];
            }
            if(isset($fields['description'])) {
                $post['post_content'] = $fields['description'];
            }
            if(count($post)) {
                $wpdb = $this->wp->getWPDB();
                $wpdb->update($wpdb->posts, $post, ['ID' => $object->getId()]);
            }

			unset($fields['id'], $fields['name'], $fields['description']);
		}

        if (isset($fields['attributes'])) {
            $this->_removeAllProductAttributesExcept($object->getId(), array_map(function ($item) {
                /** @var $item Attribute */
                return $item->getId();
            }, $fields['attributes']));

            foreach ($fields['attributes'] as $attribute) {
                $this->_saveProductAttribute($object, $attribute);
            }

            unset($fields['attributes']);
        }

        if (isset($fields['attachments'])) {
            $this->_removeAllProductAttachments($object->getId());

            foreach ($fields['attachments'] as $attachment) {
                $this->_saveProductAttachment($object->getId(), $attachment);
            }

        }

        foreach ($fields as $field => $value) {
            $this->wp->updatePostMeta($object->getId(), $field, $value);
        }

        $this->wp->doAction('jigoshop\service\product\save', $object);
    }


    /**
     * product method updating post
     * @param Entity $product
     */
    public function updateAndSavePost(EntityInterface $product)
    {
        $this->updatePost($this->wp, $product, Types::PRODUCT);
        $this->save($product);
    }

    /**
     * @param $productId int Product ID.
     * @param $ids       array List of existing attribute IDs.
     */
    private function _removeAllProductAttributesExcept($productId, $ids)
    {
        $wpdb = $this->wp->getWPDB();
        $ids = join(',', array_filter(array_map(function ($item) {
            return (int)$item;
        }, $ids)));
        // Support for removing all items
        if (empty($ids)) {
            $ids = '0';
        }
        $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}jigoshop_product_attribute WHERE attribute_id NOT IN ({$ids}) AND product_id = %d",
            [$productId]);
        $wpdb->query($query);
    }

    /**
     * @param $object    \Jigoshop\Entity\Product
     * @param $attribute Attribute
     */
    private function _saveProductAttribute($object, $attribute)
    {
        $wpdb = $this->wp->getWPDB();

        $value = $attribute->getValue();
        if (is_array($value)) {
            $value = join('|', $value);
        }

        $data = [
            'product_id' => $object->getId(),
            'attribute_id' => $attribute->getId(),
            'value' => $value,
        ];

        if ($attribute->exists()) {
            $wpdb->update($wpdb->prefix . 'jigoshop_product_attribute', $data, [
                'product_id' => $object->getId(),
                'attribute_id' => $attribute->getId(),
            ]);
        } else {
            $wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute', $data);
            $attribute->setExists(Attribute::PRODUCT_ATTRIBUTE_EXISTS);
        }

        foreach ($attribute->getFieldsToSave() as $field) {
            /** @var $field Attribute\Field */
            $data = [
                'product_id' => $object->getId(),
                'attribute_id' => $attribute->getId(),
                'meta_key' => $field->getKey(),
                'meta_value' => esc_sql($field->getValue()),
            ];
            if ($field->getId()) {
                $wpdb->update($wpdb->prefix . 'jigoshop_product_attribute_meta', $data, [
                    'id' => $field->getId(),
                ]);
            } else {
                $wpdb->insert($wpdb->prefix . 'jigoshop_product_attribute_meta', $data);
                $field->setId($wpdb->insert_id);
            }
        }
    }

    /**
     * @param $productId int Product ID.
     */
    private function _removeAllProductAttachments($productId)
    {
        $wpdb = $this->wp->getWPDB();
        $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}jigoshop_product_attachment WHERE product_id = %d",
            [$productId]);
        $wpdb->query($query);
    }

    /**
     * @param $productId int Product ID.
     * @param $attachment array atachment data.
     */
    private function _saveProductAttachment($productId, $attachment)
    {
        $wpdb = $this->wp->getWPDB();
        $wpdb->insert($wpdb->prefix . 'jigoshop_product_attachment', [
            'product_id' => $productId,
            'attachment_id' => $attachment['id'],
            'type' => $attachment['type'],
        ]);
    }


    /**
     * @param $number int Number of products to find.
     *
     * @return Product[] List of products that are out of stock.
     */
    public function findOutOfStock($number)
    {
        $query = new \WP_Query([
            'post_type' => Types::PRODUCT,
            'post_status' => 'publish',
            'posts_per_page' => $number,
            'meta_query' => [
                [
                    'key' => 'stock_manage',
                    'value' => 1,
                    'compare' => '=',
                ],
                [
                    'key' => 'stock_stock',
                    'value' => 0,
                    'compare' => '=',
                ],
            ],
        ]);

        return $this->findByQuery($query);
    }

    /**
     * @param $threshold int Threshold where to assume product is low in stock.
     * @param $number    int Number of products to find.
     *
     * @return Product[] List of products that are low in stock.
     */
    public function findLowStock($threshold, $number)
    {
        $query = new \WP_Query([
            'post_type' => Types::PRODUCT,
            'post_status' => 'publish',
            'posts_per_page' => $number,
            'meta_query' => [
                [
                    'key' => 'stock_manage',
                    'value' => 1,
                    'compare' => '=',
                ],
                [
                    'key' => 'stock_stock',
                    'value' => $threshold,
                    'compare' => '<=',
                ],
            ],
        ]);

        return $this->findByQuery($query);
    }

    /**
     * Save the product data upon post saving.
     *
     * @param $id int Post ID.
     *
     * @return Product
     */
    public function savePost($id)
    {
        $product = $this->factory->create($id);
        $this->save($product);

        return $product;
    }

    public function saveReview($id, $approvew)
    {
        if (isset($_POST['rating'])) {
            update_comment_meta($id, 'rating', (int)$_POST['rating']);
        }
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getReviews(Product $product)
    {
        $reviews = [];
        /** @var \WP_Comment[] $comments */
        $comments = get_comments([
            'post_id' => $product->getId(),
            'order_by' => 'comment_date',
            'order' => 'ASC',
        ]);

        foreach ($comments as $comment) {
            $rating = get_comment_meta($comment->comment_ID, 'rating', true);
            if($rating) {
                $review = new Product\Review();
                $review->setRating($rating);
                $review->setComment($comment);
                $reviews[] = $review;
            }
        }

        return $reviews;
    }

    /**
     * @param \Jigoshop\Entity\Product $product Product to find attachments for.
     * @param string $size Size for images.
     *
     * @return array List of Attachments attached to the product.
     */
    public function getAttachments(Product $product, $size = Options::IMAGE_THUMBNAIL)
    {
        $this->wp->wpUploadDir();
        $uploadUrl = $this->wp->wpUploadDir()['baseurl'];
        $wpdb = $this->wp->getWPDB();

        $query = $wpdb->prepare("SELECT post.ID as id, post.post_title as title, post.guid as url, meta.meta_value as meta, attachment.type
				FROM {$wpdb->prefix}jigoshop_product_attachment as attachment
				LEFT JOIN {$wpdb->posts} as post ON (attachment.attachment_id = post.ID)
				LEFT JOIN {$wpdb->postmeta} as meta ON (meta.meta_key = '_wp_attachment_metadata' AND meta.post_id = post.ID)
				WHERE product_id = %d", $product->getId());
        $results = $wpdb->get_results($query, ARRAY_A);

        $attachments = [];

        foreach ($results as $attachment) {
            $entity = $this->factory->createAttachment($attachment['type']);
            if ($entity instanceof Product\Attachment) {
                $state = [
                    'id' => $attachment['id'],
                    'title' => $attachment['title'],
                    'url' => isset($attachment['meta']['file']) ? $uploadUrl . '/' . $attachment['meta']['file'] : $attachment['url'],
                ];

                if ($entity instanceof Product\Attachment\Image) {
                    $attachment['meta'] = unserialize($attachment['meta']);
                    if (isset($attachment['meta']['sizes'], $attachment['meta']['sizes'][$size])) {
                        $state['thumbnail'] = $uploadUrl . '/' . str_replace(
                                basename($attachment['meta']['file']),
                                basename($attachment['meta']['sizes'][$size]['file']),
                                $attachment['meta']['file']
                            );
                    } else {
                        $state['thumbnail'] = $uploadUrl . '/' . $attachment['meta']['file'];
                    }
                    $state['image'] = $this->wp->wpGetAttachmentImage($attachment['id'], $size);
                }
                $entity->restoreState($state);

                $attachments[] = $entity;
            }
        }

        return $this->wp->applyFilters('jigoshop\service\product\get_attachments', $attachments, $product);
    }

    /**
     * Finds and returns list of available attributes.
     *
     * @return Attribute[] List of available product attributes
     */
    public function findAllAttributes()
    {
        $wpdb = $this->wp->getWPDB();
        $query = "
		SELECT a.id, a.is_local, a.slug, a.label, a.type,
			ao.id AS option_id, ao.value AS option_value, ao.label as option_label
		FROM {$wpdb->prefix}jigoshop_attribute a
			LEFT JOIN {$wpdb->prefix}jigoshop_attribute_option ao ON a.id = ao.attribute_id
			WHERE a.is_local = 0
            ORDER BY a.id ASC, ao.position ASC, ao.id ASC
		";
        $results = $wpdb->get_results($query, ARRAY_A);
        $attributes = [];

        for ($i = 0, $endI = count($results); $i < $endI;) {
            $attribute = $this->factory->createAttribute($results[$i]['type']);
            $attribute->setId((int)$results[$i]['id']);
            $attribute->setSlug($results[$i]['slug']);
            $attribute->setLabel($results[$i]['label']);
            $attribute->setLocal((bool)$results[$i]['is_local']);

            while ($i < $endI && $results[$i]['id'] == $attribute->getId()) {
                if ($results[$i]['option_id'] !== null) {
                    $option = new Attribute\Option();
                    $option->setId($results[$i]['option_id']);
                    $option->setLabel($results[$i]['option_label']);
                    $option->setValue($results[$i]['option_value']);
                    $attribute->addOption($option);
                }

                $i++;
            }

            $attributes[$attribute->getId()] = $attribute;
        }

        return $attributes;
    }

    /**
     * Finds and returns number of available attributes.
     *
     * @return int Number of available product attributes
     */
    public function countAttributes()
    {
        $wpdb = $this->wp->getWPDB();
        $query = "
		SELECT COUNT(*) FROM {$wpdb->prefix}jigoshop_attribute a
			WHERE a.is_local = 0
		";

        return $wpdb->get_var($query);
    }

    /**
     * Finds and returns list of attributes associated with selected product by it's ID.
     *
     * @param $productId int Product ID.
     *
     * @return Attribute[] List of attributes attached to selected product.
     */
    public function getAttributes($productId)
    {
        return $this->factory->getAttributes($productId);
    }

    /**
     * Finds attribute for selected ID.
     *
     * If attribute is not found - returns null.
     *
     * @param int $id Attribute ID.
     *
     * @return Attribute
     */
    public function getAttribute($id)
    {
        return $this->factory->getAttribute($id);
    }

    /**
     * Creates new attribute for selected type.
     *
     * @param int $type Attribute type.
     *
     * @return Attribute
     */
    public function createAttribute($type)
    {
        return $this->factory->createAttribute($type);
    }

    /**
     * Saves attribute to database.
     *
     * @param Attribute $attribute Attribute to save.
     *
     * @return \Jigoshop\Entity\Product\Attribute Saved attribute.
     */
    public function saveAttribute(Attribute $attribute)
    {
        $wpdb = $this->wp->getWPDB();
        $data = [
            'label' => $attribute->getLabel(),
            'slug' => $attribute->getSlug(),
            'type' => $attribute->getType(),
            'is_local' => $attribute->isLocal(),
        ];

        if ($attribute->getId()) {
            $wpdb->update($wpdb->prefix . 'jigoshop_attribute', $data, ['id' => $attribute->getId()]);
        } else {
            $wpdb->insert($wpdb->prefix . 'jigoshop_attribute', $data);
            $attribute->setId($wpdb->insert_id);
        }

        $this->wp->doAction('jigoshop\attribute\save', $attribute);

        $this->removeAllAttributesExcept($attribute->getId(), array_map(function ($item) {
            /** @var $item Attribute\Option */
            return $item->getId();
        }, $attribute->getOptions()));

        $optionPosition = 1;
        foreach ($attribute->getOptions() as $option) {
            /** @var $option Attribute\Option */
            $data = [
                'attribute_id' => $option->getAttribute()->getId(),
                'label' => $option->getLabel(),
                'value' => $option->getValue(),
                'position' => $optionPosition
            ];
            if ($option->getId()) {
                $wpdb->update($wpdb->prefix . 'jigoshop_attribute_option', $data, ['id' => $option->getId()]);
            } else {
                $wpdb->suppress_errors = true;
                $wpdb->insert($wpdb->prefix . 'jigoshop_attribute_option', $data);
                $wpdb->suppress_errors = false;
                if ($wpdb->last_error) {
                    throw new Exception($wpdb->last_error, 409);
                }
                $option->setId($wpdb->insert_id);
            }

            $optionPosition++;
        }

        return $attribute;
    }

    /**
     * @param $attributeId int ID of parent attribute.
     * @param $ids         array IDs to preserve.
     */
    private function removeAllAttributesExcept($attributeId, $ids)
    {
        $wpdb = $this->wp->getWPDB();
        $ids = join(',', array_filter(array_map(function ($item) {
            return (int)$item;
        }, $ids)));
        // Support for removing all items
        if (empty($ids)) {
            $ids = '0';
        }
        $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}jigoshop_attribute_option WHERE id NOT IN ({$ids}) AND attribute_id = %d",
            [$attributeId]);
        $wpdb->query($query);
    }

    /**
     * Removes attribute from database.
     *
     * @param int $id Attribute ID.
     */
    public function removeAttribute($id)
    {
        $wpdb = $this->wp->getWPDB();
        $wpdb->delete($wpdb->prefix . 'jigoshop_attribute', ['id' => $id]);
    }

    /**
     * Returns unique key for product in the cart.
     *
     * @param $item Item Item to get key for.
     *
     * @return string
     */
    public function generateItemKey(Item $item)
    {
        $parts = [
            $item->getProduct()->getId(),
        ];

        $parts = $this->wp->applyFilters('jigoshop\cart\generate_item_key', $parts, $item);

        return hash('md5', join('_', $parts));
    }
}
