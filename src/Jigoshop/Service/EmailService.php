<?php

namespace Jigoshop\Service;

use Jigoshop\Api\Routes\V1\Emails;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Email;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Factory\Email as Factory;
use Jigoshop\Traits\WpPostManageTrait;
use WPAL\Wordpress;

/**
 * Email service.
 *
 * TODO: Add caching.
 *
 * @package Jigoshop\Service
 */
class EmailService implements EmailServiceInterface
{
    use WpPostManageTrait;

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Factory */
	private $factory;
	/** @var bool */
	private $suppress = false;
    /** @var bool */
    private $suppressForWholeRequest = false;
    /** @var array  */
    private $templates = [];

	public function __construct(Wordpress $wp, Options $options, Factory $factory)
	{
        $this->wp = $wp;
        $this->options = $options;
        $this->factory = $factory;
        $wp->addAction('save_post_'.Types\Email::NAME, [$this, 'savePost'], 10);
	}

	/**
	 * Suppresses sending next email.
	 */
	public function suppressNextEmail()
	{
		$this->suppress = true;
	}

    /**
     * Suppresses sending emails for whole request.
     */
    public function suppressEmailForWholeRequest()
    {
        $this->suppressForWholeRequest = true;
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 *
	 * @return \Jigoshop\Entity\Email The item.
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
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 *
	 * @return \Jigoshop\Entity\Email Item found.
	 */
	public function findForPost($post)
	{
		return $this->factory->fetch($post);
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 *
	 * @return Email[] Collection of found items.
	 */
	public function findByQuery($query)
	{
		$results = $query->get_posts();
		$emails = [];

		// TODO: Maybe it is good to optimize this to fetch all found emails at once?
		foreach ($results as $email) {
			$emails[] = $this->findForPost($email);
		}

		return $emails;
	}

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object)
	{
		if (!($object instanceof \Jigoshop\Entity\Email)) {
			throw new Exception('Trying to save not an email!');
		}

        if (!$object->getId()) {
            //if object does not exist insert new one
            $id = $this->insertPost($this->wp, $object, Types::EMAIL);
            if (!is_int($id) || $id === 0) {
                throw new Exception(__('Unable to save email. Please try again.', 'jigoshop-ecommerce'));
            }
            $object->setId($id);
        }

        // TODO: Support for transactions!

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['title']) || isset($fields['text'])) {
			// We do not need to save ID, title and text (content) as they are saved by WordPress itself.
			unset($fields['id'], $fields['title'], $fields['text']);
		}

