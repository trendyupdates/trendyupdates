<?php

namespace Cmsmart\Marketplace\Block\Catalog\Product\Edit\Tab;

/*
 * Cmsmart Marketplace Product Create Block
 */
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\GoogleOptimizer\Model\Code as ModelCode;
use Cmsmart\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Associated extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var ModelCode
     */
    protected $_modelCode;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Config      $wysiwygConfig
     * @param Product                                $product
     * @param Category                               $category
     * @param ModelCode                              $modelCode
     * @param HelperData                             $helperData
     * @param ProductRepositoryInterface             $productRepository
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        Product $product,
        Category $category,
        ModelCode $modelCode,
        HelperData $helperData,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_product = $product;
        $this->_category = $category;
        $this->_modelCode = $modelCode;
        $this->_helperData = $helperData;
        $this->_productRepository = $productRepository;
        $this->_coreRegistry = $context->getRegistry();
        parent::__construct($context, $data);
    }

    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getCategory()
    {
        return $this->_category;
    }

    /**
     * Get Googleoptimizer Fields Values.
     *
     * @param ModelCode|null $experimentCodeModel
     *
     * @return array
     */
    public function getGoogleoptimizerFieldsValues()
    {
        $entityId = $this->getRequest()->getParam('id');
        $storeId = $this->_helperData->getCurrentStoreId();
        $experimentCodeModel = $this->_modelCode->loadByEntityIdAndType($entityId, 'product', $storeId);
        $result = [];
        $result['experiment_script'] =
            $experimentCodeModel ? $experimentCodeModel->getExperimentScript() : '';
        $result['code_id'] =
            $experimentCodeModel ? $experimentCodeModel->getCodeId() : '';

        return $result;
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }
}
