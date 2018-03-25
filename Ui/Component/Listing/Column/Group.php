<?php

namespace Yireo\OrderGridFilterVat\Ui\Component\Listing\Column;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Psr\Log\LoggerInterface;

class Group extends Column
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    public function __construct(
        GroupRepositoryInterface $customerGroupRepository,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $order = $this->orderRepository->get($item['entity_id']);
                $customerGroup = $this->getCustomerGroupByOrder($order);
                $item[$this->getData('name')] = $customerGroup->getCode();
            }
        }

        return $dataSource;
    }

    /**
     * @param OrderInterface $order
     *
     * @return GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerGroupByOrder(OrderInterface $order): GroupInterface
    {
        return $this->customerGroupRepository->getById($order->getCustomerGroupId());
    }
}