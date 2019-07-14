<?php

namespace Cmsmart\Marketplace\Controller\Adminhtml\Locator;

use Magento\Backend\App\Action;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Cmsmart_Marketplace::locator';

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax'))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        if ($postItems) {

            $id = 0;
            foreach ($postItems as $item) {
                $id = $item['id'];
                $status = $item['status'];
            }

            if ($id) {
                $model = $this->_objectManager->create('Cmsmart\Marketplace\Model\Location')->load($id);

                $model->setStatus($status);
                $model->save();

            }

            return $resultJson->setData([
                'messages' => [__('Locator status has been updated.')],
                'error' => false
            ]);
        }
    }
}
