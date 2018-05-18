<?php

namespace Jigoshop\Api\Routes\V1;


use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Exception;
use Jigoshop\Factory\Product;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Products
 * @package Jigoshop\Api\Routes\V1;
 * @author Krzysztof Kasowski
 */
class Products extends PostController implements ApiControllerContract
{
    /** @var  App */
    protected $app;

    /**
     * @apiDefine ProductReturnObject
     * @apiSuccess {Number} data.id    The ID.
     * @apiSuccess {String} data.type Product type.
     * @apiSuccess {String} data.name Product name.
     * @apiSuccess {String} data.description Product description.
     * @apiSuccess {String} data.sku SKU.
     * @apiSuccess {String} data.gtin gtin.
     * @apiSuccess {String} data.mpn mpn.
     * @apiSuccess {Bool} data.featured If featured.
     * @apiSuccess {Number} data.visibility Defines where product should be visible. 0 - Hidden, 1 - Search only, 2 - Catalog only, 3 - Catalog & Search
     * @apiSuccess {Array} data.tax_classes Array of tax classes assigned for this product.
     * @apiSuccess {Object} data.size Size attributes.
     * @apiSuccess {Number} data.size.weight Weight.
     * @apiSuccess {Number} data.size.width width.
     * @apiSuccess {Number} data.size.height height.
     * @apiSuccess {Number} data.size.length length.
     * @apiSuccess {Array} data.attributes Array of tax classes assigned for this product.
     * @apiSuccess {Array} data.attribute_order Array of attributes ids in order.
     * @apiSuccess {Object} data.attachments Array of attachments.
     * @apiSuccess {Object[]} data.attachments.image Image type attachments.
     * @apiSuccess {Number} data.attachments.image.width Path to file.
     * @apiSuccess {Number} data.attachments.image.height File height.
     * @apiSuccess {String} data.attachments.image.file Path to file.
     * @apiSuccess {Array} data.attachments.image.sizes Image available sizes.
     * @apiSuccess {Array} data.attachments.image.image_meta Image meta.
     * @apiSuccess {Object[]} data.attachments.datafile Datafile attachments.
     * @apiSuccess {String} data.attachments.datafile.file Path to file.
     * @apiSuccess {Object[]} data.categories Array of categories that product is assigned to.
     * @apiSuccess {Number} data.categories.id Category id.
     * @apiSuccess {String} data.categories.name Category name.
     * @apiSuccess {String} data.categories.slug Category slug.
     * @apiSuccess {String} data.categories.link Category link.
     * @apiSuccess {Object[]} data.tags Array of tags that product is assigned to.
     * @apiSuccess {Number} data.tags.id Tag id.
     * @apiSuccess {String} data.tags.name Tag name.
     * @apiSuccess {String} data.tags.slug Tag slug.
     * @apiSuccess {String} data.tags.link Tag link.
     * @apiSuccess {String} data.link Link to product.
     * @apiSuccess {Array} data.cross_sells Cross sells array.
     * @apiSuccess {Array} data.up_sells Up sell array.
     * @apiSuccess {Number} data.regular_price Product regular price.
     * @apiSuccess {Object} data.stock Stock object.
     * @apiSuccess {Bool} data.stock.manage If stock is manageable.
     * @apiSuccess {Number} data.stock.status Status of product in stock.
     * @apiSuccess {String} data.stock.backorders If product is allowed to be backordered.
     * @apiSuccess {Number} data.stock.stock Number of products in stock.
     * @apiSuccess {Object} data.sale Sale object.
     * @apiSuccess {Bool} data.sale.enable If sale is enabled.
     * @apiSuccess {Number} data.sale.price Price of product on sale.
     * @apiSuccess {Object} data.sale.from When product sale starts.
     * @apiSuccess {Timestamp} data.sale.from.timestamp When product sale starts timestamp.
     * @apiSuccess {Timestamp} data.sale.from.date When product sale starts date.
     * @apiSuccess {Object} data.sale.to When product sale ends.
     * @apiSuccess {Timestamp} data.sale.to.timestamp When product sale ends timestamp.
     * @apiSuccess {Timestamp} data.sale.to.date When product sale ends date.
     */
    /**
     * @apiDefine ProductData
     * @apiParam {String} post_title Product name.
     * @apiParam {String} post_excerpt Product description.
     * @apiParam {Array} jigoshop_product Product data.
     * @apiParam {String='simple','virtual','variable','external','downloadable'} [jigoshop_product.type] Product type.
     * @apiParam {String} [jigoshop_product.name] Product name.
     * @apiParam {String} [jigoshop_product.description] Product description.
     * @apiParam {String} [jigoshop_product.sku] SKU. SKU will be generated as id if not provided
     * @apiParam {String} [jigoshop_product.gtin] gtin.
     * @apiParam {String} [jigoshop_product.mpn] mpn.
     * @apiParam {Bool} [jigoshop_product.featured] If featured.
     * @apiParam {Number=0,1,2,3} [jigoshop_product.visibility] Visibility value. Defines where product should be visible. 0 - Hidden, 1 - Search only, 2 - Catalog only, 3 - Catalog & Search
     * @apiParam {Array} [jigoshop_product.tax_classes] Array of tax classes assigned for this product.
     * @apiParam {Number} [jigoshop_product.weight] Weight.
     * @apiParam {Number} [jigoshop_product.width] width.
     * @apiParam {Number} [jigoshop_product.height] height.
     * @apiParam {Number} [jigoshop_product.length] length.
     * @apiParam {Array} [jigoshop_product.attributes] Array attributes assigned for this product.
     * @apiParam {Array} [jigoshop_product.attribute_order] Array of attributes ids in order.
     * @apiParam {Object} [jigoshop_product.attachments] Array of attachments.
     * @apiParam {Array} [jigoshop_product.attachments.image] Array of image attachments ids.
     * @apiParam {Array} [jigoshop_product.attachments.datafile] Array of datafile attachments ids.
     * @apiParam {Array} [tax_input.product_category] Array of categories that product is assigned to.
     * @apiParam {Array} [tax_input.tags] Array of tags that product is assigned to.
     * @apiParam {Array} [jigoshop_product.cross_sells] Cross sells array.
     * @apiParam {Array} [jigoshop_product.up_sells] Up sells array.
     * @apiParam {Number} [jigoshop_product.regular_price] Product regular price.
     * @apiParam {String='on', 'off'} [jigoshop_product.stock_manage] If stock is manageable.
     * @apiParam {Number} [jigoshop_product.stock_stock] Number of items in stock.
     * @apiParam {String='no','notify','yes'} [jigoshop_product.stock_allow_backorders] If backorders should be allowed.
     * @apiParam {String='on','off'} [jigoshop_product.sales_enabled] If sale is enabled.
     */

