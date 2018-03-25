<?php

namespace Yireo\OrderGridFilterVat\Ui\Component\Listing\Column\Group;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GroupRepositoryInterface $customerGroupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Get options
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $results = $this->customerGroupRepository->getList($searchCriteria);

            $items = $results->getItems();
            foreach ($items as $item) {
                $this->options[] = ['value' => $item->getId(), 'label' => $item->getCode()];
            }
        }

        return $this->options;
    }
}
