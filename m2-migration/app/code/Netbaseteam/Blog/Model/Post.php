<?php

namespace Netbaseteam\Blog\Model;

/**
 * Blog Model
 *
 * @method \Netbaseteam\Blog\Model\Resource\Page _getResource()
 * @method \Netbaseteam\Blog\Model\Resource\Page getResource()
 */
class Post extends \Magento\Framework\Model\AbstractModel
{
    
    protected function _construct()
    {
        $this->_init('Netbaseteam\Blog\Model\ResourceModel\Post');
    }


}
