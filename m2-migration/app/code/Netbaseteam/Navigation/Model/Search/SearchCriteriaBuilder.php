<?php
namespace Netbaseteam\Navigation\Model\Search;

use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder as SourceSearchCriteriaBuilder;

/**
 * Builder for SearchCriteria Service Data Object
 */
class SearchCriteriaBuilder extends SourceSearchCriteriaBuilder
{
	/**
	 * @param ObjectFactory $objectFactory
	 * @param FilterGroupBuilder $filterGroupBuilder
	 * @param SortOrderBuilder $sortOrderBuilder
	 */
	public function __construct(
		ObjectFactory $objectFactory,
		FilterGroupBuilder $filterGroupBuilder,
		SortOrderBuilder $sortOrderBuilder
	)
	{
		parent::__construct($objectFactory, $filterGroupBuilder, $sortOrderBuilder);
	}

	public function removeFilter($attributeCode)
	{
		$this->filterGroupBuilder->removeFilter($attributeCode);

		return $this;
	}

	public function setFilterGroupBuilder($filterGroupBuilder)
	{
		$this->filterGroupBuilder = $filterGroupBuilder;
	}

	public function cloneObject()
	{
		$cloneObject = clone $this;
		$cloneObject->setFilterGroupBuilder($this->filterGroupBuilder->cloneObject());

		return $cloneObject;
	}
}
