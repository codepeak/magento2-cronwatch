<?php

namespace Codepeak\Cronwatch\Model;

use Codepeak\Cronwatch\Api\CronwatchRepositoryInterface;
use Codepeak\Cronwatch\Api\Data\CronwatchSearchResultsInterfaceFactory;
use Codepeak\Cronwatch\Api\Data\CronwatchInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Codepeak\Cronwatch\Model\ResourceModel\Cronwatch as ResourceCronwatch;
use Codepeak\Cronwatch\Model\ResourceModel\Cronwatch\CollectionFactory as CronwatchCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CronwatchRepository
 *
 * @package Codepeak\Cronwatch\Model
 * @author  Robert Lord <robert@codepeak.se>
 */
class CronwatchRepository implements CronwatchRepositoryInterface
{
    /**
     * @var ResourceCronwatch
     */
    protected $resource;

    /**
     * @var
     */
    protected $CronwatchFactory;

    /**
     * @var
     */
    protected $CronwatchCollectionFactory;

    /**
     * @var CronwatchSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var CronwatchInterfaceFactory
     */
    protected $dataCronwatchFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CronwatchRepository constructor.
     *
     * @param ResourceCronwatch                      $resource
     * @param CronwatchFactory                       $cronwatchFactory
     * @param CronwatchInterfaceFactory              $dataCronwatchFactory
     * @param CronwatchCollectionFactory             $cronwatchCollectionFactory
     * @param CronwatchSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper                       $dataObjectHelper
     * @param DataObjectProcessor                    $dataObjectProcessor
     * @param StoreManagerInterface                  $storeManager
     */
    public function __construct(
        ResourceCronwatch $resource,
        CronwatchFactory $cronwatchFactory,
        CronwatchInterfaceFactory $dataCronwatchFactory,
        CronwatchCollectionFactory $cronwatchCollectionFactory,
        CronwatchSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->cronwatchFactory = $cronwatchFactory;
        $this->cronwatchCollectionFactory = $cronwatchCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCronwatchFactory = $dataCronwatchFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Codepeak\Cronwatch\Api\Data\CronwatchInterface $cronwatch)
    {
        try {
            $this->resource->save($cronwatch);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the cronwatch: %1', $exception->getMessage()));
        }

        return $cronwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($cronwatchId)
    {
        $cronwatch = $this->cronwatchFactory->create();
        $cronwatch->load($cronwatchId);
        if (!$cronwatch->getId()) {
            throw new NoSuchEntityException(__('Cronwatch with id "%1" does not exist.', $cronwatchId));
        }

        return $cronwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->cronwatchCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $items = [];

        foreach ($collection as $cronwatchModel) {
            $cronwatchData = $this->dataCronwatchFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $cronwatchData,
                $cronwatchModel->getData(),
                'Codepeak\Cronwatch\Api\Data\CronwatchInterface'
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $cronwatchData,
                'Codepeak\Cronwatch\Api\Data\CronwatchInterface'
            );
        }
        $searchResults->setItems($items);

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Codepeak\Cronwatch\Api\Data\CronwatchInterface $cronwatch)
    {
        try {
            $this->resource->delete($cronwatch);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the Cronwatch: %1', $exception->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($cronwatchId)
    {
        return $this->delete($this->getById($cronwatchId));
    }
}