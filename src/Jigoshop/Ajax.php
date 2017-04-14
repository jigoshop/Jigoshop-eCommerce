<?php

namespace Jigoshop;

use Jigoshop\Ajax\Processable;
use WPAL\Wordpress;

/**
 * Class Ajax
 * @package Jigoshop;
 * @author Krzysztof Kasowski
 */
class Ajax
{
    /** @var  Container  */
    private $di;

    /**
     * Ajax constructor.
     * @param Wordpress $wp
     * @param Container $di
     */
    public function __construct(Wordpress $wp, Container $di)
    {
        $this->di = $di;
        $wp->addAction('wp_ajax_jigoshop.ajax.logged', [$this, 'process']);
        $wp->addAction('wp_ajax_jigoshop.ajax', [$this, 'process']);
        $wp->addAction('wp_ajax_nopriv_jigoshop.ajax', [$this, 'process']);
        $wp->addAction('wp_ajax_nopriv_jigoshop.ajax.logged_out', [$this, 'process']);
    }

    /**
     *
     */
    public function run()
    {
        if(isset($_REQUEST['service']) && $this->di->getServices()->detailsExists($_REQUEST['service'])) {
            try {
                $service = $this->di->get($_REQUEST['service']);

                if($service instanceof Processable) {
                    $response = $service->process();
                } else {
                    throw new Exception('Invalid service instance.');
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            echo json_encode($response);
            exit;
        }
    }
}