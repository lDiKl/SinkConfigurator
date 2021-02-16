<?php
namespace  DKostynenko\SinkConfigurator\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Elasticsearch\Model\ResourceModel\Index;

class ConfiguratorFieldsProvider implements AdditionalFieldsProviderInterface
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var Index
     */
    private $resourceIndex;
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param DataProvider $dataProvider
     * @param Index $resourceIndex
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        DataProvider $dataProvider,
        Index $resourceIndex
    ) {
        $this->productFactory = $productFactory;
        $this->dataProvider = $dataProvider;
        $this->resourceIndex = $resourceIndex;
    }

    public function getFields(array $productIds, $storeId)
    {
        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductSinkData($productId);
        }
        return $fields;
    }

    private function getProductSinkData($productId)
    {
        $data = ['width' => 0, 'height' => 0];

        $groups = [
            [
                'width' => 'width_overlay',
                'height' => 'height_overlay',
            ],
            [
                'width' => 'width_undercounter',
                'height' => 'height_undercounter',
            ],
            [
                'width' => 'width_flush',
                'height' => 'height_flush',
            ],
            [
                'width' => 'width_overlay',
                'height' => 'height_overlay',
            ],
        ];

        return [
            'sink_width' => (int)$data['width'],
            'sink_height' => (int)$data['height']
        ];
    }
}
