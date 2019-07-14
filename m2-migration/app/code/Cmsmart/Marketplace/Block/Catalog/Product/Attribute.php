<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Block\Catalog\Product;

class Attribute extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $_inputTypeFactory;

    /**
     * Reward constructor.
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        array $data = []
    )
    {
        $this->_inputTypeFactory = $inputTypeFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {

        parent::_prepareLayout();
        return $this;
    }

    public function catalogInputType() {
        return $this->_inputTypeFactory->create()->toOptionArray();
    }

}
