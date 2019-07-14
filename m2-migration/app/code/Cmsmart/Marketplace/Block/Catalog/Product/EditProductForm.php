<?php

/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Block\Catalog\Product;
use Magento\Catalog\Model\Category;

class EditProductForm extends \Magento\Framework\View\Element\Template
{
    protected $_priceCurrency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Tax\Model\ResourceModel\TaxClass\Collection $taxClassCollection,
        \Magento\Framework\File\Size $fileConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Cmsmart\Marketplace\Helper\Data $helper,
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $attributeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollectionFactory,
		Category $category,
        \Magento\Framework\Registry $registry,

        array $data = []
    )
    {
        $this->formkey = $formKey;
        $this->_storeManager = $context->getStoreManager();
        $this->_priceCurrency = $priceCurrency;
        $this->_visibility = $visibility;
        $this->_taxClassCollection = $taxClassCollection;
        $this->_assetRepo = $context->getAssetRepository();
        $this->_fileConfig = $fileConfig;
        $this->eavConfig = $eavConfig;
        $this->_helper = $helper;
        $this->attributeFactory = $attributeFactory;
        $this->attrCollectionFactory = $attrCollectionFactory;
		$this->_category = $category;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        return $this;
    }

    public function getFormKey()
    {
        return $this->formkey->getFormKey();
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
    }

    public function getProductVisibility()
    {
        return $this->_visibility->getOptionArray();
    }

    public function getTaxClassCollection() {
        return $this->_taxClassCollection;
    }

    public function getSpaceImage()
    {
        return $this->_assetRepo->getUrl('images/spacer.gif');
    }

    public function getFileMaxSize()
    {
        return $this->_fileConfig->getMaxFileSize();
    }

    public function getHtml() {
        return $this->_escaper->escapeHtml('image');
    }

    public function getAllowedSets(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coll = $objectManager->create(\Magento\Catalog\Model\Product\AttributeSet\Options::class);

        $allowed=explode(',',$this->_helper->getAllowedAttributesetIds());

        foreach($coll->toOptionArray() as $d){
            if(in_array($d['value'], $allowed)) {
                $this->_options[] = ['label' => $d['label'], 'value' => $d['value']];
            }
        }
        return $this->_options;
    }

    public function getCategory()
    {
        return $this->_category;
    }

    public function getProductModel() {
        if ($this->_registry->registry('product_edit')) {
            return $this->_registry->registry('product_edit');
        } else {
            return null;
        }
    }

}
