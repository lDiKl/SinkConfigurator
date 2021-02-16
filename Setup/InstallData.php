<?php

namespace DKostynenko\SinkConfigurator\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    // Sink attributes
    protected $attributes = ['width_overlay', 'height_overlay', 'width_undercounter', 'height_undercounter', 'width_flush', 'height_flush'];

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'is_rinse',
            [
                'type'     => 'int',
                'label'    => 'Ist Spülen Konfigurator',
                'input'    => 'boolean',
                'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible'  => true,
                'default'  => '0',
                'required' => false,
                'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'    => 'Display Settings',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'sink_width',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Sink Width',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'sink_height',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Sink Height',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        foreach ($this->attributes as $attributeCode) {
            //set all attrubutes as 'use in search' so they will be indexed in elasticsearch
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\product::ENTITY,
                $attributeCode,
                'is_searchable',
                1
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\product::ENTITY,
                $attributeCode,
                'search_weight',
                10
            );
        }

    }
}
