<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Simple;
use Jigoshop\Entity\Product\Variable;
use Jigoshop\Entity\Product\Virtual;
use Jigoshop\Exception;
use Jigoshop\Helper\Attribute as HelperAttribute;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\Product\CategoryServiceInterface;
use WPAL\Wordpress;

class Product
{
    /** @var \WPAL\Wordpress */
    private $wp;
    /** @var \Jigoshop\Core\Options */
    private $options;
    /** @var \Jigoshop\Service\ProductServiceInterface */
    private $productService;
    /** @var \Jigoshop\Service\Product\CategoryServiceInterface */
    private $categoryService;
    /** @var Types\Product */
    private $type;
    /** @var array */
    private $menu;

    public function __construct(Wordpress $wp, Options $options, Types\Product $type, ProductServiceInterface $productService, CategoryServiceInterface $categoryService)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->type = $type;

        $wp->addAction('wp_ajax_jigoshop.admin.product.find', [$this, 'ajaxFindProduct'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.product.update_type', [$this, 'ajaxUpdateType'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.product.save_attribute', [$this, 'ajaxSaveAttribute'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.product.remove_attribute', [$this, 'ajaxRemoveAttribute'], 10, 0);
        $wp->addAction('wp_ajax_jigoshop.admin.product.get_inherited_attributes', [$this, 'ajaxGetInheritedAttributes'], 10, 0);

        $that = $this;
        $wp->addAction('add_meta_boxes_'.Types::PRODUCT, function () use ($wp, $that){
            $wp->addMetaBox('jigoshop-product-data', __('Product Data', 'jigoshop-ecommerce'), [$that, 'box'], Types::PRODUCT, 'normal', 'high');
            $wp->addMetaBox('jigoshop-product-attachments', __('Attachments', 'jigoshop-ecommerce'), [$that, 'attachmentsBox'], Types::PRODUCT, 'side', 'low');
            $wp->removeMetaBox('commentstatusdiv', null, 'normal');
        });

        $this->menu = $menu = $this->wp->applyFilters('jigoshop\admin\product\menu', [
            'general' => ['label' => __('General', 'jigoshop-ecommerce'), 'visible' => true],
            'advanced' => ['label' => __('Advanced', 'jigoshop-ecommerce'), 'visible' => [Simple::TYPE, Virtual::TYPE]],
            'attributes' => ['label' => __('Attributes', 'jigoshop-ecommerce'), 'visible' => true],
            'stock' => ['label' => __('Stock', 'jigoshop-ecommerce'), 'visible' => [Simple::TYPE, Virtual::TYPE]],
            'sales' => ['label' => __('Sales', 'jigoshop-ecommerce'), 'visible' => [Simple::TYPE, Virtual::TYPE]],
        ]);

        $wp->addAction('admin_enqueue_scripts', function () use ($wp, $menu, $that){
            if ($wp->getPostType() == Types::PRODUCT) {

                Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', ['jigoshop.admin.product']);
                Styles::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl().'/assets/css/vendors/datepicker.css', ['jigoshop.admin.product']);
                Styles::add('jigoshop.admin.product', \JigoshopInit::getUrl().'/assets/css/admin/product.css');

                Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl() . '/assets/js/vendors/select2.js', ['jquery', 'jigoshop.admin.product']);
                Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl() . '/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', ['jquery', 'jigoshop.admin.product']);
                Scripts::add('jigoshop.vendors.datepicker', \JigoshopInit::getUrl() . '/assets/js/vendors/datepicker.js', ['jquery', 'jigoshop.admin.product']);
                Scripts::add('jigoshop.admin.product', \JigoshopInit::getUrl() . '/assets/js/admin/product.js', [
                    'jquery',
                    'jigoshop.helpers.ajax_search',
                    'jquery-ui-sortable'
                ]);
                Scripts::localize('jigoshop.admin.product', 'jigoshop_admin_product', [
                    'i18n' => [
                        'saved' => __('Changes saved.', 'jigoshop-ecommerce'),
                        'attribute_removed' => __('Attribute successfully removed.', 'jigoshop-ecommerce'),
                        'confirm_remove' => __('Are you sure?', 'jigoshop-ecommerce'),
                        'invalid_attribute' => __('Invalid attribute, please select another one.', 'jigoshop-ecommerce'),
                        'attribute_without_label' => __('Please provide attribute label.', 'jigoshop-ecommerce'),
                    ],
                    'menu' => array_map(function ($item){
                        return $item['visible'];
                    }, $menu),
                    'attachments' => $that->getAttachments()
                ]);

                $wp->doAction('jigoshop\admin\product\assets', $wp);
            }
        }, 5);
    }

    /**
     * Displays the product data box, tabbed, with several panels covering price, stock etc
     *
     * @since        1.0
     */
    public function box()
    {
        $post = $this->wp->getGlobalPost();
        /** @var \Jigoshop\Entity\Product $product */
        $product = $this->productService->findForPost($post);
        $types = [];

        foreach ($this->type->getEnabledTypes() as $type) {
            /** @var $type Types\Product\Type */
            $types[$type->getId()] = $type->getName();
        }

        $taxClasses = [];
        foreach ($this->options->get('tax.classes') as $class) {
            $taxClasses[$class['class']] = $class['label'];
        }

        $attributes = [
            '' => ['label' => ''],
            '-1' => ['label' => __('Custom attribute', 'jigoshop-ecommerce')],
        ];
        foreach ($this->productService->findAllAttributes() as $attribute) {
            /** @var $attribute Attribute */
            $attributes[$attribute->getId()] = ['label' => $attribute->getLabel(), 'disabled' => $product->hasAttribute($attribute->getId())];
        }

        $tabs = $this->wp->applyFilters('jigoshop\admin\product\tabs', [
            'general' => [
                'product' => $product,
            ],
            'advanced' => [
                'product' => $product,
                'taxClasses' => $taxClasses,
            ],
            'attributes' => [
                'product' => $product,
                'availableAttributes' => $attributes,
                'attributes' => $product->getAttributes(),
            ],
            'stock' => [
                'product' => $product,
            ],
            'sales' => [
                'product' => $product,
            ],
        ], $product);

        Render::output('admin/product/box', [
            'product' => $product,
            'types' => $types,
            'menu' => $this->menu,
            'tabs' => $tabs,
            'current_tab' => 'general',
        ]);
    }

    public function attachmentsBox()
    {
        $menu = [
            'gallery' => __('Gallery', 'jigoshop-ecommerce'),
            'downloads' => __('Downloads', 'jigoshop-ecommerce'),
        ];

        Render::output('admin/product/attachments', [
            'menu' => $menu,
        ]);
    }

    public function getAttachments()
    {
        $post = $this->wp->getGlobalPost();
        /** @var \Jigoshop\Entity\Product $product */
        $product = $this->productService->findForPost($post);
        return $this->productService->getAttachments($product);
    }

    public function ajaxUpdateType()
    {
        try {
            if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
                throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['product_id'])) {
                throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
            }
            if (!isset($_POST['type']) || empty($_POST['type'])) {
                throw new Exception(__('Product type was not specified.', 'jigoshop-ecommerce'));
            }

            update_post_meta((int)$_POST['product_id'], 'type', trim($_POST['type']));

            $additionalTabs = [];
            if(isset($_POST['additionalTabs']) && is_array($_POST['additionalTabs'])) {
                $product = $this->productService->find($_POST['product_id']);
                $tabs = $this->wp->applyFilters('jigoshop\admin\product\tabs', [], $product);
                foreach($tabs as $tabName => $tab) {
                    if(in_array($tabName, $_POST['additionalTabs'])) {
                        $additionalTabs[$tabName] = $tab;
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'additionalTabs' => $additionalTabs
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function ajaxSaveAttribute()
    {
        try {
            if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
                throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['product_id'])) {
                throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
            }
            if (!isset($_POST['attribute_id']) || empty($_POST['attribute_id'])) {
                throw new Exception(__('Attribute was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['attribute_id'])) {
                throw new Exception(__('Invalid attribute ID.', 'jigoshop-ecommerce'));
            }

            /** @var \Jigoshop\Entity\Product $product */
            $product = $this->productService->find((int)$_POST['product_id']);

            if (!$product->getId()) {
                throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
            }

            $id = (int)$_POST['attribute_id'];
            if ($product->hasAttribute($id)) {
                $attribute = $product->removeAttribute($id);
                $attributeExists = true;
            } else if ($id == -1) {
                $attribute = new Attribute\Custom();
                $label = trim(strip_tags($_POST['attribute_label']));

                if (empty($label)) {
                    throw new Exception(__('Custom attribute requires label to be set.', 'jigoshop-ecommerce'));
                }

                $attribute->setLabel($label);
                $attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($label));
                $this->productService->saveAttribute($attribute);
                $attributeExists = false;
            } else {
                $attribute = $this->productService->getAttribute($id);
                $attributeExists = false;
            }

            if ($attribute === null) {
                throw new Exception(__('Attribute does not exists.', 'jigoshop-ecommerce'));
            }

            if (isset($_POST['value'])) {
                $attribute->setValue(trim(htmlspecialchars(wp_kses_post($_POST['value']))));
            } else if ($attributeExists) {
                throw new Exception(sprintf(__('Attribute "%s" already exists.', 'jigoshop-ecommerce'), $attribute->getLabel()));
            } else {
                $attribute->setValue('');
            }

            if (isset($_POST['options']) && isset($_POST['options']['display'])) {
                $attribute->setVisible($_POST['options']['display'] === 'true');
            }

            $this->wp->doAction('jigoshop\admin\product_attribute\add', $attribute, $product);

            $product->addAttribute($attribute);
            $this->productService->save($product);

            echo json_encode([
                'success' => true,
                'html' => Render::get('admin/product/box/attributes/attribute', ['attribute' => $attribute]),
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function ajaxRemoveAttribute()
    {
        try {
            if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
                throw new Exception(__('Product was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['product_id'])) {
                throw new Exception(__('Invalid product ID.', 'jigoshop-ecommerce'));
            }
            if (!isset($_POST['attribute_id']) || empty($_POST['attribute_id'])) {
                throw new Exception(__('Attribute was not specified.', 'jigoshop-ecommerce'));
            }
            if (!is_numeric($_POST['attribute_id'])) {
                throw new Exception(__('Invalid attribute ID.', 'jigoshop-ecommerce'));
            }

            /** @var \Jigoshop\Entity\Product $product */
            $product = $this->productService->find((int)$_POST['product_id']);

            if (!$product->getId()) {
                throw new Exception(__('Product does not exists.', 'jigoshop-ecommerce'));
            }

            $product->removeAttribute((int)$_POST['attribute_id']);
            $this->productService->save($product);
            echo json_encode([
                'success' => true,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

        exit;
    }

    public function ajaxFindProduct()
    {
        try {
            $products = [];

            if (isset($_POST['query'])) {
                $query = trim(htmlspecialchars(strip_tags($_POST['query'])));
                if (!empty($query)) {
                    $products = $this->productService->findLike($query);
                }
            } else if (isset($_POST['value'])) {
                $query = explode(',', trim(htmlspecialchars(strip_tags($_POST['value']))));
                foreach ($query as $id) {
                    $products[] = $this->productService->find($id);
                }
            } else {
                throw new Exception(__('Neither query nor value is provided to find products.', 'jigoshop-ecommerce'));
            }

            $products = array_filter($products, function($product) {
                return $product instanceof \Jigoshop\Entity\Product;
            });

            $result = [
                'success' => true,
                'results' => $this->prepareResults($products, isset($_POST['only_parent']) && (bool)$_POST['only_parent']),
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

    public function ajaxGetInheritedAttributes() {
        try {
            if(!isset($_POST['categories']) || !is_array($_POST['categories'])) {
                throw new Exception(__('No categories specified.', 'jigoshop-ecommerce'));
            }

            $categories = [];
            $parents = [];
            foreach($_POST['categories'] as $categoryId) {
                $categories[$categoryId] = $this->categoryService->find($categoryId);

                if($categories[$categoryId]->getParentId() > 0) {
                    $parents[] = $categories[$categoryId]->getParentId();
                }
            }

            $attributes = [];
            foreach($categories as $categoryId => $category) {
                if(in_array($categoryId, $parents)) {
                    continue;
                }

                $categoryAttributes = $category->getAllAttributes();
                $attributesStates = $category->getAttributesStates();

                $filteredAttributes = [];
                for($ca = 0; $ca < count($categoryAttributes); $ca++) {
                    if(in_array($categoryAttributes[$ca]->getId(), $category->getRemovedAttributesIds())) {
                        continue;
                    }

                    $visible = true;
                    if(isset($attributesStates[$categoryAttributes[$ca]->getId()]) && $attributesStates[$categoryAttributes[$ca]->getId()] === false) {
                        $visible = false;
                    }

                    $categoryAttributes[$ca]->setVisible($visible);
                    $filteredAttributes[] = $categoryAttributes[$ca];
                }

                $categoryAttributes = HelperAttribute::sortAttributesByOrder($filteredAttributes, $category->getOrderOfAttributes());

                $attributes = array_merge($attributes, $categoryAttributes);
            }

            $product = $this->productService->find($_POST['productId']);
            $attributesRender = [];
            foreach($attributes as $attribute) {
                $attributesRender[] = [
                    'id' => $attribute->getId(),
                    'render' => Render::get('admin/product/box/attributes/attribute', [
                        'attribute' => $attribute
                    ])
                ];

                if(!$product->hasAttribute($attribute->getId())) {
                    if($attribute->getType() == 1) {
                        if(!empty($attribute->getOptions())) {
                            $attribute->setValue(array_keys($attribute->getOptions())[0]);
                        }
                        else {
                            $attribute->setValue(0);
                        }                        
                    }
                    else {
                        $attribute->setValue('');
                    }
                    $product->addAttribute($attribute);
                }
            }
            
	    $this->productService->save($product);

            $result = [
                'success' => true,
                'attributes' => $attributesRender
            ];
        }
        catch(Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Get id and name product from different products types
     *
     * @param array $products Products list
     * @param bool  $onlyParent
     *
     * @return array
     */
    public function prepareResults($products, $onlyParent = false)
    {
        $preparedProducts = [];
        if (count($products) > 0)
        {
            /** @var \Jigoshop\Entity\Product | \Jigoshop\Entity\Product\Variable $product */
            /** @var \Jigoshop\Entity\Product\Variable\Variation $variation */
            foreach ($products as $product)
            {
                if ($product->getType() == Variable::TYPE && $onlyParent == false)
                {
                    foreach ($product->getVariations() as $variation)
                    {
                        $preparedProducts[] = [
                            'id'   => $variation->getProduct()->getId(),
                            'text' => $variation->getTitle()
                        ];
                    }
                }
                else
                {
                    $preparedProducts[] = ['id' => $product->getId(), 'text' => $product->getName()];
                }
            }
        }

        return $preparedProducts;
    }
}
