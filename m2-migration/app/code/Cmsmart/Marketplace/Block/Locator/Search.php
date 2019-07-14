<?php

namespace Cmsmart\Marketplace\Block\Locator;

class Search extends \Cmsmart\Marketplace\Block\Locator
{
    public function getSearchAction(){
        return $this->getUrl("*/*/", ['_secure' => $this->getRequest()->isSecure()]);
    }
}