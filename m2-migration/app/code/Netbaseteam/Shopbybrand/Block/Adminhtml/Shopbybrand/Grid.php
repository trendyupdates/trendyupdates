<?php
namespace Netbaseteam\Shopbybrand\Block\Adminhtml\Shopbybrand;

/**
 * Adminhtml Shopbybrand grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Netbaseteam\Shopbybrand\Model\ResourceModel\Shopbybrand\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Netbaseteam\Shopbybrand\Model\Shopbybrand
     */
    protected $_shopbybrand;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Netbaseteam\Shopbybrand\Model\Shopbybrand $shopbybrandPage
     * @param \Netbaseteam\Shopbybrand\Model\ResourceModel\Shopbybrand\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Netbaseteam\Shopbybrand\Model\Shopbybrand $shopbybrand,
        \Netbaseteam\Shopbybrand\Model\ResourceModel\Shopbybrand\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_shopbybrand = $shopbybrand;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shopbybrandGrid');
        $this->setDefaultSort('shopbybrand_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        /* @var $collection \Netbaseteam\Shopbybrand\Model\ResourceModel\Shopbybrand\Collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn('shopbybrand_id', [
            'header'    => __('ID'),
            'index'     => 'shopbybrand_id',
        ]);
        
        $this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);
        $this->addColumn('author', ['header' => __('Author'), 'index' => 'author']);
        
        $this->addColumn(
            'published_at',
            [
                'header' => __('Published On'),
                'index' => 'published_at',
                'type' => 'date',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
        
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
        
        $this->addColumn(
            'action',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'shopbybrand_id'
                    ]
                ],
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['shopbybrand_id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
