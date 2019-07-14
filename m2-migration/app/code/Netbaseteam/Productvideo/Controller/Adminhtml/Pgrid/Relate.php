<?php

namespace Netbaseteam\Productvideo\Controller\Adminhtml\Pgrid;

class Relate extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer grid action
     *
     * @return void
     */
    public function execute()
    {
		$this->_view->loadLayout();
        $this->getResponse()->setBody(
              $this->_view->getLayout()->createBlock('Netbaseteam\Productvideo\Block\Adminhtml\Pgrid\Relate')->toHtml()
        );
    }
}
