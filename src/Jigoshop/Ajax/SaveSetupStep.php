<?php

namespace Jigoshop\Ajax;

use Jigoshop\Core\Options;

/**
 * Class SaveSetupStep
 * @package Jigoshop\Ajax;
 * @author Krzysztof Kasowski
 */
class SaveSetupStep  implements Processable
{
    /** @var Options */
    private $options;

    /**
     * SaveSetupStep constructor.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function process()
    {
        $settings = $this->validate($_POST['jigoshop']);
        $settings = array_merge($this->options->getAll(), $settings);

        update_option(Options::NAME, $settings);

        return [
            'success' => true,
        ];
    }

    private function validate($settings)
    {
        return $settings;
    }
}