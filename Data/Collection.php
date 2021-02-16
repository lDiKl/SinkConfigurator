<?php
namespace DKostynenko\SinkConfigurator\Data;

class Collection extends \Magento\Framework\Data\Collection
{
    public function resetTotals()
    {
        $this->_totalRecords = null;
    }
}
