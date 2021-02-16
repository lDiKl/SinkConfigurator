<?php
namespace DKostynenko\SinkConfigurator\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Psr\Log\LoggerInterface;

class Adapter extends \Magento\Elasticsearch7\SearchAdapter\Adapter
{
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var AggregationBuilder
     */
    protected $aggregationBuilder;

    /**
     * @var QueryContainerFactory
     */
    protected $queryContainerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository $productRepository,
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Empty response from Elasticsearch
     *
     * @var array
     */
    protected static $emptyRawResponse = [
        "hits" => [
                "hits" => []
            ],
        "aggregations" => [
                "price_bucket" => [],
                "category_bucket" => [
                        "buckets" => []

                    ]
            ]
    ];

    protected $sinkAttributes = ['width_overlay', 'height_overlay'];
    protected $sinkMappings = ['width_overlay' => 'sink_width', 'height_overlay'=> 'sink_height'];

    protected $besteckAttributes = ['bwidth', 'bheight'];

    protected $margin = 10;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param QueryContainerFactory $queryContainerFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        QueryContainerFactory $queryContainerFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->_request = $request;
        $this->logger = $logger;
    }

    /**
     * Search query
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request) : QueryResponse
    {

        //if(!$this->_request->getParam('autosugest')) {
            $searchQuery = $this->_request->getParam('q');
            if(!$searchQuery && $this->_request->getParam('autosugest')) {
                $tempSearchQuery = $this->_request->getParam('searchCriteria');
                foreach ($tempSearchQuery['filterGroups'][0]['filters'] as $searchFilter) {
                    if(isset($searchFilter['field']) && $searchFilter['field'] == 'search_term') {
                        $searchQuery = $searchFilter['value'];
                    }
                }
            }

            if($searchQuery && !preg_match('/^\S.*\s.*\S$/', $searchQuery) && mb_strlen($searchQuery) > 5) {

                $connection = $this->resourceConnection->getConnection();
                $sql = $connection
                    ->select()
                    ->from($connection->getTableName('catalog_product_entity'), ['entity_id', 'sku'])
                    ->where('sku LIKE "%'.$searchQuery.'%"');
                $result = $connection->fetchAll($sql);

                if($result && is_array($result) && count($result) == 1) {
                    $sql = $connection
                        ->select()
                        ->from($connection->getTableName('catalog_product_entity'), ['entity_id', 'sku'])
                        ->where('sku = ?', $searchQuery);
                    $result = $connection->fetchAll($sql);
                    if($result && is_array($result) && count($result) == 1) {
                        $product = $this->productRepository->get($searchQuery);

                        if(!$this->_request->getParam('autosugest')) {
                            header('Location: '.$product->getProductUrl());
                            die();
                        }
                        else {
                            $queryResponse = $this->responseFactory->create(
                                [
                                    'documents' => [
                                        0 => [
                                            '_index' => 'magento2_product_1_v2',
                                            '_type' => 'document',
                                            '_id' => $product->getId(),
                                            '_score' => 138.19687,
                                            '_source' => [
                                                'image' => $product->getImage(),
                                                'price_0_1' => $product->getPrice(),
                                                'name' => $product->getName(),
                                            ]

                                        ]
                                    ],
                                    'aggregations' => [],
                                    'total' => 1
                                ]
                            );

                            return $queryResponse;
                        }
                    }
                }


            }

        $client = $this->connectionManager->getConnection();
        $aggregationBuilder = $this->aggregationBuilder;
        $query = $this->mapper->buildQuery($request);

        foreach ($this->_request->getParams() as $param=>$value) {
            if (in_array($param, $this->sinkAttributes) && $value) {
                $query['body']['query']['bool']['must'][]['range'][$this->sinkMappings[$param]] =
                    [   'gte' => (int)$value - $this->margin,
                        'lte' => (int)$value    ];
            }
        }

        $bwidth = intval($this->_request->getParam('bwidth'));
        $bheight = intval($this->_request->getParam('bheight'));

        //@TODO: Check Besteckeinsatz configurator
        if ($bwidth > 0) {
            $query['body']['query']['bool']['must'][]['range']['min_width'] =  ['lte' => $bwidth];
            $query['body']['query']['bool']['must'][]['range']['max_width'] = ['gte' => $bwidth];
        }

        if ($bheight > 0) {
            $query['body']['query']['bool']['must'][]['range']['min_depth'] =  ['lte' => $bheight];
            $query['body']['query']['bool']['must'][]['range']['max_depth'] = ['gte' => $bheight];
        }


        if ($this->_request->getParam('autosugest') == 1) {
            unset($query['body']['aggregations']);
            $query['index'] = 'magento2_product_1';
            $query['body']['_source'][] = 'name';
            $query['body']['_source'][] = 'image';
            $query['body']['_source'][] = 'price_0_1';
            $query['body']['size'] = 6;

            //@TODO: SKU with parent_
        }

        if (isset($query['body']['query']['bool']['should'])) {
            //if there is should we are probably in search not in catalog
            foreach ($query['body']['query']['bool']['should'] as $k=>$item) {
                if (isset($item['match'])) {
                    $term = array_key_first($item['match']);
                    if(in_array($term, ['meta_keyword', '_search', 'description', 'sku'])) {
                        $query['body']['query']['bool']['should'][$k]['match'][$term]['minimum_should_match'] = '100%';
                    }
                    if($term == 'sku') {
                        $query['body']['query']['bool']['should'][$k]['match'][$term]['boost'] = 20;
                    }
                }
            }
        }

        $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $query]));

        try {
            $rawResponse = $client->query($query);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            // return empty search result in case an exception is thrown from Elasticsearch
            $rawResponse = self::$emptyRawResponse;
        }

        $rawDocuments = $rawResponse['hits']['hits'] ?? [];
        $queryResponse = $this->responseFactory->create(
            [
                'documents' => $rawDocuments,
                'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                'total' => $rawResponse['hits']['total']['value'] ?? 0
            ]
        );
        return $queryResponse;
    }
}
