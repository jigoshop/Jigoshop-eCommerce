<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Coupon as Entity;
use Jigoshop\Entity\Coupon;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Factory\Coupon as Factory;
use WPAL\Wordpress;

/**
 * Coupon service.
 *
 * TODO: Add caching.
 *
 * @package Jigoshop\Service
 */
class CouponService implements CouponServiceInterface
{
    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var Factory */
    private $factory;
    /** @var array */
    private $types;

    public function __construct(Wordpress $wp, Options $options, Factory $factory)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->factory = $factory;
        $wp->addAction('save_post_' . Types\Coupon::NAME, array($this, 'savePost'), 10);
    }

    /**
     * Finds item specified by ID.
     *
     * @param $id int The ID.
     *
     * @return Coupon
     */
    public function find($id)
    {
        $post = null;

        if ($id !== null) {
            $post = $this->wp->getPost($id);
        }

        return $this->factory->fetch($post);
    }

    /**
     * Save the email data upon post saving.
     *
     * @param $id int Post ID.
     */
    public function savePost($id)
    {
        $coupon = $this->factory->create($id);
        $this->save($coupon);
    }

    /**
     * Saves entity to database.
     *
     * @param $object EntityInterface Entity to save.
     */
    public function save(EntityInterface $object)
    {
        if (!($object instanceof Entity)) {
            throw new Exception('Trying to save not a coupon!');
        }

        if (!$object->getId()) {
            //if object does not exist insert new one
            $wpdb = $this->wp->getWPDB();
            $date = $this->wp->getHelpers()->currentTime('mysql');
            $dateGmt = $this->wp->getHelpers()->currentTime('mysql', true);

            $wpdb->insert($wpdb->posts, array(
                'post_author' => 0, //TODO #316 ticket update posts
                'post_date' => $date,
                'post_date_gmt' => $dateGmt,
                'post_modified' => $date,
                'post_modified_gmt' => $dateGmt,
                'post_type' => Types::COUPON,
                'post_title' => $object->getTitle(),
                'post_name' => sanitize_title($object->getTitle()),
                'ping_status' => 'closed',
                'comment_status' => 'closed',
            ));

            $id = $wpdb->insert_id;
            if (!is_int($id) || $id === 0) {
                throw new Exception(__('Unable to save coupon. Please try again.', 'jigoshop'));
            }

            $object->setId($id);

        }

        // TODO: Support for transactions!

        $fields = $object->getStateToSave();

        if (isset($fields['id']) || isset($fields['title']) || isset($fields['code'])) {
            // We do not need to save ID, title and code (post name) as they are saved by WordPress itself.
            unset($fields['id'], $fields['title'], $fields['code']);
        }

        foreach ($fields as $field => $value) {
            $this->wp->updatePostMeta($object->getId(), $field, $value);
        }

        $this->wp->doAction('jigoshop\service\coupon\save', $object);
    }

    /**
     * @param $coupon Entity
     *
     * @return string Type name.
     */
    public function getType($coupon)
    {
        $types = $this->getTypes();
        if (!isset($types[$coupon->getType()])) {
            return '';
        }

        return $types[$coupon->getType()];
    }

    /**
     * @return array List of available coupon types.
     */
    public function getTypes()
    {
        if ($this->types === null) {
            $this->types = $this->wp->applyFilters('jigoshop\service\coupon\types', array(
                Entity::FIXED_CART => __('Cart Discount', 'jigoshop'),
                Entity::PERCENT_CART => __('Cart % Discount', 'jigoshop'),
                Entity::FIXED_PRODUCT => __('Product Discount', 'jigoshop'),
                Entity::PERCENT_PRODUCT => __('Product % Discount', 'jigoshop')
            ));
        }

        return $this->types;
    }

    /**
     * @param array $codes List of codes to find.
     *
     * @return Coupon[] Found coupons.
     */
    public function getByCodes(array $codes)
    {
        $coupons = array();
        foreach ($codes as $code) {
            $coupons[] = $this->findByCode($code);
        }

        // TODO: Filter by dates somehow in DB?
        $time = time();
        $coupons = array_filter($coupons, function ($coupon) use ($time) {
            /** @var $coupon \Jigoshop\Entity\Coupon */
            if ($coupon === null) {
                return false;
            }

            if ($coupon->getFrom() !== null && $coupon->getFrom()->getTimestamp() > $time) {
                return false;
            }
            if ($coupon->getTo() !== null && $coupon->getTo()->getTimestamp() < $time) {
                return false;
            }

            return true;
        });

        return $coupons;
    }

    /**
     * @param $code string Code of the coupon to find.
     *
     * @return \Jigoshop\Entity\Coupon Coupon found.
     */
    public function findByCode($code)
    {
        $query = new \WP_Query(array(
            'post_type' => Types::COUPON,
            'name' => $code,
        ));

        $results = $this->findByQuery($query);

        if (count($results) > 0) {
            /** @var \Jigoshop\Entity\Coupon $coupon */
            $coupon = $results[0];
            $time = time();

            if ($coupon->getFrom() !== null && $coupon->getFrom()->getTimestamp() > $time) {
                return null;
            }
            if ($coupon->getTo() !== null && $coupon->getTo()->getTimestamp() < $time) {
                return null;
            }

            return $coupon;
        }

        return null;
    }

    /**
     * Finds items specified using WordPress query.
     *
     * @param $query \WP_Query WordPress query.
     *
     * @return Coupon[] Collection of found items.
     */
    public function findByQuery($query)
    {
        $results = $query->get_posts();
        $coupons = array();

        // TODO: Maybe it is good to optimize this to fetch all found coupons at once?
        foreach ($results as $coupon) {
            $coupons[] = $this->findForPost($coupon);
        }

        return $coupons;
    }

    /**
     * Finds item for specified WordPress post.
     *
     * @param $post \WP_Post WordPress post.
     *
     * @return EntityInterface Item found.
     */
    public function findForPost($post)
    {
        return $this->factory->fetch($post);
    }

    /**
     * Gets number of Coupons
     *
     * @return int
     */
    public function getCouponsCount()
    {
        $wpdb = $this->wp->getWPDB();
        return (int)$wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'publish' AND post_type = %s", Types::COUPON));
    }
}
