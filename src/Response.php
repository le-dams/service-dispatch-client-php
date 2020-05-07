<?php

namespace Bourcy\Service\Dispatch\Client;

class Response implements \Countable
{
    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @var ResponseItem[]
     */
    private $items = [];

    public function __construct(string $transactionId, array $items = [])
    {
        $this->transactionId = $transactionId;
        $this->items = $items;
    }

    public function count()
    {
        return count($this->items);
    }

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @return ResponseItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return ResponseItem[]
     */
    public function getSuccessItems(): array
    {
        return array_filter($this->getItems(), function (ResponseItem $item) {
           return $item->isSuccess() === true;
        });
    }

    /**
     * @return ResponseItem[]
     */
    public function getFailedItems(): array
    {
        return array_filter($this->getItems(), function (ResponseItem $item) {
            return $item->isSuccess() === false;
        });
    }

    /**
     * @return ResponseItem[]
     */
    public function getNoResponseItems(): array
    {
        return array_filter($this->getItems(), function (ResponseItem $item) {
            return $item->isSuccess() === null;
        });
    }

    /**
     * @param string $service
     * @return ResponseItem[]
     */
    public function findItemsByService(string $service): array
    {
        return array_filter($this->getItems(), function (ResponseItem $item) use ($service) {
            return strtolower($item->getService()) === strtolower($service);
        });
    }
}
