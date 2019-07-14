<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */
namespace Cmsmart\Marketplace\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MassDelete extends \Magento\Framework\App\Action\Action
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Action\Context $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        CollectionFactory $collectionFactory,
        \Cmsmart\Marketplace\Model\ResourceModel\Product\CollectionFactory $mkProductCollection,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->mkProductCollection = $mkProductCollection;
        $this->productRepository = $productRepository;
        $this->formkey = $formKey;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Cmsmart\Marketplace\Helper\Data'
        );
        $isPartner = $helper->getSellerId();
        if ($isPartner) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            /** @var \Magento\Framework\ObjectManagerInterface $om */
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $om->get('Magento\Framework\Event\ManagerInterface');

            try {
                $unauth_ids = array();

                if ($this->getRequest()->isPost()) {
                    if (!$this->formkey->getFormKey()) {
                        $this->_redirect('marketplace/product/');
                    }
                    if ($this->getRequest()->getParam('p')) {
                        $numPage = $this->getRequest()->getParam('p');
                    } else {
                        $numPage = 1;
                    }

                    $customerId = $this->customerSession->getCustomerId();
                    $storeId = $this->_storeManager->getStore()->getId();
                    if ($this->getRequest()->getParam('mpselecctall')) {
                        $mkData = $this->mkProductCollection->create()
                            ->addFieldToFilter('seller_id', array('eq' => $customerId))
                            ->setPageSize($numPage * 9)
                            ->getData();
                        if ($mkData) {
                            foreach ($mkData as $item) {
                                $ids[] = $item['product_id'];
                            }
                        }
                    } else {
                        $ids = $this->getRequest()->getParam('product_mass_delete');
                    }

                    $this->_registry->register("isSecureArea", 1);
                    $mkproductDeleted = 0;
                    foreach ($ids as $id) {
                        $data['id'] = $id;

                        $manager->dispatch('mp_delete_product', $data);

                        $collection_product = $this->mkProductCollection->create()
                            ->addFieldToFilter('product_id', array('eq' => $id))
                            ->addFieldToFilter('seller_id', array('eq' => $customerId));
                        if (count($collection_product)) {
                            $product = $this->productRepository->getById($id);
                            $this->productRepository->delete($product);
                            $collection = $this->mkProductCollection->create()
                                ->addFieldToFilter('product_id', array('eq' => $id));

                            foreach ($collection as $row) {
                                $row->delete();
                                $mkproductDeleted++;
                            }
                        } else {
                            array_push($unauth_ids, $id);
                        }
                    }
                    $this->messageManager->addSuccess(
                        __('A total of %1 product(s) have been deleted.', $mkproductDeleted)
                    );

                    $this->_registry->unregister('isSecureArea');
                    $this->_storeManager->setCurrentStore($storeId);
                }
                if (count($unauth_ids)) {
                    $this->messageManager->addError(__('You are not authorized to delete products with id %s', implode(",", $unauth_ids)));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }


            $request = $this->getRequest();
            $refererUrl = (string)$request->getServer('HTTP_REFERER');
            $url = (string)$request->getParam(\Magento\Framework\App\Response\RedirectInterface::PARAM_NAME_REFERER_URL);
            if ($url) {
                $refererUrl = $url;
            }
            if ($request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED) && $refererUrl) {
                $redirectUrl = $refererUrl;
            } else {
                $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
            }
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/registry',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
