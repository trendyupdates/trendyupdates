<?php

namespace Cmsmart\Categoryicon\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class CategoryiconLoader implements CategoryiconLoaderInterface
{
    /**
     * @var \Cmsmart\Categoryicon\Model\CategoryiconFactory
     */
    protected $categoryiconFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Cmsmart\Categoryicon\Model\CategoryiconFactory $categoryiconFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Cmsmart\Categoryicon\Model\CategoryiconFactory $categoryiconFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->categoryiconFactory = $categoryiconFactory;
        $this->registry = $registry;
        $this->url = $url;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    public function load(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int)$request->getParam('id');
        if (!$id) {
            $request->initForward();
            $request->setActionName('noroute');
            $request->setDispatched(false);
            return false;
        }

        $categoryicon = $this->categoryiconFactory->create()->load($id);
        $this->registry->register('current_categoryicon', $categoryicon);
        return true;
    }
}
