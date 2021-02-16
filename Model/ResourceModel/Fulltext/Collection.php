<?php
namespace DKostynenko\SinkConfigurator\Model\ResourceModel\Fulltext;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    public function resetTotals()
    {
        $this->_totalRecords = null;
    }

}
