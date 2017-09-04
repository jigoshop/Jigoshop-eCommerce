<?php
namespace Jigoshop\Core;
use Jigoshop\Core\Types;
use WPAL\Wordpress;
class Permalinks
{
    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    public function __construct(Wordpress $wp, Options $options)
    {
        $this->wp = $wp;
        $this->options = $options;
        $wp->addAction('init', array($this, 'initFix'));
        $wp->addFilter('post_type_link', [$this, 'parsePermalink'], 10, 2);
        $wp->addFilter('rewrite_rules_array', array($this, 'fix'));
    }
    public function initFix()
    {
        if ($this->options->get('permalinks.verbose')) {
            $this->wp->getRewrite()->use_verbose_page_rules = true;
        }
    }
    public function fix($rules)
    {
        $wp_rewrite = $this->wp->getRewrite();
        $permalink = $this->options->get('permalinks.product');
        // Fix the rewrite rules when the product permalink have %product_category% flag
        if (preg_match('`/(.+)(/%'.Types::PRODUCT_CATEGORY.'%)`', $permalink, $matches)) {
            foreach ($rules as $rule => $rewrite) {
                if (preg_match('`^'.preg_quote($matches[1], '`').'/\(`', $rule) && preg_match('/^(index\.php\?'.Types::PRODUCT_CATEGORY.')(?!(.*'.Types::PRODUCT.'))/', $rewrite)) {
                    unset($rules[$rule]);
                }
            }
        }
        // If the shop page is used as the base, we need to enable verbose rewrite rules or sub pages will 404
        if ($this->options->get('permalinks.verbose')) {
            $page_rewrite_rules = $wp_rewrite->page_rewrite_rules();
            $rules = array_merge($page_rewrite_rules, $rules);
        }
        return $rules;
    }
    /**
     * @param $postLink
     * @param $post
     *
     * @return string
     */
    public function parsePermalink($postLink, $post)
    {
        if (is_object($post) && $post->post_type == Types::PRODUCT) {
            $parseProductLink = function ($postLink, $post, $taxonomy) {
                if (strpos($postLink, '%' . $taxonomy . '%') !== 0) {
                    $terms = wp_get_object_terms($post->ID, $taxonomy);
                    if ($terms) {
                        return str_replace('%' . $taxonomy . '%', $terms[0]->slug, $postLink);
                    }
                }
                return $postLink;
            };
            $postLink = $parseProductLink($postLink, $post, Types::PRODUCT_CATEGORY);
            $postLink = $parseProductLink($postLink, $post, Types::PRODUCT_TAG);
        }
        return $postLink;
    }
}