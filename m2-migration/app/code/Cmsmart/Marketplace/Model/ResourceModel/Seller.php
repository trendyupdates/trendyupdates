<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * cmsmart_marketplace_seller mysql resource
 */
class Seller extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cmsmart_marketplace_seller', 'id');
    }
}
