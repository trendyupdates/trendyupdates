<?php
namespace Netbaseteam\Opc\Block\Adminhtml\Order;

use Magento\Sales\Model\Order;

class GiftWrap extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $data);
    }
    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();

        $title = 'Gift Wrap';

        $quoteId = $this->_order->getQuoteId();

        $quote = $this->quoteFactory->create()->load($quoteId);
        $giftWrapAmount = $quote->getData('giftwrap_amount');

        if($giftWrapAmount){
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'giftwrap',
                    'strong' => false,
                    'value' => $giftWrapAmount,
                    'label' => __($title),
                ]
            );
            $parent->addTotal($customAmount, 'giftwrap');
        }
        return $this;
    }
    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}