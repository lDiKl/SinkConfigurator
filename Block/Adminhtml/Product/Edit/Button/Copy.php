<?php

namespace DKostynenko\SinkConfigurator\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;

class Copy extends Generic {
    public function getButtonData()
    {
        if ($this->getProduct() && $this->getProduct()->getId()
                && (float)$this->getProduct()->getHeightOverlay() > 0)
        {
            $message = 'Haben Sie das Produkt vorher gespeichert?';
            return [
                'label' => __('Spülen Werte in Einzelartikel Kopieren'),
                'on_click' => "confirmSetLocation('{$message}', '{$this->getCopyUrl()}')",
                'sort_order' => 100
            ];
        }
        return [];
    }

    protected function getCopyUrl()
    {
        return $this->getUrl('sinkconfigurator/product/copySink', array('product_id' => $this->getProduct()->getId()));
    }
}
