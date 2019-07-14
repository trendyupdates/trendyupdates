<?php

/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Block\Sale;

class Report extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;


    /**
     * Reward constructor.
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Cmsmart\Marketplace\Model\SalesReportFactory $salesReportFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $formatDate,
        \Cmsmart\Marketplace\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_salesReportFactory = $salesReportFactory;
        $this->_productFactory = $productFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->_registry = $registry;
        $this->orderStatusFactory = $orderStatusFactory;
        $this->_formatDate = $formatDate;
        $this->timezone = $context->getLocaleDate();
        $this->logger = $context->getLogger();
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getSalesReport()) {
            $number_records = 10;
            if (isset($_GET['records'])) {
                $number_records = $_GET['records'] != "" ? $_GET['records'] : $number_records;
            }

            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.sales.report.pager'
            )->setAvailableLimit(array($number_records => $number_records))
                ->setShowPerPage(true)->setCollection(
                    $this->getSalesReport()
                );
            $this->setChild('pager', $pager);
            $this->getSalesReport()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getSalesReport()
    {
        $sellerId = $this->_helper->getSellerId();
        if (!($sellerId)) {
            return false;
        }

        $filter = '';
        $filter_orderstatus = '';
        $filter_data_frm = '';
        $filter_data_to = '';
        $from = null;
        $to = null;

        if (isset($_GET['from_date'])) {
            $filter_data_frm = $_GET['from_date'] != "" ? $_GET['from_date'] : "";
        }
        if (isset($_GET['to_date'])) {
            $filter_data_to = $_GET['to_date'] != "" ? $_GET['to_date'] : "";
        }
        if ($filter_data_to) {
            $todate = str_replace('/', '-', $filter_data_to);
            $to = date('Y-m-d', strtotime($todate));
        }
        if ($filter_data_frm) {
            $fromdate = str_replace('/', '-', $filter_data_frm);
            $from = date('Y-m-d', strtotime($fromdate));
        }
        

        $salesReportCollection = $this->_salesReportFactory->create()->getCollection()->addFieldToFilter('seller_id', $sellerId);
        $salesReportCollection->addFieldToFilter('created_at', array('datetime' => true, 'from' => $from, 'to' => $to))
            ->setOrder('id', 'DESC');

        if (isset($_GET['search'])) {
            $filter = $_GET['search'] != "" ? $_GET['search'] : "";

            $productCollection = $this->_productFactory->create()->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('name', array('like' => "%" . $filter . "%"));
            $productIds = array();
            foreach ($productCollection as $item) {
                $productIds[] = $item->getEntityId();
            }

            $salesReportCollection->addFieldToFilter('product_id', array('in' => $productIds));
        }

        if (isset($_GET['orderstatus']) && $_GET['orderstatus'] != '') {
            $filter_orderstatus = $_GET['orderstatus'];

            $orderCollection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter('status', $filter_orderstatus);
            $orderIds = array();
            foreach ($orderCollection as $item) {
                $orderIds[] = $item->getEntityId();
            }

            $salesReportCollection->addFieldToFilter('order_id', array('in' => $orderIds));
        }

        return $salesReportCollection->addFieldToFilter('price', array('neq' => 0));
    }

    public function getOrderCollection($orderId) {
        return $orderCollection = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', $orderId);
    }

    public function getOrderData($orderId){
        $orderCollection = $this->getOrderCollection($orderId);
        $order = array();
        foreach ($orderCollection as $item) {
            $order[] = $item->getData();
        }
        return $order[0];
    }

    public function getProduct($productId) {
        $product = $this->_productFactory->create()->load($productId);
        return $product;
    }

    public function getStatusLabel($code)
    {
        $status = $this->orderStatusFactory->create()->load($code);
        return $status->getLabel();
    }

    public function getOrderStatus() {
        $status = $this->orderStatusFactory->create()->getCollection();
        return $status;
    }

}
