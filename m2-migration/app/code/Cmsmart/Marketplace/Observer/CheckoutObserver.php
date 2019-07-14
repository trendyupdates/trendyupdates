<?php

namespace Cmsmart\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Unserialize\Unserialize;


class CheckoutObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * [$_coreSession description].
     *
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;


    /**
     * @var Unserialize
     */
    protected $_unserializer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param SessionManager $coreSession
     * @param QuoteRepository $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Unserialize $unserializer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        OrderRepositoryInterface $orderRepository,
        \Cmsmart\Marketplace\Model\ProductFactory $marketplaceProductFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Weee\Block\Item\Price\Renderer $itemPriceRenderer,
        \Cmsmart\Marketplace\Model\SellerFactory $sellerFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_orderRepository = $orderRepository;
        $this->_marketplaceProductFactory = $marketplaceProductFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->itemPriceRenderer = $itemPriceRenderer;
        $this->sellerFactory = $sellerFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Sales Order Place Success event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        $lastOrderId = $orderIds[0];

        $mpCollection = $this->_marketplaceProductFactory
            ->create()->getCollection();

        $mpProductIds = array();
        foreach ($mpCollection as $item) {
            $mpProductIds[] = $item->getProductId();
        }

        $orderItems = $this->orderItemFactory->create()->getCollection()->addFieldToFilter('order_id', $lastOrderId);
        $rowTotal = 0;
        $sellerAmount = 0;
        $countProduct = 0;
        $countSeller = 0;

        $seller_arr = array();

        foreach ($orderItems as $orderItem) {
            $sellerOrder = $this->_objectManager->create(
                'Cmsmart\Marketplace\Model\Order'
            );

            $productId = $orderItem->getProductId();

            if (in_array($productId, $mpProductIds)) { /* foreach products in order */

                $countProduct += $orderItem->getQtyOrdered();

                $mpCollection1 = $this->_marketplaceProductFactory
                    ->create()->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('product_id', $productId);

                $sellerId = 0;

                foreach ($mpCollection1 as $item) { /* foreach seller */
                    $sellerId = $item->getSellerId();

                    if (!in_array($sellerId, $seller_arr)) {
                        $seller_arr[] = $sellerId;
                        $ret = $this->getProductInOrderOfSeller($productId, $lastOrderId, $sellerId);
                        $rowTotal = $ret["row_total"];
                        $countProduct = $ret["countProduct"];
                        $productIds = $ret["product_ids"];
                        $shippingMethods = $ret["shipping_methods"];
                        $shippingAmount = $ret["shippingAmount"];

                        $this->saveSellerOrder($lastOrderId, $sellerOrder,$sellerId, $productIds, $rowTotal , $countProduct, $shippingMethods, $shippingAmount);
                    }
                }

                $proPrice = $orderItem->getPrice();
                $earning = $this->itemPriceRenderer->getTotalAmount($orderItem);
                $countReport = $orderItem->getQtyOrdered();
                $this->saveSalesReport($lastOrderId, $sellerId, $proPrice, $earning, $countReport, $productId);
            }
        }
    }

    public function getProductInOrderOfSeller($pid, $oid, $sellerID)
    {
        $mpCollection = $this->_marketplaceProductFactory->create()->getCollection();

        $mpProductIds = array();
        $ret = array();
        $orderItems = $this->orderItemFactory->create()->getCollection()->addFieldToFilter('order_id', $oid);

        foreach ($mpCollection as $item) {
            if ($sellerID == $item->getSellerId()) { /* foreach products in order */
                $mpProductIds[] = $item->getProductId();
            }
        }

        $row_total = 0; $countProduct = 0; $countReport= 0;
        $prouductList = '';
        foreach ($orderItems as $orderItem) {
            if(in_array($orderItem->getProductId(), $mpProductIds) && $orderItem->getQtyOrdered()){
                $prouductList .= ','.$orderItem->getProductId();
                $row_total += $this->itemPriceRenderer->getTotalAmount($orderItem);
                $proPrice = $orderItem->getPrice();
                if ($proPrice > 0) {
                    $countProduct += $orderItem->getQtyOrdered();
                    $countReport = $orderItem->getQtyOrdered();
                }
                $earning = $this->itemPriceRenderer->getTotalAmount($orderItem);
            }
        }

        $ret["row_total"] = $row_total;
        $ret["countProduct"] = $countProduct;
        $ret["product_ids"] = trim($prouductList,',');
        $ret["shipping_methods"] = "";
        $ret["shippingAmount"] = "";
        return $ret;
    }

    public function saveSellerOrder($lastOrderId,$sellerOrder, $sellerId, $productIds, $rowTotal, $countProduct, $shippingMethods, $shippingAmount) {
        $mpCollection2 = $this->sellerFactory->create()->getCollection()->addFieldToFilter('seller_id', $sellerId);
        $countSeller = 0;
        $commissionAmount = 0;
        $commissionType = 0;
        $fixedOrPecentage = 0;
        $sellerAmount = 0;
        $commission = 0;

        $seller = $this->customerFactory->create()->load($sellerId);

        $sellerName = '';
        if ($seller) {
            $sellerName = $seller->getName();
        }
        foreach ($mpCollection2 as $item) {
            $commissionAmount = $item->getCommissionAmount();
            $commissionType = $item->getCommissionType();
            $fixedOrPecentage = $item->getFixedOrPercentage();

            if ($fixedOrPecentage) {
                $sellerAmount = $rowTotal - $commissionAmount * $rowTotal / 100;
            } else {
                if ($commissionType) {
                    $sellerAmount = $rowTotal - $commissionAmount;
                } else {
                    $sellerAmount = $rowTotal - $commissionAmount * $countProduct;
                }
            }
            $commission = $rowTotal - $sellerAmount;

            $sellerOrder->setOrderId($lastOrderId);
            $sellerOrder->setSellerId($sellerId);
            $sellerOrder->setSellerName($sellerName);
            $sellerOrder->setProductIds($productIds);
            $sellerOrder->setShippingMethods($shippingMethods);
            $sellerOrder->setShippingAmount($shippingAmount);
            $sellerOrder->setPaidStatus('pending');
            $sellerOrder->setRowTotal($rowTotal);
            $sellerOrder->setSellerAmount($sellerAmount);
            $sellerOrder->setCommission($commission);
            $sellerOrder->save();

            $countSeller++;
        }
    }

    public function saveSalesReport($lastOrderId, $sellerId, $proPrice, $earning, $countReport, $productId) {
        $salesReport = $this->_objectManager->create(
            'Cmsmart\Marketplace\Model\SalesReport'
        );

        $salesReport->setOrderId($lastOrderId);
        $salesReport->setSellerId($sellerId);
        $salesReport->setProductId($productId);
        $salesReport->setPrice($proPrice);
        $salesReport->setEarning($earning);
        $salesReport->setQty($countReport);
        $salesReport->save();
    }
}