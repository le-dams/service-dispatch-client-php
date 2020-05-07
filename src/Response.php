<?php

namespace Bourcy\Service\Dispatch\Client;

class Response
{
    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @var string|null
     */
    private $transactionItemId;

    /**
     * @var bool|null
     */
    private $success;

    /**
     * @var int|null
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $contentType;

    /**
     * @var string|null
     */
    private $response;

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @param string|null $transactionId
     */
    public function setTransactionId(?string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string|null
     */
    public function getTransactionItemId(): ?string
    {
        return $this->transactionItemId;
    }

    /**
     * @param string|null $transactionItemId
     */
    public function setTransactionItemId(?string $transactionItemId): void
    {
        $this->transactionItemId = $transactionItemId;
    }

    /**
     * @return bool|null
     */
    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * @param bool|null $success
     */
    public function setSuccess(?bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @param int|null $statusCode
     */
    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @param string|null $contentType
     */
    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string|null
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param string|null $response
     */
    public function setResponse(?string $response): void
    {
        $this->response = $response;
    }
}