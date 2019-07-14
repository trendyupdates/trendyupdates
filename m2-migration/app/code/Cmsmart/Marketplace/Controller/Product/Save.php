<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */
namespace Cmsmart\Marketplace\Controller\Product;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Cmsmart\Marketplace\Helper\Data as MarketplaceHelperData;
use Cmsmart\Marketplace\Model\Product as SellerProduct;
use Magento\Framework\App\ObjectManager;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory as SampleFactory;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as LinkFactory;

/**
 * Marketplace Product Save Controller.
 */
class Save extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SaveProduct
     */
    protected $_saveProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResourceModel;


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var Builder
     */
    protected $_marketplaceProductBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $_catalogProductTypeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationHandler
     */
    protected $_variationHandler;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $_productRepositoryInterface;

    /** @var \Magento\Catalog\Api\ProductLinkInterfaceFactory */
    protected $_productLinkFactory;

    /**
     * @var eventManager
     */
    protected $_eventManager;


    /**
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
     */
    protected $_productLinks;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var MarketplaceHelperData
     */
    protected $_marketplaceHelperData;

    /**
     * @var SampleFactory
     */
    private $sampleFactory;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var \Magento\Downloadable\Model\Sample\Builder
     */
    private $sampleBuilder;

    /**
     * @var \Magento\Downloadable\Model\Link\Builder
     */
    private $linkBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\Link\Resolver
     */
    private $linkResolver;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager,
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeConfigurable,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Model\Product\Link\Resolver $linkResolver,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Filesystem $filesystem,
        Builder $marketplaceProductBuilder,
        MarketplaceHelperData $marketplaceHelperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $contextInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    )
    {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_productResourceModel = $productResourceModel;
        $this->_product = $product;
        $this->_eventManager = $eventManager;
        $this->_date = $date;
        $this->_dateFilter = $dateFilter;
        $this->_catalogProductTypeManager = $catalogProductTypeManager;
        $this->_variationHandler = $variationHandler;
        $this->_catalogProductTypeConfigurable = $productTypeConfigurable;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_productLinkFactory = $productLinkFactory;
        $this->_linkResolver = $linkResolver;
        $this->_productLinks = $productLinks;
        $this->_jsHelper = $jsHelper;
        $this->_marketplaceProductBuilder = $marketplaceProductBuilder;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_marketplaceHelperData = $marketplaceHelperData;
        $this->storeManager = $storeManager;
        $this->context = $contextInterface;
        $this->localeCurrency = $localeCurrency;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Cmsmart\Marketplace\Helper\Data'
        );
        $isPartner = $helper->getSellerId();
        if ($isPartner) {
            try {
                $returnArr = [];
                $productId = $this->getRequest()->getParam('id');
                if ($this->getRequest()->isPost()) {
                    $wholedata = $this->getRequest()->getPostValue();
                    if ($productId) {
                        $wholedata['id'] = $productId;
                        $wholedata['product_id'] = $productId;
                        $wholedata['status'] = $this->_product->load($productId)->getStatus();
                    }
                    $skuType = $this->_marketplaceHelperData->getAllowedSkuTypes();
                    $skuPrefix = $this->_marketplaceHelperData->getSkuPrefix();
                    if ($skuType == 'dynamic') {
                        $sku = $skuPrefix.'-' . $wholedata['product']['name'];
                        $wholedata['product']['sku'] = $this->checkSkuExist($sku);
                    }

                    list($datacol, $errors) = $this->validatePost();

                    if (empty($errors)) {
                        $returnArr = $this->saveProductData(
                            $this->_getSession()->getCustomerId(),
                            $wholedata
                        );

                        $this->messageManager->addSuccess(__('Your product has been successfully saved.'));

                    } else {
                        foreach ($errors as $message) {
                            $this->messageManager->addError($message);
                        }
                    }
                }

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/registry',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    private function checkSkuExist($sku)
    {
        try {
            $id = $this->_productResourceModel->getIdBySku($sku);
            if ($id) {
                $avialability = 0;
            } else {
                $avialability = 1;
            }
        } catch (\Exception $e) {
            $avialability = 0;
        }
        if ($avialability == 0) {
            $sku = $sku . rand();
            $sku = $this->checkSkuExist($sku);
        }
        return $sku;
    }

    private function validatePost()
    {
        $errors = [];
        $data = [];
        foreach ($this->getRequest()->getParam('product') as $code => $value) {
            switch ($code) :
                case 'price':
                    $result = $this->priceValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'weight':
                    $result = $this->weightValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'stock':
                    $result = $this->stockValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                    break;
                case 'bundle_options':
                    $result = $this->bundleOptionValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
            endswitch;
        }

        return [$data, $errors];
    }

    private function priceValidateFunction($value, $code, $errors, $data)
    {
        if (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __(
                'Price should contain only decimal numbers'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    private function weightValidateFunction($value, $code, $errors, $data)
    {
        if ($value != '' && !preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __(
                'Weight should contain only decimal numbers'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    private function stockValidateFunction($value, $code, $errors, $data)
    {
        if (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __(
                'Product stock should contain only integers'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    private function bundleOptionValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __('Default Title has to be completed');
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * saveProductData method for seller's product save action.
     *
     * @param $sellerId
     * @param $wholdata
     *
     * @return array
     */
    public function saveProductData($sellerId, $wholedata)
    {
        $returnArr = [];
        $returnArr['error'] = 0;
        $returnArr['product_id'] = '';
        $returnArr['message'] = '';
        $wholedata['new-variations-attribute-set-id'] = $wholedata['set'];
        $wholedata['product']['attribute_set_id'] = $wholedata['set'];

        $helper = $this->_marketplaceHelperData;

        $sellerId = $sellerId;
		
        if (!empty($wholedata['id'])) {
            $mageProductId = $wholedata['id'];
            $editFlag = 1;
            $storeId = $helper->getCurrentStoreId();
            $this->_eventManager->dispatch(
                'mp_customattribute_deletetierpricedata',
                [$wholedata]
            );
			$wholedata['product']['website_ids'][] = $helper->getWebsiteId() ? $helper->getWebsiteId() : 1;
            $wholedata = $this->adminStoreMediaImages($mageProductId, $wholedata);
        } else {
            $mageProductId = '';
            $editFlag = 0;
            $storeId = 0;
            $wholedata['product']['website_ids'][] = $helper->getWebsiteId() ? $helper->getWebsiteId() : 1;
            
            $checkPro = $this->_product->getCollection()->addFieldToFilter('name', $wholedata['product']['name'])->getData();
            $wholedata['product']['url_key'] = '';

            if (count($checkPro)) {
                $wholedata['product']['url_key'] = preg_match('/(.*)-(\d+)$/', $wholedata['product']['name'], $matches)
                ? $matches[1] . '-' . ($matches[2] + 1)
                : $wholedata['product']['name'] . '-'.count($checkPro);
            }
        }
        if (isset($wholedata['status']) && $wholedata['status'] && $mageProductId) {
            $status = $wholedata['status'];
            if ($helper->getIsProductEditApproval()) {
//                $status = $helper->getIsProductEditApproval() ?
//                    SellerProduct::STATUS_DISAPPROVE : SellerProduct::STATUS_APPROVE;

                $status = $helper->getIsProductEditApproval() ?
                    SellerProduct::STATUS_PENDING : SellerProduct::STATUS_APPROVE;
            }
        } else {
//            $status = $helper->getIsProductApproval() ?
//                SellerProduct::STATUS_DISAPPROVE : SellerProduct::STATUS_APPROVE;

            $status = $helper->getIsProductApproval() ?
                SellerProduct::STATUS_PENDING : SellerProduct::STATUS_APPROVE;
        }
        $wholedata['status'] = $status;

        $wholedata['store'] = $storeId;
        /*
        * Marketplace Product save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_save_before',
            [$wholedata]
        );

        $catalogProductTypeId = $wholedata['type'];
        /*
        * Product Initialize method to set product data
        */

        $catalogProduct = $this->productInitialize(
            $this->_marketplaceProductBuilder->build($wholedata, $storeId),
            $wholedata
        );


        /*for downloadable products start*/

        $catalogProduct = $this->buildDownloadableProduct($catalogProduct, $wholedata);

        /*for downloadable products end*/

        /*for configurable products start*/

        $associatedProductIds = [];

        $resultData = $this->buildConfigurableProduct($catalogProduct, $wholedata);

        $catalogProduct = $resultData['catalogProduct'];

        $associatedProductIds = $resultData['associatedProductIds'];

        /*for configurable products end*/

        //for check type
        $this->_catalogProductTypeManager->processProduct($catalogProduct);

        $set = $catalogProduct->getAttributeSetId();

        $type = $catalogProduct->getTypeId();

        if (isset($set) && isset($type)) {
            $allowedsets = explode(',', $helper->getAllowedAttributesetIds());
            $allowedtypes = explode(',', $helper->getAllowedProductTypes());
            if (!in_array($type, $allowedtypes) || !in_array($set, $allowedsets)) {
                $returnArr['error'] = 1;
                $returnArr['message'] = __('Product Type Invalid Or Not Allowed');
                return $returnArr;
            }
        } else {
            $returnArr['error'] = 1;
            $returnArr['message'] = __('Product Type Invalid Or Not Allowed');

            return $returnArr;
        }

        $originalSku = $catalogProduct->getSku();
        $catalogProduct->setStatus($status);
        $catalogProduct->save();
        $mageProductId = $catalogProduct->getId();

        /*for configurable associated products save start*/

        $this->saveConfigurableAssociatedProducts($wholedata, $storeId);

        /*for configurable associated products save end*/

        $wholedata['id'] = $mageProductId;
        $this->_eventManager->dispatch(
            'mp_customoption_setdata',
            [$wholedata]
        );

        /* Update marketplace product*/
        $this->saveMaketplaceProductTable(
            $mageProductId,
            $sellerId,
            $status,
            $editFlag,
            $associatedProductIds
        );

        /*
        * Marketplace Custom Attribute Set Tier Price Observer
        */
        $this->_eventManager->dispatch(
            'mp_customattribute_settierpricedata',
            [$wholedata]
        );

        /*
        * Marketplace Product Save After Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_save_after',
            [$wholedata]
        );

        /*
        * Marketplace Product Send Mail Function
        */
        $this->sendProductMail($wholedata, $sellerId, $editFlag);

        if ($editFlag == null) {
            $this->sendConfirmMail($wholedata, $sellerId);
        }

        return $returnArr;
    }

    protected function adminStoreMediaImages($productId, $wholedata, $storeId = 0)
    {
        if (!empty($wholedata['product']['media_gallery'])) {
            $flag = 0;
            foreach ($wholedata['product']['media_gallery']['images'] as $key => $value) {
                if ($value['media_type'] == 'external-video') {
                    $flag = 1;
                }
            }
            if ($flag == 1) {
                $catalogProduct = $this->_productRepositoryInterface
                    ->getById(
                        $productId,
                        false,
                        $storeId
                    );
                /*for downloadable products start*/

                $catalogProduct = $this->buildDownloadableProduct($catalogProduct, $wholedata);

                /*for downloadable products end*/
                $catalogProduct->setMediaGallery($wholedata['product']['media_gallery'])->save();
                foreach ($wholedata['product']['media_gallery']['images'] as $key => $value) {
                    if ($value['value_id'] == '') {
                        unset($wholedata['product']['media_gallery']['images'][$key]);
                    }
                }
            }
        }
        return $wholedata;
    }

    /**
     * Get LinkFactory instance.
     *
     * @deprecated
     *
     * @return LinkFactory
     */
    private function getLinkFactory()
    {
        if (!$this->linkFactory) {
            $this->linkFactory = ObjectManager::getInstance()->get(LinkFactory::class);
        }

        return $this->linkFactory;
    }

    /**
     * Get Sample Factory.
     *
     * @deprecated
     *
     * @return SampleFactory
     */
    private function getSampleFactory()
    {
        if (!$this->sampleFactory) {
            $this->sampleFactory = ObjectManager::getInstance()->get(SampleFactory::class);
        }

        return $this->sampleFactory;
    }

    /**
     * Product initialize function before saving.
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param $requestData
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function productInitialize(\Magento\Catalog\Model\Product $catalogProduct, $requestData)
    {
        $helper = $this->_marketplaceHelperData;
        $requestProductData = $requestData['product'];

        unset($requestProductData['custom_attributes']);
        unset($requestProductData['extension_attributes']);

        /*
        * Manage seller product Stock data
        */
        $requestProductData = $this->manageSellerProductStock($requestProductData);

        $requestProductData = $this->normalizeProductData($requestProductData);

        if (!empty($requestProductData['is_downloadable'])) {
            $requestProductData['product_has_weight'] = 0;
        }

        $requestProductData = $this->manageProductCategoryWebsiteData($requestProductData);

        $wasLockedMedia = false;
        if ($catalogProduct->isLockedAttribute('media')) {
            $catalogProduct->unlockAttribute('media');
            $wasLockedMedia = true;
        }

        $requestProductData = $this->manageProductDateTimeFilter($catalogProduct, $requestProductData);

        if (isset($requestProductData['options'])) {
            $productOptions = $requestProductData['options'];
            unset($requestProductData['options']);
        } else {
            $productOptions = [];
        }

        $catalogProduct->addData($requestProductData);

        if ($wasLockedMedia) {
            $catalogProduct->lockAttribute('media');
        }

        if ($helper->getSingleStoreStatus()) {
            $catalogProduct->setWebsiteIds([$helper->getWebsiteId()]);
        }

        /*
         * Check for "Use Default Value" field value
         */
        $catalogProduct = $this->manageProductForDefaultAttribute($catalogProduct, $requestData);

        /*
         * Set Downloadable links if available
         */
        $catalogProduct = $this->manageProductDownloadableData($catalogProduct, $requestData);

        /*
         * Set Product options to product if exist
         */
        $catalogProduct = $this->manageProductOptionData($catalogProduct, $productOptions);

        /*
         * Set Product Custom options status to product
         */
        if (empty($requestData['affect_product_custom_options'])) {
            $requestData['affect_product_custom_options'] = '';
        }

        $catalogProduct->setCanSaveCustomOptions(
            (bool)$requestData['affect_product_custom_options']
            && !$catalogProduct->getOptionsReadonly()
        );
        return $catalogProduct;
    }

    /**
     * Set Downloadable Data in Product Model.
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param array $wholedata
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function buildDownloadableProduct($catalogProduct, $wholedata)
    {
        if (!empty($wholedata['downloadable']) && $downloadableParamData = $wholedata['downloadable']) {
            $downloadableParamData = $links = $this->getDownloadableParamData($downloadableParamData);

            $catalogProduct->setDownloadableData($downloadableParamData);

            $extension = $catalogProduct->getExtensionAttributes();

            if (isset($downloadableParamData['link']) && is_array($downloadableParamData['link'])) {
                $links = [];
                $links = $this->getDownloabaleLinkData($downloadableParamData, $links);
                $extension->setDownloadableProductLinks($links);
            }
            if (isset($downloadableParamData['sample']) && is_array($downloadableParamData['sample'])) {
                $samples = [];
                $samples = $this->getDownloabaleSampleData($downloadableParamData, $samples);
                $extension->setDownloadableProductSamples($samples);
            }
            $catalogProduct->setExtensionAttributes($extension);
            if ($catalogProduct->getLinksPurchasedSeparately()) {
                $catalogProduct->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
            } else {
                $catalogProduct->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
            }
        }
        return $catalogProduct;
    }

    /**
     * Set Downloadable Data in Product Model.
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param array $wholedata
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function buildConfigurableProduct($catalogProduct, $wholedata)
    {
        $associatedProductIds = [];
        if (!empty($wholedata['attributes'])) {
            $requestProductData = $wholedata['product'];
            $attributes = $wholedata['attributes'];
            $setId = $wholedata['set'];
            $catalogProduct->setAttributeSetId($setId);
            $this->_catalogProductTypeConfigurable->setUsedProductAttributeIds(
                $attributes,
                $catalogProduct
            );

            $extensionAttributes = $catalogProduct->getExtensionAttributes();

            $catalogProduct->setNewVariationsAttributeSetId($setId);
            $configurableOptions = [];

            $extensionAttributes->setConfigurableProductOptions($configurableOptions);

            if (!empty($wholedata['associated_product_ids'])) {
                $associatedProductIds = $wholedata['associated_product_ids'];
            }
            // Get variationsMatrix
            $variationsMatrix = [];
            if (!empty($wholedata['variations-matrix'])) {
                $variationsMatrix = $wholedata['variations-matrix'];
            }

            if ($associatedProductIds || $variationsMatrix) {
                $this->_variationHandler->prepareAttributeSet($catalogProduct);
            }

            if (!empty($variationsMatrix)) {
                $generatedProductIds = $this->_variationHandler->generateSimpleProducts(
                    $catalogProduct,
                    $variationsMatrix
                );
                $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
            }
            $extensionAttributes->setConfigurableProductLinks(array_filter($associatedProductIds));

            $catalogProduct->setCanSaveConfigurableAttributes(
                (bool)$wholedata['affect_configurable_product_attributes']
            );

            $catalogProduct->setExtensionAttributes($extensionAttributes);
        }
        return ["catalogProduct" => $catalogProduct, "associatedProductIds" => $associatedProductIds];
    }

    private function saveConfigurableAssociatedProducts($wholedata, $storeId)
    {
        $configurations = [];
        if (!empty($wholedata['configurations'])) {
            $configurations = $wholedata['configurations'];
        }

        if (!empty($configurations)) {
            $configurations = $this->_variationHandler
                ->duplicateImagesForVariations($configurations);
            foreach ($configurations as $associtedProductId => $associtedProductData) {
                $associtedProduct = $this->_productRepositoryInterface
                    ->getById(
                        $associtedProductId,
                        false,
                        $storeId
                    );
                $associtedProductData = $this->_variationHandler
                    ->processMediaGallery(
                        $associtedProduct,
                        $associtedProductData
                    );
                $associtedProduct->addData($associtedProductData);
                if ($associtedProduct->hasDataChanges()) {
                    $associtedProduct->save();
                }
            }
        }
    }

    /**
     * Set Product Records in marketplace_product table.
     *
     * @param int $mageProductId
     * @param int $sellerId
     * @param int $status
     * @param int $editFlag
     * @param array $associatedProductIds
     */
    private function saveMaketplaceProductTable(
        $mageProductId,
        $sellerId,
        $status,
        $editFlag,
        $associatedProductIds
    )
    {
        $sellerProductId = 0;
        $shopTitle = '';

        $sellerDataColls = $this->_objectManager->create(
            'Cmsmart\Marketplace\Model\Sellerdata'
        )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
        foreach ($sellerDataColls as $sellerData) {
            $shopTitle = $sellerData->getShopTitle();
        }

        if ($mageProductId) {
            $sellerProductColls = $this->_objectManager->create(
                'Cmsmart\Marketplace\Model\Product'
            )
                ->getCollection()
                ->addFieldToFilter(
                    'product_id',
                    $mageProductId
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            foreach ($sellerProductColls as $sellerProductColl) {
                $sellerProductId = $sellerProductColl->getId();
            }
            $collection1 = $this->_objectManager->create(
                'Cmsmart\Marketplace\Model\Product'
            )->load($sellerProductId);
            $collection1->setProductId($mageProductId);
            $collection1->setSellerId($sellerId);
            $collection1->setShopTitle($shopTitle);
            $collection1->setStatus($status);
            if (!$editFlag) {
                $collection1->setCreatedAt($this->_date->gmtDate());
            }
            $collection1->setUpdatedAt($this->_date->gmtDate());
            $collection1->save();
        }


        foreach ($associatedProductIds as $associatedProductId) {
            if ($associatedProductId) {
                $sellerAssociatedProductId = 0;
                $sellerProductColls = $this->_objectManager->create(
                    'Cmsmart\Marketplace\Model\Product'
                )
                    ->getCollection()
                    ->addFieldToFilter(
                        'product_id',
                        $associatedProductId
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                foreach ($sellerProductColls as $sellerProductColl) {
                    $sellerAssociatedProductId = $sellerProductColl->getId();
                }
                $collection1 = $this->_objectManager->create(
                    'Cmsmart\Marketplace\Model\Product'
                )
                    ->load($sellerAssociatedProductId);
                $collection1->setProductId($associatedProductId);
                if (!$editFlag) {
                    /* If new product is added*/
                    $collection1->setStatus(SellerProduct::STATUS_APPROVE);
                    $collection1->setCreatedAt($this->_date->gmtDate());
                }
                $collection1->setUpdatedAt($this->_date->gmtDate());
                $collection1->setSellerId($sellerId);
                $collection1->setShopTitle($shopTitle);
                $collection1->save();
            }
        }
    }

    /**
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageSellerProductStock($requestProductData)
    {
        if ($requestProductData) {
            $stockData = isset($requestProductData['stock_data']) ?
                $requestProductData['stock_data'] : [];
            if (isset($stockData['qty']) && (double)$stockData['qty'] > 99999999.9999) {
                $stockData['qty'] = 99999999.9999;
            }
            if (isset($stockData['min_qty']) && (int)$stockData['min_qty'] < 0) {
                $stockData['min_qty'] = 0;
            }
            if (!isset($stockData['use_config_manage_stock'])) {
                $stockData['use_config_manage_stock'] = 0;
            }
            if ($stockData['use_config_manage_stock'] == 1 && !isset($stockData['manage_stock'])) {
                $stockData['manage_stock'] = $this->stockConfiguration
                    ->getManageStock();
            }
            if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
                $stockData['is_decimal_divided'] = 0;
            }
            $requestProductData['stock_data'] = $stockData;
        }
        return $requestProductData;
    }

    /**
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageProductCategoryWebsiteData($requestProductData)
    {
        foreach (['category_ids', 'website_ids'] as $field) {
            if (!isset($requestProductData[$field])) {
                $requestProductData[$field] = [];
            }
        }
        foreach ($requestProductData['website_ids'] as $websiteId => $checkboxValue) {
            if (!$checkboxValue) {
                unset($requestProductData['website_ids'][$websiteId]);
            }
        }
        return $requestProductData;
    }

    /**
     * @param Magento /Catalog/Model/Product $catalogProduct
     * @param array $requestProductData
     *
     * @return array
     */
    private function manageProductDateTimeFilter($catalogProduct, $requestProductData)
    {
        $dateFieldFilters = [];
        $attributes = $catalogProduct->getAttributes();
        foreach ($attributes as $attrKey => $attribute) {
            if ($attribute->getBackend()->getType() == 'datetime') {
                if (array_key_exists($attrKey, $requestProductData) && $requestProductData[$attrKey] != '') {
                    $dateFieldFilters[$attrKey] = $this->_dateFilter;
                }

            }
        }
        $inputFilter = new \Zend_Filter_Input(
            $dateFieldFilters,
            [],
            $requestProductData
        );
        $requestProductData = $inputFilter->getUnescaped();
        return $requestProductData;
    }

    /**
     * @param Magento /Catalog/Model/Product $catalogProduct
     * @param array $requestData
     *
     * @return Magento/Catalog/Model/Product
     */
    private function manageProductForDefaultAttribute($catalogProduct, $requestData)
    {
        if (!empty($requestData['use_default'])) {
            foreach ($requestData['use_default'] as $attributeCode => $useDefaultState) {
                if ($useDefaultState) {
                    $catalogProduct->setData($attributeCode, null);
                    if ($catalogProduct->hasData('use_config_' . $attributeCode)) {
                        $catalogProduct->setData('use_config_' . $attributeCode, false);
                    }
                }
            }
        }
        return $catalogProduct;
    }

    /**
     * @param Magento /Catalog/Model/Product $catalogProduct
     * @param array $requestData
     *
     * @return Magento/Catalog/Model/Product
     */
    private function manageProductDownloadableData($catalogProduct, $requestData)
    {
        if (!empty($requestData['links'])) {
            $links = $this->_linkResolver->getLinks();

            $catalogProduct->setProductLinks([]);

            $catalogProduct = $this->_productLinks->initializeLinks($catalogProduct, $links);
            $productLinks = $catalogProduct->getProductLinks();

            $linkTypes = [
                'related' => $catalogProduct->getRelatedReadonly(),
                'upsell' => $catalogProduct->getUpsellReadonly(),
                'crosssell' => $catalogProduct->getCrosssellReadonly()
            ];
            foreach ($linkTypes as $linkType => $readonly) {
                if (isset($links[$linkType]) && !$readonly) {
                    foreach ((array)$links[$linkType] as $linkData) {
                        if (empty($linkData['id'])) {
                            continue;
                        }

                        $linkProduct = $this->_productRepositoryInterface->getById($linkData['id']);
                        $link = $this->_productLinkFactory->create();
                        $link->setSku($catalogProduct->getSku())
                            ->setLinkedProductSku($linkProduct->getSku())
                            ->setLinkType($linkType)
                            ->setPosition(isset($linkData['position']) ? (int)$linkData['position'] : 0);
                        $productLinks[] = $link;
                    }
                }
            }
            $catalogProduct->setProductLinks($productLinks);
        }
        return $catalogProduct;
    }

    /**
     * @param Magento /Catalog/Model/Product $catalogProduct
     * @param $productOptions
     *
     * @return Magento/Catalog/Model/Product
     */
    private function manageProductOptionData($catalogProduct, $productOptions)
    {
        if ($productOptions && !$catalogProduct->getOptionsReadonly()) {
            // mark custom options that should to fall back to default value
            $options = $this->mergeProductOptions(
                $productOptions,
                []
            );
            $customOptions = [];
            foreach ($options as $customOptionData) {
                if (empty($customOptionData['is_delete'])) {
                    if (isset($customOptionData['values'])) {
                        $customOptionData['values'] = array_filter(
                            $customOptionData['values'],
                            function ($valueData) {
                                return empty($valueData['is_delete']);
                            }
                        );
                    }
                    $customOption = $this->_objectManager->get(
                        'Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory'
                    )->create(['data' => $customOptionData]);
                    $customOption->setProductSku($catalogProduct->getSku());
                    $customOption->setOptionId(null);
                    $customOptions[] = $customOption;
                }
            }
            $catalogProduct->setOptions($customOptions);
        }
        return $catalogProduct;
    }

    /**
     * Internal normalization
     *
     * @param array $requestProductData
     *
     * @return array
     */
    private function normalizeProductData(array $requestProductData)
    {
        foreach ($requestProductData as $key => $value) {
            if (is_scalar($value)) {
                if ($value === 'true') {
                    $requestProductData[$key] = '1';
                } elseif ($value === 'false') {
                    $requestProductData[$key] = '0';
                }
            } elseif (is_array($value)) {
                $requestProductData[$key] = $this->normalizeProductData($value);
            }
        }

        return $requestProductData;
    }

    /**
     * Merge product and default options for product.
     *
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     *
     * @return array
     */
    public function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            return [];
        }

        if (!is_array($overwriteOptions)) {
            return $productOptions;
        }

        foreach ($productOptions as $index => $option) {
            $optionId = $option['option_id'];

            if (!isset($overwriteOptions[$optionId])) {
                continue;
            }

            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_' . $fieldName])) {
                    $productOptions[$index][$fieldName] = $option['default_' . $fieldName];
                }
            }
        }

        return $productOptions;
    }

    private function getDownloadableParamData($downloadableParamData)
    {
        if (isset($downloadableParamData['link']) && is_array($downloadableParamData['link'])) {
            foreach ($downloadableParamData['link'] as $key => $linkData) {
                if ($linkData['link_id'] == 0) {
                    $linkData['link_id'] = null;
                }
                $linkData['file'] = $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonDecode($linkData['file']);
                $linkData['sample']['file'] = $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonDecode($linkData['sample']['file']);
                $downloadableParamData['link'][$key]['link_id'] = $linkData['link_id'];
                $downloadableParamData['link'][$key]['file'] = $linkData['file'];
                $downloadableParamData['link'][$key]['sample']['file'] = $linkData['sample']['file'];
            }
        }
        if (isset($downloadableParamData['sample']) && is_array($downloadableParamData['sample'])) {
            foreach ($downloadableParamData['sample'] as $key => $sampleData) {
                if ($sampleData['sample_id'] == 0) {
                    $sampleData['sample_id'] = null;
                }
                $sampleData['file'] = $this->_objectManager->get(
                    'Magento\Framework\Json\Helper\Data'
                )->jsonDecode($sampleData['file']);
                $downloadableParamData['sample'][$key]['sample_id'] = $sampleData['sample_id'];
                $downloadableParamData['sample'][$key]['file'] = $sampleData['file'];
            }
        }
        return $downloadableParamData;
    }

    /**
     * Get Product Link Data from Post Downloadable Data.
     *
     * @param array $downloadableParamData
     * @param array $links
     *
     * @return array
     */
    private function getDownloabaleLinkData($downloadableParamData, $links)
    {
        foreach ($downloadableParamData['link'] as $linkData) {
            if (!$linkData || (isset($linkData['is_delete']) && $linkData['is_delete'])) {
                continue;
            } else {
                if (!$this->linkBuilder) {
                    $this->linkBuilder = ObjectManager::getInstance()->get(
                        \Magento\Downloadable\Model\Link\Builder::class
                    );
                }
                $links[] = $this->linkBuilder->setData(
                    $linkData
                )->build(
                    $this->getLinkFactory()->create()
                );
            }
        }
        return $links;
    }

    /**
     * Get Product Sample Data from Post Downloadable Data.
     *
     * @param array $downloadableParamData
     * @param array $sampleData
     *
     * @return array
     */
    private function getDownloabaleSampleData($downloadableParamData, $samples)
    {
        foreach ($downloadableParamData['sample'] as $sampleData) {
            if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
                continue;
            } else {
                if (!$this->sampleBuilder) {
                    $this->sampleBuilder = ObjectManager::getInstance()->get(
                        \Magento\Downloadable\Model\Sample\Builder::class
                    );
                }
                $samples[] = $this->sampleBuilder->setData(
                    $sampleData
                )->build(
                    $this->getSampleFactory()->create()
                );
            }
        }
        return $samples;
    }

    /**
     * @param array $data
     * @param string $sellerId
     * @param bool $editFlag
     */
    private function sendProductMail($data, $sellerId, $editFlag = null)
    {
        $helper = $this->_marketplaceHelperData;

        $customer = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        )->load($sellerId);

        $sellerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        $sellerEmail = $customer->getEmail();

        $emailTempVariables = [];
        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail = $adminStoremail ?
            $adminStoremail : $helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $emailTempVariables['admin'] = $adminUsername;
        if ($editFlag == null) {
            $emailTempVariables['header'] = __(
                'I would like to inform you that recently I have added a new product in the store.'
            );

        } else {
            $emailTempVariables['header'] = __(
                'I would like to inform you that recently I have updated a  product in the store.'
            );
        }

        $store = $this->storeManager->getStore(
            $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );

        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        $priceAmount = $currency->toCurrency(sprintf("%f", $data['product']['price']));


        $emailTempVariables['product_name'] = $data['product']['name'];
        $emailTempVariables['sku'] = $data['product']['sku'];
        $emailTempVariables['price'] = $priceAmount;

        $senderInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];
        $receiverInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        if ($helper->getIsProductApproval() == 1) {
            $helper->sendProductMail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
        }
    }

    private function sendConfirmMail($data, $sellerId)
    {
        $helper = $this->_marketplaceHelperData;

        $customer = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        )->load($sellerId);

        $sellerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        $sellerEmail = $customer->getEmail();

        $emailTempVariables = [];
        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail = $adminStoremail ?
            $adminStoremail : $helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $emailTempVariables['admin'] = $sellerName;
        $emailTempVariables['templateSubject'] = "DemoShop";
        $emailTempVariables['header'] = __(
            'Your item has been successfully added on DemoShop website. Please wait for the approval from our admin for your item being officially published.'
        );


        $store = $this->storeManager->getStore(
            $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );

        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        $priceAmount = $currency->toCurrency(sprintf("%f", $data['product']['price']));


        $emailTempVariables['product_name'] = $data['product']['name'];
        $emailTempVariables['sku'] = $data['product']['sku'];
        $emailTempVariables['price'] = $priceAmount;

        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];
        if ($helper->getIsProductApproval() == 1) {
            $helper->sendProductMail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
        }
    }
}
