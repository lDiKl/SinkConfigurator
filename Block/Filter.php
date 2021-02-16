<?php
namespace DKostynenko\SinkConfigurator\Block;

class Filter extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    public $listProduct;
    public $activeAttributes =[];
    protected $attributes = ['width_overlay', 'height_overlay'];

    private $layerResolver;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->listProduct = $listProduct;
        $this->layerResolver = $layerResolver;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context);
    }

    public function getValue($name)
    {
        return $this->getRequest()->getParam($name);
    }

    public function getFilterUrl()
    {
        $query = ['width' => ''];
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    public function getCurrentCategoryId()
    {
        return $this->getCurrentCategory()->getId();
    }

    public function getAttributes()
    {
        foreach ($this->attributes as $attribute) {
            $attributeObj = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
            $this->activeAttributes[$attribute] = $attributeObj->getFrontendLabel();
        }
        return $this->activeAttributes;
    }

    public function canShow()
    {
        return $this->getCurrentCategory()->getIsRinse();
    }
}
