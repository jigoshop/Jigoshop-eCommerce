<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Email as Entity;
use WPAL\Wordpress;

/**
 * Email factory.
 *
 * @package Jigoshop\Factory
 */
class Email implements EntityFactoryInterface
{
    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var array */
    private $actions = [];

    public function __construct(Wordpress $wp, Options $options)
    {
        $this->wp = $wp;
        $this->options = $options;
    }

    /**
     * Registers an email action.
     *
     * @param       $action      string Action name.
     * @param       $description string Email description.
     * @param array $arguments Accepted arguments list.
     */
    public function register($action, $description, array $arguments)
    {
        $this->actions[$action] = [
            'description' => $description,
            'arguments' => $arguments
        ];
    }

    /**
     * @return array Registered actions.
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Creates new email properly based on POST variable data.
     *
     * @param $id int Post ID to create object for.
     *
     * @return \Jigoshop\Entity\Email
     */
    public function create($id)
    {
        $email = new Entity();
        $email->setId($id);

        if (!empty($_POST)) {
            $helpers = $this->wp->getHelpers();
            $email->setTitle($helpers->sanitizeTitle($_POST['post_title']));
            $email->setText($helpers->parsePostBody($_POST['content']));

            $this->convertData($_POST);
            $email->restoreState($_POST['jigoshop_email']);
        }

        return $email;
    }

    /**
     * Updates email properties based on array data.
     *
     * @param $email \Jigoshop\Entity\Email for update.
     * @param $data array of data for update.
     *
     * @return \Jigoshop\Entity\Email
     */
    public function update(Entity $email, $data)
    {
        if (!empty($data)) {
            $helpers = $this->wp->getHelpers();

            $email->setTitle($helpers->sanitizeTitle($data['post_title']));
            $email->setText($helpers->parsePostBody($data['content']));
            $this->convertData($data);

            $email->restoreState($data['jigoshop_email']);
        }

        return $email;
    }

    /**
     * Fetches product from database.
     *
     * @param $post \WP_Post Post to fetch product for.
     *
     * @return \Jigoshop\Entity\Product
     */
    public function fetch($post)
    {
        if($post && ($post->post_type != Types::EMAIL)) {
            return null;
        }

        $email = new Entity();
        $state = [];

        if ($post) {
            $state = array_map(function ($item) {
                return $item[0];
            }, $this->wp->getPostMeta($post->ID));

			$email->setId($post->ID);
			$email->setTitle($post->post_title);
			$email->setText($post->post_content);
			$state['actions'] = unserialize($state['actions']);
			if(isset($state['attachments'])) {
			    $state['attachments'] = unserialize($state['attachments']);
            }

            $email->restoreState($state);
        }

        return $this->wp->applyFilters('jigoshop\find\email', $email, $state);
    }

    /**
     * converting data function
     * @param $data
     */
    private function convertData(&$data)
    {
        $availableActions = $this->getActions();
        $data['jigoshop_email']['actions'] = array_intersect($data['jigoshop_email']['actions'],
            array_keys($availableActions));
    }

}
