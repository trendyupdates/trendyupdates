<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */
namespace Cmsmart\Marketplace\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Cmsmart\Marketplace\Controller\Adminhtml\AbstractMassAction;
use Cmsmart\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Cmsmart\Marketplace\Model\SellerFactory;
use Cmsmart\Marketplace\Model\SellerdataFactory;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassAction
{
    const ADMIN_RESOURCE = 'Cmsmart_Marketplace::marketplace_seller';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SellerFactory $manageFactory,
        SellerdataFactory $sellerdataFactory,
        \Cmsmart\Marketplace\Helper\Data $marketplaceHelperData
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
        $this->modelData = $sellerdataFactory;
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

            $modelDataCollection = $this->modelData->create()->getCollection()->addFieldToFilter('seller_id', $item->getId());
            $sellerDataId = '';
            if (!empty($modelDataCollection)) {
                foreach ($modelDataCollection as $data) {
                    $sellerDataId = $data->getId();
                }
                $modelData = $this->_objectManager->create('Cmsmart\Marketplace\Model\Sellerdata');
                if ($sellerDataId) {
                    $modelData->load($sellerDataId);
                    $modelData->delete();
                }
            }


            $this->sendMail($model);
            $itemsDeleted++;
        }

        if ($itemsDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 seller(s) were deleted.', $itemsDeleted));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while delete seller');
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    private function sendMail($data)
    {

        $helper = $this->_marketplaceHelperData;
        $sellerName = '';
        $sellerEmail = '';
        $sellerId = '';

        if ($data) {
            $sellerId = $data['seller_id'];
        }

        if ($sellerId) {
            $customer = $this->_objectManager->get(
                'Magento\Customer\Model\Customer'
            )->load($sellerId);

            $sellerName = $customer->getFirstname() . ' ' . $customer->getLastname();
            $sellerEmail = $customer->getEmail();
        }


        $emailTempVariables = [];
        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail = $adminStoremail ?
            $adminStoremail : $helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $emailTempVariables['admin'] = $adminUsername;
        $emailTempVariables['seller_status'] = __("deleted.");

        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];

        $helper->sendSellerMail(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}