    /**
     * Products constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;


        /**
         * @api {get} /products Get Products
         * @apiName FindProducts
         * @apiGroup Product
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data List of products.
         * @apiUse ProductReturnObject
         * @apiPermission read_products
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /products/:id Get Product information
         * @apiName GetProducts
         * @apiGroup Product
         *
         * @apiParam (Url Params) {Number} id Product unique ID.
         *
         * @apiSuccess {Object} data Products object.
         * @apiUse ProductReturnObject
         *
         * @apiUse validateObjectFindingError
         * @apiPermission read_products
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /products Create a Product
         * @apiName PostProduct
         * @apiGroup Product
         *
         * @apiUse ProductData
         *
         * @apiUse StandardSuccessResponse
         * @apiPermission manage_products
         */
        $app->post('', [$this, 'create']);

        /**
         * @api {put} /products/:id Update a Product
         * @apiName PutProduct
         * @apiGroup Product
         *
         * @apiParam (Url Params) {Number} id Product unique ID.
         * @apiUse ProductData
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_products
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /products/:id Delete a Product
         * @apiName DeleteProduct
         * @apiGroup Product
         *
         * @apiParam (Url Params) {Number} id Product unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_products
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);
    }

    /**
     * overrided create function from PostController
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        if(!isset($_POST['jigoshop_product']) || !is_array($_POST['jigoshop_product'])) {
            throw new Exception('Invalid parameters', 422);
        }
        /** @var Product $factory */
        $factory = $this->app->getContainer()->di->get('jigoshop.factory.product');
        self::overridePostProductData();
        $this->saveAttributes($_POST['product']);
        $product = $factory->createWithAttributes(null);
        $this->service->save($product);

        return $response->withJson([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * overrided update function from PostController
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $object = $this->validateObjectFinding($args);

        $putData = self::overridePutProductData($request->getParsedBody());
        $factory = $this->app->getContainer()->di->get('jigoshop.factory.product');
        $this->saveAttributes($putData['product']);
        $product = $factory->update($object, $putData); //updating object with parsed variables
        $this->service->updateAndSavePost($product);

        return $response->withJson([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * helper function that makes product saving available
     */
    public static function overridePostProductData()
    {
        $_POST['product'] = $_POST['jigoshop_product'];
        //Backward compatibiity
        if(isset($_POST['post_title'])) {
            $_POST['product']['name'] = $_POST['post_title'];
        }
        if(isset($_POST['post_excerpt'])) {
            $_POST['product']['description'] = $_POST['post_excerpt'];
        }
        unset($_POST['jigoshop_product']);
    }

    /**
     * @param array $data
     * @return array
     */
    public static function overridePutProductData(array $data)
    {
        $data['product'] = $data['jigoshop_product'];
        //Backward compatibiity
        if(isset($data['post_title'])) {
            $data['product']['name'] = $data['post_title'];
        }
        if(isset($data['post_excerpt'])) {
            $data['product']['description'] = $data['post_excerpt'];
        }
        unset($data['jigoshop_product']);
        return $data;
    }

    //todo move to service
    /**
     * converts attributes in array to updated or created attribute objects
     * @param array $productData
     */
    private function saveAttributes(array &$productData)
    {
        if (isset($productData['attributes'])) {
            $newAttributesArray = [];
            $factory = $this->app->getContainer()->di->get('jigoshop.factory.product');
            foreach ($productData['attributes'] as $key => &$attribute) {
                if (!$dbAttr = $this->service->getAttribute($key)) {
                    $dbAttr = $this->service->createAttribute($attribute['type']);
                    $dbAttr = $factory->updateAttribute($attribute, $dbAttr, $key);
                    $dbAttr->setValue($attribute['value']);
                } else {
                    $dbAttr->setExists(true);
                    $dbAttr->setValue($attribute);
                }
                $tempAttribute = $this->service->saveAttribute($dbAttr);
                $newAttributesArray[$tempAttribute->getId()] = $tempAttribute;
                unset($productData['attributes'][$key]);
            }
            $productData['attributes'] = $newAttributesArray;
        }
    }

}