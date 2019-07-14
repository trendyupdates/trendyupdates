<?php
namespace Cmsmart\Marketplace\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Cmsmart_Marketplace::marketplace_seller';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
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
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cmsmart_Marketplace::seller')
            ->addBreadcrumb(__('Marketplace'), __('Marketplace'))
            ->addBreadcrumb(__('Edit Commission'), __('Edit Commission'));
        return $resultPage;
    }

    /**
     * Edit Commission
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $id = $data['id'];
        $model = $this->_objectManager->create('Cmsmart\Marketplace\Model\Seller');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This seller no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }


        if (!empty($data)) {
            $model->setData($data);
            $model->save();

            $this->messageManager->addSuccessMessage(__('This seller commission has updated successfully!.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        }

        $this->_coreRegistry->register('current_seller', $model);
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/index');
    }
}
