<?php
/**
 * Magento
 *
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @copyright   
 * @license     
 */

class Verite_Japanpost_Model_Carrier_Japanpost
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'japanpost';
    protected $_isFixed = true;
    protected $_default_condition_name = 'package_weight';

    protected $_conditionNames = array();

    protected $_defaultGatewayUrl = '';
    
    public function __construct()
    {
        parent::__construct();
        foreach ($this->getCode('condition_name') as $k=>$v) {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }

        if (!$request->getConditionName()) {
            $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

         // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        $result = Mage::getModel('shipping/rate_result');
        
        $freeShippingPrice = (float)$this->getConfigData('free_shipping');
        if (((int)$freeShippingPrice) > 0 && $request->getPackageValue() >= $freeShippingPrice) {
            $rate = array('cost' => 0, 'price' => 0);
        } else {
            $rate = $this->getRate($request);
        }

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        if (!empty($rate) && $rate['price'] >= 0) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('japanpost');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('japanpost');
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }

            $method->setPrice($shippingPrice);
            $method->setCost($rate['cost']);

            $result->append($method);
        }

        return $result;
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        return Mage::getResourceModel('japanpost/tablerate')->getRate($request);
    }

    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('japanpost')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('japanpost')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('japanpost')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('japanpost')->__('Weight (and above)'),
                'package_value'  => Mage::helper('japanpost')->__('Order Subtotal (and above)'),
                'package_qty'    => Mage::helper('japanpost')->__('# of Items (and above)'),
            ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('japanpost')->__('Invalid Table Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('japanpost'=>$this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }
    
    public function getTveriteingInfo($tveriteing)
    {
        $info = array();

        $result = $this->getTveriteing($tveriteing);

        if($result instanceof Mage_Shipping_Model_Tveriteing_Result){
            if ($tveriteings = $result->getAllTveriteings()) {
                return $tveriteings[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }
    
    public function getTveriteing($tveriteings)
    {
        if (!is_array($tveriteings)) {
            $tveriteings = array($tveriteings);
        }
        $this->_getTveriteing($tveriteings);

        return $this->_result;
    }
    
    protected function _getTveriteing($tveriteings)
    {
        $result = Mage::getModel('shipping/tveriteing_result');
        $defaults = $this->getDefaults();
        foreach($tveriteings as $tveriteing){
            $status = Mage::getModel('shipping/tveriteing_result_status');
            $status->setCarrier('japanpost');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTveriteing($tveriteing);
            $status->setPopup(1);
            $status->setUrl($this->getConfigData('gateway_url'));
            $result->append($status);
        }

        $this->_result = $result;
        return $result;
    }    
}
