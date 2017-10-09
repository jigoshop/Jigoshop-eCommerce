<?php

namespace Jigoshop;

use Jigoshop\Frontend\Pages;
use Monolog\Registry;

/**
 * Class Frontend
 * @package Jigoshop;
 * @author Krzysztof Kasowski
 */
class Frontend
{
    public function __construct()
    {
        add_action('template_redirect', function () {
            if(Pages::isCheckout() && !is_user_logged_in()) {
                Integration::getMessages()->addWarning('Checkout is available only for logged users. Please log in first.');
                wp_safe_redirect(get_permalink(Integration::getOptions()->getPageId(Pages::CART)));
                exit;
            }
        }, 0);

        add_action('user_register', function ($id) {
            $user = get_user_by('id', $id);

            if($user) {
                $key = md5($user->user_login . $user->user_email);

                update_user_meta($id, 'activation_key', $key);
                $this->sendActivationEmail($user, $key);
            }
        });

        add_filter('wp_authenticate_user', function ($user, $password) {
            /** @var \WP_User $user */
            $authCode = get_user_meta($user->ID, 'activation_key', true);

            if($authCode) {
                return new \WP_Error('not-activated', __('This account wasn\'t activated jet, please check your email to activate your account.', 'jigoshop-ecommerce'));
            } else {
                return $user;
            }
        }, 11, 3);

        add_action('init', function() {
            if(isset($_GET['activate_user'], $_GET['key'])) {
                $id = intval($_GET['activate_user']);
                $key = esc_attr($_GET['key']);
                if(get_user_meta($id, 'activation_key', true) == $key) {
                    delete_user_meta($id, 'activation_key');
                    Integration::getMessages()->addNotice(__('You account was successfully activated.', 'jigoshop-ecommerce'));
                } else {
                    Integration::getMessages()->addWarning(__('Something went wrong, please try again.', 'jigoshop-ecommerce'));
                }
            }
        });

        //test1507286558
//        $login = 'test'.time();
//        $id = wp_create_user($login, 'test2', time().'test@gmail.com');
//        var_dump($login);
//        $this->sendActivationEmail($id);
//        exit;
    }

    /**
     * @param \WP_User $user
     * @param string $key
     */
    private function sendActivationEmail($user, $key)
    {
        $title = 'activation';
        wp_mail($user->user_email, $title, add_query_arg(['activate_user' => $user->ID, 'key' => $key], home_url()));
    }
}