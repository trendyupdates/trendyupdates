<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Controller\Transaction;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class ListTransaction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry
    )
    {
        $this->customerSession = $customerSession;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * Display marketplace form registry
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Cmsmart\Marketplace\Helper\Data'
        );
        $isPartner = $helper->getSellerId();
        if ($isPartner) {
            $this->_registry->register('sellerId', $this->customerSession->getCustomer()->getId());

            /** @var \Magento\Framework\View\Result\Page resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(
                __('Transactions')
            );
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()
                ->setPath('marketplace/account/registry', ['_secure' => $this->getRequest()->isSecure()]);
        }
    }
}
