<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Controller\Adminhtml\Vacation;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Cmsmart_Marketplace::marketplace_vacation';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Cmsmart\Marketplace\Helper\Data'
        );
        $isVacation = $helper->isVacation();

        if ($isVacation == 1) {
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Cmsmart_Marketplace::marketplace_vacation');
            $resultPage->addBreadcrumb(__('Manage Vacations'), __('Manage Vacations'));
            $resultPage->addBreadcrumb(__('Marketplace'), __('Marketplace'));
            $resultPage->getConfig()->getTitle()->prepend(__('Manage Vacations'));

            return $resultPage;
        } else {
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
