<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cmsmart\Marketplace\Block\Catalog\Product\Edit\Tab;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Adminhtml catalog product downloadable items tab and form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Downloadable extends \Magento\Framework\View\Element\Template
{
    /**
     * Reference to product objects that is being edited
     *
     * @var \Cmsmart\Marketplace\Model\Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\DataObject|null
     */
    protected $_config = null;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/downloadable.phtml';

    /**
     * Accordion block id
     *
     * @var string
     */
    protected $blockId = 'downloadableInfo';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Check is readonly block
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * Get downloadable tab content id
     *
     * @return string
     */
    public function getContentTabId()
    {
        return 'tab_content_' . $this->blockId;
    }

    public function getCurrentProductType() {
        return $this->_coreRegistry->registry('product_type');
    }

    /**
     * @return bool
     */
    public function isDownloadable()
    {
        if ($this->_coreRegistry->registry('product_edit')) {
           return  $this->_coreRegistry->registry('product_edit')->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
        }
        return $this->getCurrentProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', $this->isDownloadable());
        return parent::_prepareLayout();
    }
}
