<?php

namespace Cmsmart\Marketplace\Block\Adminhtml\Locator\Edit;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Fieldset element renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Renderer extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    
    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'Cmsmart_Marketplace::locator/add_map.phtml';

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
}
