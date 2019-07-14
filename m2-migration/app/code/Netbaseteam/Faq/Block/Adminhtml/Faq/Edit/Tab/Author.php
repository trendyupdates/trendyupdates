<?php
namespace Netbaseteam\Faq\Block\Adminhtml\Faq\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Author extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;


    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('faq');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Netbaseteam_Faq::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('faq_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Author Information')]);

        if ($model->getId()) {
            $fieldset->addField('faq_id', 'hidden', ['name' => 'faq_id']);
        }

        $fieldset->addField(
            'author_name',
            'text',
            [
                'name' => 'author_name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'author_email',
            'text',
            [
                'name' => 'author_email',
                'label' => __('Email'),
                'title' => __('Email'),
                'class' => 'validate-email',
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        

       
        
        $this->_eventManager->dispatch('adminhtml_faq_edit_tab_main_prepare_form', ['form' => $form]);
        
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Author');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Author');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _getAdditionalElementTypes()
    {
        return ['icon' => 'Netbaseteam\Faq\Block\Adminhtml\Form\Element\Image'];
    }
}
