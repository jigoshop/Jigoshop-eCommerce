<?php

namespace Jigoshop;

/**
 * Class Kirki
 * @package Jigoshop;
 * @author Krzysztof Kasowski
 */
class Kirki
{

    const CONFIG = 'jigoshop_kirki_config';
    const PANEL = 'jigoshop';
    const GENERAL_SECTION = 'general';

    public function __construct()
    {

    }

    public function run()
    {
        if (class_exists('Kirki')) {
            $this->addConfig();
            $this->addPanels();
            $this->addGeneralSection();
        }
    }

    private function addConfig()
    {
        \Kirki::add_config(self::CONFIG, [
            'capability' => 'edit_theme_options',
            'option_type' => 'theme_mod',
        ]);
    }

    private function addPanels()
    {
        \Kirki::add_panel(self::PANEL, [
            'priority' => 10,
            'title' => __('Jigoshop', 'jigoshop'),
            'description' => __('Example description', 'jigoshop'),
        ]);
    }

    private function addGeneralSection()
    {
        \Kirki::add_section(self::GENERAL_SECTION, [
            'title'          => __( 'General', 'jigoshop'),
            'description'    => __( 'Example description', 'jigoshop'),
            'panel'          => self::PANEL,
            'priority'       => 10,
            'capability'     => 'edit_theme_options',
            'theme_supports' => '', // Rarely needed.
        ]);
        \Kirki::add_field(self::CONFIG, [
            'type'        => 'color',
            'settings'    => 'primary_color',
            'label'       => __('Primary text Color', 'jigoshop' ),
            'section'     => self::GENERAL_SECTION,
            'default'     => '#337ab7',
        ]);
        \Kirki::add_field(self::CONFIG, [
            'type'        => 'color',
            'settings'    => 'primary_background',
            'label'       => __('Primary background', 'jigoshop' ),
            'section'     => self::GENERAL_SECTION,
            'default'     => '#337ab7',
        ]);
        \Kirki::add_field(self::CONFIG, [
            'type'        => 'color',
            'settings'    => 'sale_color',
            'label'       => __('Sale badge text color', 'jigoshop' ),
            'section'     => self::GENERAL_SECTION,
            'default'     => '#337ab7',
        ]);
        \Kirki::add_field(self::CONFIG, [
            'type'        => 'color',
            'settings'    => 'sale_background',
            'label'       => __('Sale badge background', 'jigoshop' ),
            'section'     => self::GENERAL_SECTION,
            'default'     => '#337ab7',
        ]);
    }
}