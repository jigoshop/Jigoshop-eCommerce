<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Cart;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product\Attributes;
use Jigoshop\Entity\Product\Variable;
use Jigoshop\Helper\Tax;
use Jigoshop\Shipping\Method;
use Monolog\Registry;
use WPAL\Wordpress;

/**
 * Service calculating tax value for products.
 *
 * @package Jigoshop\Service
 */
class TaxService implements TaxServiceInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Service\CustomerServiceInterface */
	private $customers;
	private $taxClasses = [];
	private $rules;

	public function __construct(Wordpress $wp, array $classes, CustomerServiceInterface $customers)
	{
		$this->wp = $wp;
		$this->taxClasses = $classes;
		$this->customers = $customers;
	}

	/**
	 * Registers all required actions to update prices with taxes.
	 */
	public function register()
	{
		// TODO: Calculate taxes AFTER all discounts!
		$service = $this;
		$wp = $this->wp;
		$wp->addFilter('jigoshop\factory\order\fetch\after_customer', function ($order) use ($wp, $service){
			/** @var $order Order */
			// Fetch current definitions
			$definitions = $service->getDefinitions($order->getCustomer()->getTaxAddress());

			// Fetch definitions for the order
			$wpdb = $wp->getWPDB();
			$tax = [];
            if(!$order instanceof Cart && $order->getId()) {
                $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}jigoshop_order_tax jot WHERE jot.order_id = %d",
                    [$order->getId()]));

                foreach ($results as $result) {
                    $tax[$result->tax_class] = [
                        'label' => $result->label,
                        'rate' => (float)$result->rate,
                        'is_compound' => $result->is_compound,
                        'class' => $result->tax_class,
                    ];
                }
            } else {
                foreach ($definitions as $class => $definition) {
                    if (!isset($tax[$class])) {
                        $tax[$class] = $definition;
                    }
                }
            }

            $order->setTaxDefinitions($tax);

            return $order;
		}, 10, 1);
		$wp->addFilter('jigoshop\factory\order\create', function ($order) use ($service){
			/** @var $order OrderInterface */
			$order->setTaxDefinitions($service->getDefinitions($order->getCustomer()->getTaxAddress()));

			return $order;
		}, 10, 1);
		$wp->addAction('jigoshop\service\order\save', function ($order) use ($wp){
			/** @var $order Order */
			$wpdb = $wp->getWPDB();
			foreach ($order->getTaxDefinitions() as $class => $definition) {
				$data = [
					'order_id' => $order->getId(),
					'label' => $definition['label'],
					'tax_class' => $class,
					'rate' => $definition['rate'],
					'is_compound' => $definition['is_compound'],
                ];
				$wpdb->replace($wpdb->prefix.'jigoshop_order_tax', $data);
			};
		}, 10, 1);
        $wp->addAction('jigoshop\order\add_item', function($item, $order) {
            /** @var $item Order\Item */
            /** @var $order Order */
            if($item->getProduct() && $item->getProduct()->isTaxable() && $order->isTaxIncluded()) {
                if($item->getProduct() instanceof Variable) {
                    $price = $item->getProduct()->getVariation($item->getMeta('variation_id')->getValue())->getProduct()->getPrice();
                } else {
                    $price = $item->getProduct()->getPrice();
                }
                if($price == $item->getPrice()) {
                    $item->setPrice(Tax::getPriceWithoutTax($price, $item->getTaxClasses(), $order));
                }
            }
        }, 10 ,2);
		$wp->addAction('jigoshop\order\add_item', function ($item, $order) use ($service){
			/** @var $item Order\Item */
			/** @var $order Order */
			if ($item->getProduct()->isTaxable()) {
                $item->setTax($service->calculateForOrder($item, $order));
                $order->updateTaxes($service->getForOrder($item, $order));
			}

			return $item;
		}, 10, 2);
		$wp->addAction('jigoshop\order\remove_item', function ($item, $order) use ($service){
			/** @var $item Order\Item */
			/** @var $order Order */
			if ($item->getProduct()->isTaxable()) {
				$taxes = array_map(function ($tax){
					return -$tax;
				}, $service->getForOrder($item, $order));
				$order->updateTaxes($taxes);
			}

			return $item;
		}, 10, 2);
		$wp->addFilter('jigoshop\order\shipping_price', function ($price, $method, $order) use ($service){
			/** @var $order OrderInterface */
			/** @var $method Method */
			if ($method->isTaxable()) {
				return $price + $service->calculateForShipping($method, $order, $price);
			}

			return $price;
		}, 10, 3);
		$wp->addFilter('jigoshop\order\shipping_tax', function ($taxes, $method, $order) use ($service){
			/** @var $order OrderInterface */
			/** @var $method Method */
			if ($method->isTaxable()) {
				return $service->getForShipping($method, $order, $order->getShippingPrice());
			}

			return $taxes;
		}, 10, 3);
		$wp->addFilter('jigoshop\admin\order\update_product', function ($item, $order) use ($service){
			/** @var $order OrderInterface */
			/** @var $item Order\Item */
			if ($item->getProduct()->isTaxable()) {
				$item->setTax($service->calculateForOrder($item, $order));
			}

			return $item;
		}, 10, 2);
	}

	/**
	 * @param $address Customer\Address The order.
	 *
	 * @return array List of tax values per tax class.
	 */
	public function getDefinitions(Customer\Address $address)
	{
		$definitions = [];
		foreach ($this->taxClasses as $class) {
			$definition = $this->getDefinition($class, $address);
			$definitions[$class] = $definition['standard'];

			if (isset($definition['compound'])) {
				$definitions['__compound__'.$class] = $definition['compound'];
			}
		}

		return $definitions;
	}

	/**
	 * Finds and returns available tax definitions for selected parameters.
	 *
	 * @param $taxClass string Tax class.
	 * @param $address  Customer\Address Address to fetch data for.
	 *
	 * @return array Tax definition.
	 */
	public function getDefinition($taxClass, Customer\Address $address)
	{
		$taxClass = str_replace('__compound__', '', $taxClass);
		// TODO: Remember downloaded data for each address separately
		// TODO: Probably it will be good idea to update getRules() call to fetch and format only proper rules for the customer
		$rules = array_filter($this->getRules(), function ($item) use ($taxClass, $address){
			return $item['class'] == $taxClass &&
			(empty($item['country']) || $item['country'] == $address->getCountry()) &&
			(empty($item['states']) || in_array($address->getState(), $item['states'])) &&
			(empty($item['postcodes']) || in_array($address->getPostcode(), $item['postcodes']));
		});

		$standard = array_filter($rules, function ($rule){
			return !$rule['is_compound'];
		});
		$compound = array_filter($rules, function ($rule){
			return $rule['is_compound'];
		});
		$comparator = function ($a, $b){
			$aRate = 0;
			$bRate = 0;

			if (!empty($a['country'])) {
				$aRate += 1;
			}
			if (!empty($a['states'])) {
				$aRate += 1;
			}
			if (!empty($a['postcodes'])) {
				$aRate += 1;
			}

			if (!empty($b['country'])) {
				$bRate += 1;
			}
			if (!empty($b['states'])) {
				$bRate += 1;
			}
			if (!empty($b['postcodes'])) {
				$bRate += 1;
			}

			return $aRate - $bRate;
		};

		usort($standard, $comparator);
		usort($compound, $comparator);

		return ['standard' => array_pop($standard), 'compound' => array_pop($compound)];
	}

	/**
	 * Fetches and returns properly formatted list of tax rules.
	 *
	 * @return array List of rules.
	 */
	public function getRules()
	{
		if ($this->rules === null) {
			$wpdb = $this->wp->getWPDB();
			$query = "
				SELECT t.id, t.class, t.label, t.is_compound, t.rate, tl.country, tl.state, tl.postcode FROM {$wpdb->prefix}jigoshop_tax t
			  LEFT JOIN {$wpdb->prefix}jigoshop_tax_location tl ON tl.tax_id = t.id
				ORDER BY t.id
			";
			$taxes = $wpdb->get_results($query, ARRAY_A);
			$result = [];
			$processed = [];

			foreach ($taxes as $tax) {
				if (in_array($tax['id'], $processed)) {
					continue;
				}

				$processed[] = $tax['id'];
				$rule = array_filter($taxes, function ($item) use ($tax){
					return $item['id'] == $tax['id'];
				});
				$result[] = $this->formatRule($rule);
			}

			$this->rules = $result;
		}

		return $this->rules;
	}

	private function formatRule(array $source)
	{
		$item = reset($source);
		$rule = [
			'id' => $item['id'],
			'rate' => (float)$item['rate'],
			'label' => $item['label'],
			'class' => $item['class'],
			'is_compound' => $item['is_compound'] == 1,
			'country' => $item['country'],
			'states' => [],
			'postcodes' => [],
        ];

		foreach ($source as $item) {
			if (!in_array($item['state'], $rule['states']) && !empty($item['state'])) {
				$rule['states'][] = $item['state'];
			}
			if (!in_array($item['postcode'], $rule['postcodes']) && !empty($item['postcode'])) {
				$rule['postcodes'][] = $item['postcode'];
			}
		}

		return $rule;
	}

	/**
	 * @param $item  Order\Item Order item to calculate tax for.
	 * @param $order OrderInterface The order.
	 *
	 * @return float Overall tax value.
	 */
	public function calculateForOrder(Order\Item $item, OrderInterface $order)
	{
		return array_sum($this->getForOrder($item, $order));
	}

	/**
	 * @param $item  Item Order item to calculate tax for.
	 * @param $order OrderInterface The order.
	 *
	 * @return array List of tax values per tax class.
	 */
	public function getForOrder(Item $item, OrderInterface $order)
	{
		return $this->get($item->getCost(), $item->getTaxClasses(), $order->getTaxDefinitions());
	}

	/**
	 * @param float $price       Price to tax.
	 * @param array $taxClasses  List of applied tax classes.
	 * @param array $definitions List of tax definitions to use.
	 *
	 * @return array List of tax values per tax class.
	 */
	public function get($price, array $taxClasses, array $definitions)
	{
		$tax = [];
		$cost = $price;
		$standard = [];
		$compound = [];

		foreach ($taxClasses as $class) {
			if (!isset($definitions[$class])) {
				Registry::getInstance(JIGOSHOP_LOGGER)->addInfo(sprintf('No tax class: %s', $class));
				$tax[$class] = 0.0;
				continue;
			}

			$standard[$class] = $definitions[$class];

			if (isset($definitions['__compound__'.$class])) {
				$compound[$class] = $definitions['__compound__'.$class];
			}
		}

		foreach ($standard as $class => $definition) {
			$tax[$class] = $definition['rate'] * $cost / 100;
		}

		$cost += array_sum($tax);
		foreach ($compound as $class => $definition) {
			$tax['__compound__'.$class] += $definition['rate'] * $cost / 100;
		}

		return array_filter($tax);
	}

	/**
	 * @param Method         $method Method to calculate tax for.
	 * @param OrderInterface $order  Order with the shipping method.
	 * @param                $price  float Price calculated for current cart.
	 *
	 * @return float Overall tax value.
	 */
	public function calculateForShipping(Method $method, OrderInterface $order, $price)
	{
		return array_sum($this->getForShipping($method, $order, $price));
	}

	/**
	 * @param Method         $method Method to calculate tax for.
	 * @param OrderInterface $order  Order with the shipping method.
	 * @param                $price  float Price calculated for current cart.
	 *
	 * @return array List of tax values per tax class.
	 */
	public function getForShipping(Method $method, OrderInterface $order, $price)
	{
		return $this->get($price, $method->getTaxClasses(), $order->getTaxDefinitions());
	}

	/**
	 * @param float $price       Price to tax.
	 * @param array $taxClasses  List of applied tax classes.
	 * @param array $definitions List of tax definitions to use.
	 *
	 * @return float Overall tax value.
	 */
	public function calculate($price, array $taxClasses, array $definitions)
	{
		return array_sum($this->get($price, $taxClasses, $definitions));
	}

	/**
	 * @return array List of available tax classes.
	 */
	public function getClasses()
	{
		return $this->taxClasses;
	}

	/**
	 * @param                $taxClass string Tax class to get label for.
	 * @param OrderInterface $order    Order to calculate taxes for.
	 *
	 * @return string Tax class label
	 * @throws Exception When tax class is not found.
	 */
	public function getLabel($taxClass, $order)
	{
		$definitions = $order->getTaxDefinitions();

		if (!isset($definitions[$taxClass])) {
			$definitions[$taxClass] = $this->getDefinition($taxClass, $order->getCustomer()->getTaxAddress());
		}

		if (!isset($definitions[$taxClass])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf(__('No tax class: %s', 'jigoshop-ecommerce'), $taxClass));
			}

			return $taxClass;
		}

		$label = !empty($definitions[$taxClass]['label']) ? $definitions[$taxClass]['label'] : $taxClass;

		return isset($definitions[$taxClass]['rate']) ? sprintf('%s (%s%%)', $label, $definitions[$taxClass]['rate']) : '';
	}

	/**
	 * @param                $taxClass string Tax class to get label for.
	 * @param OrderInterface $order    Order to calculate taxes for.
	 *
	 * @return string Tax class rate
	 * @throws Exception When tax class is not found.
	 */
	public function getRate($taxClass, $order)
	{
		if (!in_array($taxClass, $this->taxClasses)) {
			if (WP_DEBUG) {
				throw new Exception(sprintf(__('No tax class: %s', 'jigoshop-ecommerce'), $taxClass));
			}

			return $taxClass;
		}

		$definitions = $order->getTaxDefinitions();

		if (!isset($definitions[$taxClass])) {
			$definitions[$taxClass] = $this->getDefinition($taxClass, $order->getCustomer()->getTaxAddress());
		}

		if(!isset($definitions[$taxClass]['rate'])) {
			return 0;
		}		

		return $definitions[$taxClass]['rate'];
	}

	/**
	 * @param $rule array Rule to save.
	 *
	 * @return array Saved rule.
	 */
	public function save(array $rule)
	{
		$wpdb = $this->wp->getWPDB();
		$data = [
			'class' => $rule['class'],
			'label' => $rule['label'],
			'rate' => (float)$rule['rate'],
			'is_compound' => $rule['is_compound'],
        ];

		// Process main rule data
		if ($rule['id'] === '') {
			$wpdb->insert($wpdb->prefix.'jigoshop_tax', $data);
			$rule['id'] = $wpdb->insert_id;
		} else {
			$wpdb->update($wpdb->prefix.'jigoshop_tax', $data, [
				'id' => $rule['id'],
            ]);
		}

		// Process rule locations
		if (!empty($rule['country'])) {
			$states = explode(',', $rule['states']);
			$postcodes = explode(',', $rule['postcodes']);

			if (!empty($states)) {
				$this->_removeAllStatesExcept($rule, $states);
				foreach ($states as $state) {
					$this->_removeAllPostcodesExcept($rule, $state, $postcodes);
					$this->_addPostcodes($rule, $state, $postcodes);
				}
			} else {
				$this->_removeAllPostcodesExcept($rule, '', $postcodes);
				$this->_addPostcodes($rule, '', $postcodes);
			}
		}

		return $rule;
	}

	private function _removeAllStatesExcept($rule, $states)
	{
		$wpdb = $this->wp->getWPDB();
		$values = join(',', array_filter(array_map(function ($item){
			$item = esc_sql($item);

			return "'{$item}'";
		}, $states)));
		// Support for removing all items
		if (empty($values)) {
			$values = 'NULL';
		}
		$query = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}jigoshop_tax_location WHERE state NOT IN ({$values}) AND tax_id = %d",
			[$rule['id']]
		);
		$wpdb->query($query);
	}

	private function _removeAllPostcodesExcept($rule, $state, $postcodes)
	{
		$wpdb = $this->wp->getWPDB();
		$values = join(',', array_filter(array_map(function ($item){
			$item = esc_sql($item);

			return "'{$item}'";
		}, $postcodes)));
		// Support for removing all items
		if (empty($values)) {
			$values = 'NULL';
		}
		$query = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}jigoshop_tax_location WHERE postcode NOT IN ({$values}) AND state = %s AND tax_id = %d",
			[$state, $rule['id']]
		);
		$wpdb->query($query);
	}

	private function _addPostcodes($rule, $state, $postcodes)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = [];
		$query = $wpdb->prepare("
			SELECT postcode FROM {$wpdb->prefix}jigoshop_tax_location
			WHERE tax_id = %d AND country = %s AND state = %s
		", [$rule['id'], $rule['country'], $state]);
		$existing = $wpdb->get_col($query);

		foreach ($postcodes as $postcode) {
			if (!in_array($postcode, $existing)) {
				$wpdb->insert($wpdb->prefix.'jigoshop_tax_location', [
					'tax_id' => $rule['id'],
					'country' => $rule['country'],
					'state' => $state,
					'postcode' => $postcode,
                ]);
				$ids[] = $wpdb->insert_id;
			}
		}

		return $ids;
	}

	/**
	 * @param $ids array IDs to preserve.
	 */
	public function removeAllExcept($ids)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = join(',', array_filter(array_map(function ($item){
			return (int)$item;
		}, $ids)));
		// Support for removing all items
		if (empty($ids)) {
			$ids = '0';
		}
		$query = "DELETE FROM {$wpdb->prefix}jigoshop_tax WHERE id NOT IN ({$ids})";
		$wpdb->query($query);
	}
}
