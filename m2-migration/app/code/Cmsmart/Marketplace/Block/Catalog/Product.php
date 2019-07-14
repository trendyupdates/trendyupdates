<?php

/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Block\Catalog;

class Product extends \Magento\Framework\View\Element\Template
{
    // protected $_mpColletion;
    // protected $_productCollection;
    
    /**
     * @var \Cmsmart\Marketplace\Model\ProductFactory
     */
    protected $_marketplaceProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image $imageHelper
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $_typeFactory;


    /**
     * Reward constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product $productCollection
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Cmsmart\Marketplace\Model\ProductFactory $marketplaceProductFactory,
        \Cmsmart\Marketplace\Model\SellerdataFactory $sellerdataFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $contextInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Stdlib\DateTime\DateTime $formatDate,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Cmsmart\Marketplace\Helper\Data $helper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        array $data = []
    )
    {
        $this->_marketplaceProductFactory = $marketplaceProductFactory;
        $this->_sellerdataFactory = $sellerdataFactory;
        $this->_productFactory = $productFactory;
        $this->logger = $context->getLogger();
        $this->imageHelper = $context->getImageHelper();
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $context->getStoreManager();
        $this->context = $contextInterface;
        $this->_registry = $context->getRegistry();
        $this->formkey = $formKey;
        $this->_formatDate = $formatDate;
        $this->_typeFactory = $typeFactory;
        $this->_helper = $helper;
        $this->_stockItem = $stockItem;
        $this->_orderItemFactory = $orderItemFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getProductCollection()) {
            $number_records = 10;
            if (isset($_GET['records'])) {
                $number_records = $_GET['records'] != "" ? $_GET['records'] : $number_records;
            }

            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.product.pager'
            )->setAvailableLimit(array($number_records => $number_records))
                ->setShowPerPage(true)->setCollection(
                    $this->getProductCollection()
                );
            $this->setChild('pager', $pager);
            $this->getProductCollection()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * function to get rewards point transaction of customer
     *
     * @return reward product collection
     */
    public function getProductCollection()
    {
        $filter = '';
        $filterStatus = '';
        $filterFrom = '';
        $filterTo = '';
        $from = null;
        $to = null;

        $mpCollection = $this->getMpCollection();

        $products = array();
        foreach ($mpCollection as $data) {
            array_push($products, $data->getProductId());
        }

        if (isset($_GET['search'])) {
            $filter = $_GET['search'] != "" ? $_GET['search'] : "";
        }
        if (isset($_GET['prostatus'])) {
            $filterStatus = $_GET['prostatus'] != "" ? $_GET['prostatus'] : "";
        }
        if (isset($_GET['from_date'])) {
            $filterFrom = $_GET['from_date'] != "" ? $_GET['from_date'] : "";
        }
        if (isset($_GET['to_date'])) {
            $filterTo = $_GET['to_date'] != "" ? $_GET['to_date'] : "";
        }
        if ($filterTo) {
            $todate = str_replace('/', '-', $filterTo);
            $to = date('Y-m-d', strtotime($todate));
        }
        if ($filterFrom) {
            $fromdate = str_replace('/', '-', $filterFrom);
            $from = date('Y-m-d', strtotime($fromdate));
        }


        $collection = $this->getProducts()
            ->addFieldToFilter('name', array('like' => "%" . $filter . "%"))
            ->setOrder('entity_id', 'DESC');
        if ($filterStatus || $filterStatus == 0) {
            $collection->addFieldToFilter('status', array('like' => "%" . $filterStatus . "%"));
        }


        $collection->addFieldToFilter('created_at', array('datetime' => true, 'from' => $from, 'to' => $to))
            ->addFieldToFilter('entity_id', array('in' => $products))
            ->setOrder('entity_id', 'DESC');

        //get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest
        ()->getParam('limit') : 10;

        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $this->logger->info("Here reward collection: " . $collection->getSelect());
        $this->logger->info("Here reward collection: Page:" . $page . " Page size :"
            . $pageSize);
            
        return $collection;
    }

