<?php
declare(strict_types=1);

namespace Yireo\OrderGridFilterVat\Ui\Component\Listing\Column;

use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\Vat as VatModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Yireo\Attendees\Provider\Order;

class Tax extends Column
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
     * @var PricingHelper
     */
    private $pricingHelper;
    /**
     * @var VatModel
     */
    private $vatModel;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        VatModel $vatModel,
        PricingHelper $pricingHelper,
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
        $this->pricingHelper = $pricingHelper;
        $this->vatModel = $vatModel;
        $this->scopeConfig = $scopeConfig;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $order = $this->orderRepository->get($item['entity_id']);
                $item[$this->getData('name')] = $this->getColumnValueForOrder($order);
            }
        }

        return $dataSource;
    }

    private function getColumnValueForOrder(OrderInterface $order) : string
    {
        $tax = $order->getBaseTaxAmount();
        $showWarning = $this->showWarning($order);

        $formattedTax = $this->pricingHelper->currency($tax);
        $wrapperColor = ($showWarning) ? '#ffdbaf' : 'transparent';

        return '<div style="padding:5px; background-color: '.$wrapperColor.'">' . $formattedTax . '</div>';
    }

    private function showWarning(OrderInterface $order) : bool
    {
        if ($order->getState() !== 'complete') {
            return false;
        }

        if (!$this->isDutch($order) && $this->isGroupIdDomestic($order) && $this->hasPaidTax($order)) {
            return true;
        }

        if ($this->isDutch($order) && !$this->hasPaidTax($order)) {
            return true;
        }

        return false;
    }

    private function hasPaidTax(OrderInterface $order) : bool
    {
        return (bool) ((int)$order->getBaseTaxAmount() > 0);
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    private function hasCustomerTaxVat(OrderInterface $order) : bool
    {
        return (bool) (strlen($order->getCustomerTaxvat()) > 0);
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    private function isGroupIdIntraUnion(OrderInterface $order) : bool
    {
        if ($order->getCustomerGroupId() == $this->getEuGroupId()) {
            return true;
        }

        return false;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function isGroupIdDomestic(OrderInterface $order) : bool
    {
        if ($order->getCustomerGroupId() == $this->getDomesticGroupId()) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getEuGroupId(): string
    {
        $configValue = VatModel::XML_PATH_CUSTOMER_VIV_INTRA_UNION_GROUP;
        return $this->scopeConfig->getValue((string)$configValue);
    }

    /**
     * @return string
     */
    private function getDomesticGroupId(): string
    {
        $configValue = VatModel::XML_PATH_CUSTOMER_VIV_DOMESTIC_GROUP;
        return $this->scopeConfig->getValue((string)$configValue);
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    private function isDutch(OrderInterface $order) : bool
    {
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress->getCountryId() !== 'NL') {
            return false;
        }

        return true;
    }
}