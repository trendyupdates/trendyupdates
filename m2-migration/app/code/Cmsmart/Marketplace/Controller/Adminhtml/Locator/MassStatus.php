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
class MassStatus extends AbstractMassAction
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
        \Cmsmart\Marketplace\Helper\Data $marketplaceHelperData,
        \Magento\Framework\Url $urlBuilder
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
        $this->_marketplaceHelperData  = $marketplaceHelperData;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction($collection)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int) $this->getRequest()->getParam('status');

        $itemsSelected = 0;
        foreach ($collection->getAllIds() as $itemId) {
            $model = $this->model->create()->load($itemId);
            $model->setStatus($status);
            $model->save();
            $itemsSelected++;
        }

        if ($itemsSelected) {
            $this->messageManager->addSuccess(__('A total of %1 locator(s) were updated.', $itemsSelected));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while update locator');
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