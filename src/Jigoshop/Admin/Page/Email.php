<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\EmailServiceInterface as Service;
use WPAL\Wordpress;

class Email
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Service */
	private $emailService;

	public function __construct(Wordpress $wp, Options $options, Service $emailService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->emailService = $emailService;

		add_action('wp_ajax_jigoshop.admin.email.update_variable_list', [$this, 'ajaxVariables']);

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::EMAIL, function () use ($wp, $that){
			$wp->addMetaBox('jigoshop-email-data', __('Email Data', 'jigoshop-ecommerce'), [$that, 'box'], Types::EMAIL, 'normal', 'default');
			$wp->addMetaBox('jigoshop-email-variable', __('Email Variables', 'jigoshop-ecommerce'), [$that, 'variablesBox'], Types::EMAIL, 'normal', 'default');
			$wp->addMetaBox('jigoshop-email-attachments', __('Email Attachments', 'jigoshop-ecommerce'), [$that, 'attachmentsBox'], Types::EMAIL, 'side', 'default');
		});

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			if ($wp->getPostType() == Types::EMAIL) {
				Scripts::add('jigoshop.admin.email', \JigoshopInit::getUrl().'/assets/js/admin/email.js', [
					'jquery',
					'jigoshop.helpers',
                    'wp-util'
                ]);
				Styles::add('jigoshop.admin.email', \JigoshopInit::getUrl().'/assets/css/admin/email.css', ['jigoshop.admin']);

				$wp->doAction('jigoshop\admin\email\assets', $wp);
			}
		});
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since        1.0
	 */
	public function ajaxVariables()
	{
		try {
			/** @var \Jigoshop\Entity\Email $email */
			$email = $this->emailService->find((int)$_POST['email']);

			if ($email->getId() === null) {
				throw new Exception(__('Email not found.', 'jigoshop-ecommerce'));
			}

			$availableActions = $this->emailService->getAvailableActions();
			$actions = array_intersect($_POST['actions'], $availableActions);
			$email->setActions($actions);

			$result = [
				'success' => true,
				'html' => Render::get('admin/email/variables', [
					'email' => $email,
					'emails' => $this->emailService->getMails(),
                ])
            ];
		} catch (Exception $e) {
			$result = [
				'success' => false,
				'error' => $e->getMessage(),
            ];
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since        1.0
	 */
	public function box()
	{
		$post = $this->wp->getGlobalPost();
		$email = $this->emailService->findForPost($post);

		$emails = [];
		foreach ($this->emailService->getMails() as $hook => $details) {
			$emails[$hook] = $details['description'];
		}

		Render::output('admin/email/box', [
			'email' => $email,
			'emails' => $emails,
        ]);
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since        1.0
	 */
	public function variablesBox()
	{
		$post = $this->wp->getGlobalPost();
		$email = $this->emailService->findForPost($post);

		Render::output('admin/email/variablesBox', [
			'email' => $email,
			'emails' => $this->emailService->getMails(),
        ]);
	}

    public function attachmentsBox()
    {
        $post = $this->wp->getGlobalPost();
        $email = $this->emailService->findForPost($post);
        $attachments = $this->emailService->getAttachments($email);

        Render::output('admin/email/attachmentsBox', [
            'attachments' => $attachments
        ]);
	}
}
