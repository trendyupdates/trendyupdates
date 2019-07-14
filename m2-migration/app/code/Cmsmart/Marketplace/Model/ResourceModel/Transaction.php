<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * cmsmart_marketplace_transaction mysql resource
 */
class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cmsmart_marketplace_transaction', 'id');
    }
}
