<?php

namespace Bourcy\Service\Dispatch\Client;

class Request
{
    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $action;

    /**
     * @var array|null
     */
    private $payload;

    /**
     * @var string|null
     */
    private $externalId;

    /**
     * @var string|null
     */
    private $service;

    /**
     * @var bool
     */
    private $async = false;

    /**
     * @var bool
     */
    private $parallel = false;

    /**
     * @var string|null
     */
    private $callbackUrl = null;

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return array|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param array|null $payload
     */
    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     */
    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string|null
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * @param string|null $service
     */
    public function setService(?string $service): void
    {
        $this->service = $service;
    }

    /**
     * @return bool
     */
    public function isAsync(): bool
    {
        return $this->async;
    }

    /**
     * @param bool $async
     */
    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    /**
     * @return bool
     */
    public function isParallel(): bool
    {
        return $this->parallel;
    }

    /**
     * @param bool $parallel
     */
    public function setParallel(bool $parallel): void
    {
        $this->parallel = $parallel;
    }

    /**
     * @return string|null
     */
    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    /**
     * @param string|null $callbackUrl
     */
    public function setCallbackUrl(?string $callbackUrl): void
    {
        $this->callbackUrl = $callbackUrl;
    }
}