<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Controller\Vendor;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\Registry $registry,
        \Cmsmart\Marketplace\Model\VacationFactory $vacationFactory
    )
    {
        $this->customerSession = $customerSession;
        $this->_registry = $registry;
        $this->vacationFactory = $vacationFactory;
        parent::__construct($context);
    }

    /**
     * Display marketplace vendor panel
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

        $isVacation = $helper->isVacation();
        if ($isPartner) {
            /** @var \Magento\Framework\View\Result\Page resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(
                __('Vendor Panel')
            );
            
            return $resultPage;

        } else {
            return $this->resultRedirectFactory->create()
                ->setPath('marketplace/account/registry', ['_secure' => $this->getRequest()->isSecure()]);
        }
    }
}
