<?php

namespace DKostynenko\SinkConfigurator\Observer;

use Magento\Framework\Event\ObserverInterface;

class SinkConfiguratorObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    protected $attributes = ['width_overlay', 'height_overlay'];

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $foundAttributes = false;
        $collection = $observer->getEvent()->getCollection();
        /**
         * @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection
         */

        foreach ($this->_request->getParams() as $param=>$value) {
            if (in_array($param, $this->attributes) && $value) {
                $foundAttributes = true;
            }
        }
        return $this;
    }
}
