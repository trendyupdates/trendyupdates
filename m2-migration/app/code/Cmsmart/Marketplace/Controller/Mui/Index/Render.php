<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Controller\Mui\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Controller\UiActionInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Webkul Marketplace UI Component data config update_url.
 * Class Marketplace Mui Render.
 */
class Render extends \Magento\Customer\Controller\AbstractAccount implements UiActionInterface
{
    /**
     * @var UiComponentFactory
     */
    protected $_factory;

    /**
     * @param Context            $context
     * @param UiComponentFactory $factory
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory
    ) {
        parent::__construct($context);
        $this->_factory = $factory;
    }

    /**
     * Excecute Action for Ui Component AJAX request.
     */
    public function execute()
    {
        $componentInterface = $this->_factory->create($this->_request->getParam('namespace'));
        $this->prepareMarketplaceUiComponent($componentInterface);
        $this->_response->appendBody((string) $componentInterface->render());
    }

    /**
     * executeAjaxRequest Action for AJAX request.
     */
    public function executeAjaxRequest()
    {
        $this->execute();
    }

    /**
     * Call marketplace ui coponent prepare method.
     *
     * @param UiComponentInterface $componentInterface
     */
    protected function prepareMarketplaceUiComponent(UiComponentInterface $componentInterface)
    {
        foreach ($componentInterface->getChildComponents() as $childComponent) {
            $this->prepareMarketplaceUiComponent($childComponent);
        }
        $componentInterface->prepare();
    }
}
