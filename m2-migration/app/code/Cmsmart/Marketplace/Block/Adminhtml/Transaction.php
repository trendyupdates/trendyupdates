<?php

namespace Cmsmart\Marketplace\Block\Adminhtml;

class Transaction extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
