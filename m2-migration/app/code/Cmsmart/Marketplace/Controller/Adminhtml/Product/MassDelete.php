<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */
namespace Cmsmart\Marketplace\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Cmsmart\Marketplace\Controller\Adminhtml\AbstractMassAction;
use Cmsmart\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Cmsmart\Marketplace\Model\ProductFactory;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassAction
{
    const ADMIN_RESOURCE = 'Cmsmart_Marketplace::marketplace_product';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ProductFactory $manageFactory,
        \Cmsmart\Marketplace\Model\SellerdataFactory $sellerdataFactory,
        \Cmsmart\Marketplace\Helper\Data $marketplaceHelperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $contextInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->productFactory = $productFactory;
        $this->model = $manageFactory;
        $this->sellerdataFactory = $sellerdataFactory;
        $this->_marketplaceHelperData  = $marketplaceHelperData;
        $this->storeManager = $storeManager;
        $this->context = $contextInterface;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction($collection)
    {
        $itemsDeleted = 0;
        $productDeleted = 0;
        foreach ($collection as $item) {
            $mpModel = $this->model->create()->load($item->getId());
            $mpModel->delete();
            $itemsDeleted++;

            $productModel = $this->productFactory->create()->load($item->getProductId());
            $productModel->delete();
            $this->sendMail($mpModel,$productModel);
            $productDeleted++;
        }

        if ($itemsDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 product(s) were deleted.', $itemsDeleted));
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while delete product');
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    private function sendMail($data1, $data2)
    {

        $helper = $this->_marketplaceHelperData;
        $sellerName = '';
        $sellerEmail = '';
        $sellerId = '';
        $shopName = '';
        $productName = '';
        $productSku = '';
        $productPrice = '';

        if ($data1) {
            $sellerId = $data1['seller_id'];
            $sellerData = $this->sellerdataFactory->create()->getCollection()->addFieldToFilter('seller_id', $sellerId);
            foreach ($sellerData as $item) {
                $shopName = $item->getShopTitle();
            }
        }

        if($data2) {
            $productName = $data2['name'];
            $productSku = $data2['sku'];
            $productPrice = $data2['price'];
        }

        $store = $this->storeManager->getStore(
            $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );

        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        $priceAmount = $currency->toCurrency(sprintf("%f", $productPrice));

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

        $emailTempVariables['templateSubject'] = "DemoShop";
        $emailTempVariables['message'] = __("I would like to inform you that your item $productName has been deleted.");
        $emailTempVariables['product_name'] = $productName;
        $emailTempVariables['sku'] = $productSku;
        $emailTempVariables['price'] = $priceAmount;

        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];

        $helper->sendAdminProductMail(
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