    public function getProductInfomation($productId)
    {
        $product = $this->_productFactory->create()->load($productId);
        return $product;
    }

    public function getSpecialProduct()
    {
        $collection = $this->getProducts();

        $productIds=array();
        foreach($collection as $data){
            if($data->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $product = $data->getTypeInstance()->getUsedProducts($data);
                foreach ($product as $item) {
                    array_push($productIds,$item->getEntityId());
                }
            }
        }
        return $productIds;
    }

    public function getQtyConfirmed($productId) {
        $product = $this->getProductInfomation($productId);
        return $this->_stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }

    public function getQtySold($productId) {
        $orderCollection = $this->_orderItemFactory->create()->getCollection()
            ->addFieldToFilter('product_id', $productId);
        $qtySold = 0;
        foreach ($orderCollection as $item) {
            $qtySold = $item->getQtyOrdered() - $item->getQtyCanceled();
        }

        return $qtySold;
    }

    public function getQtyPending($productId) {
        $product = $this->getProductInfomation($productId);
        return $product->getData('quantity_and_stock_status')['qty'];
    }

    public function getThumbnail($productId)
    {
        $product = $this->getProductInfomation($productId);
        $imageHelper = $this->imageHelper->init($product, 'product_page_image_large')->resize(55);
        $src = $imageHelper->getUrl();
        $alt = $this->getAlt($product) ?: $imageHelper->getLabel();

        $imageHtml = "<img alt='$alt' src='$src' class='admin__control-thumbnail' />";
        return $imageHtml;
    }

    public function getFormKey()
    {
        return $this->formkey->getFormKey();
    }

    public function formatStatus($value)
    {
        $status = '';
        if ($value == 0) {
            $status = "Pending";
        } elseif ($value == 1) {
            $status = "Approved";
        } elseif ($value == 2) {
            $status = "Disapproved";
        }
        return $status;
    }

    public function getAllowedProductTypes()
    {
        $allowed = explode(',', $this->_helper->getAllowedProductTypes());
        $types = $this->_typeFactory->create()->getTypes();
        $this->_options = array();
        foreach ($types as $d) {
            if (in_array($d['name'], $allowed)) {
                $this->_options[] = ['label' => $d['label'], 'value' => $d['name']];
            }
        }
        return $this->_options;
    }

    public function getAllowedSets(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coll = $objectManager->create(\Magento\Catalog\Model\Product\AttributeSet\Options::class);

        $allowed=explode(',',$this->_helper->getAllowedAttributesetIds());

        foreach($coll->toOptionArray() as $d){
            if(in_array($d['value'], $allowed)) {
                $options[] = ['label' => $d['label'], 'value' => $d['value']];
            }
        }
        return $options;
    }

    public function getMpCollection()
    {
        // if ($this->_mpColletion == null) {
            $sellerId = $this->_helper->getSellerId();
            $mpCollection = $this->_marketplaceProductFactory
                ->create()->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
        // }
            return $mpCollection;
        // return $this->_mpColletion;
    }

    public function getMpId($productId)
    {
        $mpId = '';
        $sellerId = $this->_helper->getSellerId();

        $collection = $this->_marketplaceProductFactory
            ->create()->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('seller_id', $sellerId);
        if ($collection->getData()) {
            foreach ($collection as $item) {
                $mpId = $item->getId();
            }
        }
        return $mpId;
    }

    public function getProducts()
    {
        // if ($this->_productCollection == null) {
            $product = $this->_productFactory->create()->getCollection()
                ->addAttributeToSelect('*');
        // }
        
            return $product;
        // return $this->_productCollection;
    }

    public function getSellerData () {
        $sellerId = $this->_helper->getSellerId();

        $sellerDataCollection = $this->_sellerdataFactory->create()->getCollection()
            ->addFieldToFilter('seller_id',$sellerId);

        $seller=array();
        foreach($sellerDataCollection as $data){
            array_push($seller,$data->getData());
        }
        if ($seller) {
            return $seller[0];
        } else {
            return null;
        }
    }

}
