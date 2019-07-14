<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */
namespace Cmsmart\Marketplace\Controller\Adminhtml\Locator;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Cmsmart\Marketplace\Controller\Adminhtml\AbstractMassAction;
use Cmsmart\Marketplace\Model\ResourceModel\Location\CollectionFactory;
use Cmsmart\Marketplace\Model\LocationFactory;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassAction
{

    const ADMIN_RESOURCE = 'Cmsmart_Marketplace::marketplace_locator';
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LocationFactory $manageFactory,
        \Cmsmart\Marketplace\Helper\Data $marketplaceHelperData
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
        $this->_marketplaceHelperData  = $marketplaceHelperData;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction($collection)
    {
        $itemsDeleted = 0;
        foreach ($collection as $item) {
            $model = $this->model->create()->load($item->getId());
            $model->delete();
            $itemsDeleted++;
        }

        if ($itemsDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 locator(s) were deleted.', $itemsDeleted));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while delete locator');
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}