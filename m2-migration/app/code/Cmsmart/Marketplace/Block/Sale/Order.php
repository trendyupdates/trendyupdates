<?php

/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Block\Sale;

class Order extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orders;


    /**
     * Reward constructor.
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Cmsmart\Marketplace\Model\OrderFactory $sellerOrderFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\Registry $registry,
        \Cmsmart\Marketplace\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_sellerOrderFactory = $sellerOrderFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->_registry = $registry;
        $this->logger = $context->getLogger();
        $this->_storeManager = $context->getStoreManager();
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
        if ($this->getOrders()) {
            $number_records = 10;
            if (isset($_GET['records'])) {
                $number_records = $_GET['records'] != "" ? $_GET['records'] : $number_records;
            }

            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.sales.report.pager'
            )->setAvailableLimit(array($number_records => $number_records))
                ->setShowPerPage(true)->setCollection(
                    $this->getOrders()
                );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
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

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated
     */
    private function getSellerOrderCollection()
    {
        $sellerId = $this->_helper->getSellerId();
        return $this->_sellerOrderFactory->create()->getCollection()->addFieldToFilter('seller_id',$sellerId);
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        $sellerId = $this->_helper->getSellerId();
        if (!($sellerId)) {
            return false;
        }

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


        $sellerOrderCollection = $this->getSellerOrderCollection();

        $orderIds = array();
        foreach ($sellerOrderCollection as $item ) {
            array_push($orderIds, $item->getOrderId());
        }

        $orderCollection = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', array('in' => $orderIds));
        $orderCollection->addFieldToFilter('created_at', array('datetime' => true, 'from' => $from, 'to' => $to))
            ->setOrder('increment_id', 'DESC');

        if (isset($_GET['orderstatus']) && $_GET['orderstatus'] != '') {
            $filter_orderstatus = $_GET['orderstatus'];

            $orderCollection->addFieldToFilter('status', $filter_orderstatus);
        }

        return $orderCollection;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('marketplace/order/view/', ['order_id' => $order->getId()]);
    }

}
