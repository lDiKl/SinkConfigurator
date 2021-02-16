<?php

namespace DKostynenko\SinkConfigurator\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;

class Copysink extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productAction;

    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        ProductAction $productAction
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->productAction     = $productAction;
    }

    /**
     *
     * @return type
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('catalog/product');
        $error = false;
        try {
            $productId = $this->getRequest()->getParam('product_id', null);
            $product = $this->productRepository->getById($productId);
            $resultRedirect->setPath('catalog/product/edit', array('id' => $product->getId()));
            $childIds = $product->getTypeInstance()->getUsedProductIds($product);
            $childIds = array_map('intval', $childIds);
            $data = [];
            foreach($this->getAttributes() as $_data)
            {
                $data[$_data] = $product->getData($_data);
            }

            $this->productAction->updateAttributes($childIds, $data, 0);
            $this->messageManager->addSuccessMessage(__('Data has been copied to the Child Products'));
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage('Could not save');
        }
        return $resultRedirect;
    }

    /**
     *
     * @return array
     */
    protected function getAttributes()
    {
        return [
            'width_overlay',
            'height_overlay',
            'width_undercounter',
            'height_undercounter',
            'width_flush',
            'height_flush',
            'width_overlay',
            'height_overlay',
            'width_outerdemision',
            'height_outerdemision'
        ];
    }
}
