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
        if(isset($_POST['jigoshop']) && !empty($_POST['jigoshop'])) {
            $settings = $this->validate($_POST['jigoshop']);
            $settings = array_replace_recursive($this->options->getAll(), $settings);

            update_option(Options::NAME, $settings);
        }

        return [
            'success' => true,
        ];
    }

    private function validate($settings)
    {
        return $settings;
    }
}