<?php

namespace Yireo\OrderGridFilterVat\Ui\Component\Listing\Column\Tax;

use Magento\Framework\Data\OptionSourceInterface;
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
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
        }

        return $this->options;
    }
}