		foreach ($fields as $field => $value) {
			$this->wp->updatePostMeta($object->getId(), $field, $value);
		}
        //
		//$this->addTemplate($object->getId(), $object->getActions());
		$this->wp->doAction('jigoshop\service\email\save', $object);
	}

	/**
	 * Save the email data upon post saving.
	 *
	 * @param $id int Post ID.
     *
     * @return Email
	 */
	public function savePost($id)
	{
        $email = $this->factory->create($id);
		$this->save($email);

		return $email;
	}

    /**
     * email method updating post
     * @param Email $email
     */
    public function updateAndSavePost(Email $email)
    {
        $this->updatePost($this->wp, $email, Types::EMAIL);
        $this->save($email);
    }

	/**
	 * @return array List of registered mails with accepted arguments.
	 */
	public function getMails()
	{
		return $this->factory->getActions();
	}

	/**
	 * Registers an email action.
	 *
	 * @param       $action      string Action name.
	 * @param       $description string Email description.
	 * @param array $arguments   Accepted arguments list.
	 */
	public function register($action, $description, array $arguments)
	{
		$this->factory->register($action, $description, $arguments);
	}

	/**
	 * @return array List of available actions.
	 */
	public function getAvailableActions()
	{
		return array_keys($this->factory->getActions());
	}

	/**
	 * @param $postId int Email template to add.
	 * @param $hooks  array List of hooks to add to.
	 */
	public function addTemplate($postId, $hooks)
	{
		$templates = $this->options->get('emails.templates');

		if (is_array($templates)) {
			$templates = array_map(function ($template) use ($postId){
				return array_filter($template, function ($templatePostId) use ($postId){
					return $templatePostId != $postId;
				});
			}, $templates);
		}

		foreach ($hooks as $hook) {
			$templates[$hook][] = $postId;
		}

		$this->options->update('emails.templates', $templates);
        $this->options->saveOptions();
	}

	/**
	 * Sends specified email to specified address.
	 *
	 * @param       $hook string Email to send.
	 * @param array $args Arguments to the email.
	 * @param       $to   string Receiver address.
	 */
	public function send($hook, array $args = [], $to)
	{
		if ($this->suppress) {
			$this->suppress = false;

			return;
		}

		if ($this->suppressForWholeRequest) {
            return;
        }

		$templates = $this->getTemplates();
		if (!isset($templates[$hook]) || empty($templates[$hook])) {
			return;
		}

        foreach ($templates[$hook] as $postId) {
            $post = $this->wp->getPost($postId);

            if (!empty($post) && $post->post_status == 'publish') {
                $email = $this->findForPost($post);
                $email->setSubject(empty($email->getSubject()) ? $email->getTitle() : $email->getSubject());
                $this->filterEmail($email, $args);

                $headers = [
					'MIME-Version: 1.0',
					'Content-Type: text/html; charset=UTF-8',
					'From: "'.$this->options->get('general.emails.from').'" <'.$this->options->get('general.email').'>',
                ];
                $footer = $this->options->get('general.emails.footer');
                if($footer) {
                	$email->setText(sprintf('%s<br /><br />%s', $email->getText(), $footer));
                }

                $this->wp->wpMail(
					$to,
					$email->getSubject(),
					nl2br($email->getText()),
					$headers,
                    $this->getAttachments($email)
				);
			}
		}
	}

	private function filterEmail(Email $email, array $args)
	{
		if (empty($args)) {
			return $email;
		}
		foreach ($args as $key => $value) {
			$email->setSubject(str_replace('['.$key.']', $value, $email->getSubject()));
			if (empty($value)) {
				$email->setText(preg_replace('#\['.$key.'\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$2', $email->getText()));
                $email->setText(preg_replace('#\['.$key.'\](.*?)\[\/'.$key.'\]#si', '', $email->getText()));
                $email->setText(str_replace('['.$key.']', '', $email->getText()));
			} else {
                $email->setText(preg_replace('#\['.$key.'\](.*?)\[value\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$1'.'['.$key.']'.'$2', $email->getText()));
                $email->setText(preg_replace('#\['.$key.'\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$1', $email->getText()));
                $email->setText(preg_replace('#\['.$key.'\](.*?)\[value\](.*?)\[\/'.$key.'\]#si', '$1'.'['.$key.']'.'$2', $email->getText()));
                $email->setText(preg_replace('#\[' . $key . '\](.*?)\[\/' . $key . '\]#si', '$1', $email->getText()));
                $email->setText(str_replace('['.$key.']', $value, $email->getText()));
			}
		}
    }

    /**
     * @return array
     */
	public function getTemplates()
    {
        if(count($this->templates) == 0) {
            $wpdb = $this->wp->getWPDB();
            $templates = $wpdb->get_results($wpdb->prepare("
SELECT posts.ID as id, meta.meta_value as actions FROM {$wpdb->posts} as posts
JOIN {$wpdb->postmeta} as meta ON (meta.post_id = posts.ID AND meta.meta_key = %s)
WHERE posts.post_type = %s", 'actions', Types\Email::NAME), ARRAY_A);

            foreach($templates as $template) {
                $actions = maybe_unserialize($template['actions']);
                if(is_array($actions) && count($actions)) {
                    foreach($actions as $action) {
                        if(!isset($this->templates[$action])) {
                            $this->templates[$action] = [$template['id']];
                        } else {
                            $this->templates[$action][] = $template['id'];
                        }
                    }
                }
            }
        }

        return $this->templates;
    }

    /**
     * @param Email $email
     *
     * @return array
     */
    public function getAttachments(Email $email)
    {
        $attacments = [];
        $ids = $email->getAttachments();
        if(is_array($ids)) {
            foreach ($ids as $id) {
                $attacments[$id] = get_attached_file($id);
            }
        }

        return  array_filter($attacments);
    }

    /**
     * Gets number of Emails
     *
     * @return int
     */
    public function getEmailsCount()
    {
        $wpdb = $this->wp->getWPDB();
        return (int)$wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'publish' AND post_type = %s", Types::EMAIL));
    }

}