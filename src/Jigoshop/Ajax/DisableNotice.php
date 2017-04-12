<?php

namespace Jigoshop\Ajax;
use Jigoshop\Admin\Notices;
use Jigoshop\Core\Options;
use Jigoshop\Exception;

/**
 * Class DisableNotice
 * @package Jigoshop\Ajax;
 * @author Krzysztof Kasowski
 */
class DisableNotice implements Processable
{
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function process()
    {
        if(!isset($_POST['notice'])) {
            throw new Exception('Something went wrong');
        }

        $this->options->update(Notices::SLUG.'.'.$_POST['notice'], true);
        $this->options->saveOptions();

        return [
            'success' => true,
        ];
    }
}