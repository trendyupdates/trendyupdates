<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Core\Block\System\Config;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\ModuleResource
     */
    private $moduleResource;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

		$html .= $this->_getInfo();

		$html .= $this->_getFooterHtml($element);

		return $html;
    }

	protected function _getInfo()
	{
		$html = '<div class="cmsmart-store-info">';
		$html .= '	<h3>Company</h3>';
		$html .= '	<p>Netbase JSC is one of top software outsourcing companies. We focus on a total e-Commerce solutions and consulting for all ecommerce business to sell products and services on the Internet.
We have served over 25.000+ customers and hundreds of different projects with wide ranges of development and customization. You can count on us 100% to take care of your business development. Our team work together to bring the best service quality to our dearest customers is our core value.</p>';
		$html .= '	<h3>About us store</h3>';
		$html .= '	<p>Cmsmart Marketplace has the best collection of quality CMS extensions and themes for most popular CMS like Joomla, Magento, Wordpress...</p>';
		$html .= '	<a href="https://cmsmart.net/" title="Cmsmart Store" target="_blank">Cmsmart Marketplace</a>';
		$html .= '	<p></p>';
		$html .= '	<h3>Follow Us</h3>';
		$html .= '	<div class="cmsmart-follow"><a href="http://netbasejsc.com/" title="NetBase JSC" target="_blank"><img src="' . $this->getViewFileUrl('Cmsmart_Core::images/netbase_logo.png') . '" alt="Netbase"/></a></li></ul></div>';
		$html .= '</div>';

		return $html;
	}
}