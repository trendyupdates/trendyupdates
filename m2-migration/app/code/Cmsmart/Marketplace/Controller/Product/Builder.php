<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Controller\Product;

/**
 * Cmsmart Marketplace Product Builder Controller Class.
 */
class Builder
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry           $registry
     * @param \Psr\Log\LoggerInterface              $loggerInterface
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
        \Cmsmart\Marketplace\Helper\Data $helper,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        $this->_productFactory = $productFactory;
        $this->_logger = $loggerInterface;
        $this->_helper = $helper;
        $this->_registry = $registry;
    }

    /**
     * Build product based on requestData.
     *
     * @param $requestData
     *
     * @return \Magento\Catalog\Model\Product $mageProduct
     */
    public function build($requestData, $store = 0)
    {
        if (!empty($requestData['id'])) {
            $mageProductId = (int) $requestData['id'];
        } else {
            $mageProductId = '';
        }
        /** @var $mageProduct \Magento\Catalog\Model\Product */
        $mageProduct = $this->_productFactory->create();
        if (!empty($requestData['set'])) {
            $mageProduct->setAttributeSetId($requestData['set']);
        }
        if (!empty($requestData['type'])) {
            $mageProduct->setTypeId($requestData['type']);
        }
        $mageProduct->setStoreId($store);
        if ($mageProductId) {
            try {
                $isPartner = $this->_helper->getSellerId();
                $flag = false;
                if ($isPartner) {
                    $rightseller = $this->_helper->isCorrectSeller($mageProductId);
                    if ($rightseller == 1) {
                        $flag = true;
                    }
                }
                if ($flag) {
                    $mageProduct->load($mageProductId);
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        $this->_registry->register('product', $mageProduct);
        $this->_registry->register('current_product', $mageProduct);

        return $mageProduct;
    }
}
