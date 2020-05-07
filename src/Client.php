<?php

namespace Bourcy\Service\Dispatch\Client;

use GuzzleHttp\Exception\GuzzleException;
use Bourcy\Service\Dispatch\Client\Exception\ServiceDispatchException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client
{
    const VERSION = '0.1';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var string|null
     */
    private $userName;

    /**
     * @var string|null
     */
    private $secretKey;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $baseUri, string $userName = null, string $secretKey = null, bool $debug = false, LoggerInterface $logger = null)
    {
        $this->userName = $userName;
        $this->secretKey = $secretKey;
        $this->debug = $debug;
        $this->logger = new NullLogger();
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $baseUri.(substr($baseUri, -1)!=='/' ? '/' : null),
        ]);
        if ($logger instanceof LoggerInterface) {
            $this->logger = $logger;
        }
    }

    /**
     * @param string $method
     * @param string $type
     * @param string $action
     * @param array|null $payload
     * @param string|null $externalId
     * @param string|null $callbackUrl
     * @return array|null
     * @throws \Exception
     */
    public function sendAsync(string $method, string $type, string $action, array $payload = null, string $externalId = null, string $callbackUrl = null): ?array
    {
        return $this->request($method, 'v'.self::VERSION.'/create', [
            'parallel' => true,
            'async' =>  true,
            'type' => $type,
            'external_id' => $externalId,
            'action' => $action,
            'payload' => $payload,
            'callback_url' => $callbackUrl,
        ]);
    }

    /**
     * @param string $method
     * @param string $type
     * @param string $action
     * @param array|null $payload
     * @param string|null $externalId
     * @param bool $parallel
     * @return array|null
     * @throws \Exception
     */
    public function sendSync(string $method, string $type, string $action, array $payload = null, string $externalId = null, bool $parallel = false): ?array
    {
        return $this->request($method,'v'.self::VERSION.'/create', [
            'parallel' => $parallel,
            'async' =>  false,
            'type' => $type,
            'external_id' => $externalId,
            'action' => $action,
            'payload' => $payload,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function sendRequest(Request $request): Response
    {
        $responseArray = $this->request($request->getMethod(),'v'.self::VERSION.'/create', [
            'parallel' => $request->isParallel(),
            'async' =>  $request->isAsync(),
            'type' => $request->getType(),
            'external_id' => $request->getExternalId(),
            'action' => $request->getAction(),
            'payload' => $request->getPayload(),
            'service' => $request->getService(),
            'callback_url' => $request->getCallbackUrl()
        ]);
        $response = new Response();
        if (isset($responseArray['success']) && is_bool($responseArray['success'])) {
            $response->setSuccess($responseArray['status_code']);
        }
        if (isset($responseArray['status_code']) && is_int($responseArray['status_code'])) {
            $response->setStatusCode($responseArray['status_code']);
        }
        if (isset($responseArray['service']) && is_string($responseArray['service'])) {
            $response->setService($responseArray['service']);
        }
        if (isset($responseArray['content_type']) && is_string($responseArray['content_type'])) {
            $response->setContentType($responseArray['content_type']);
        }
        if (isset($response['response']) && is_string($responseArray['response'])) {
            $response->setResponse($responseArray['response']);
        }
        if (isset($response['transaction_id']) && is_string($responseArray['transaction_id'])) {
            $response->setTransactionId($responseArray['transaction_id']);
        }
        if (isset($response['transaction_item_id']) && is_string($responseArray['transaction_item_id'])) {
            $response->setTransactionItemId($responseArray['transaction_item_id']);
        }
        return $response;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function ping(): bool
    {
        try {
            $this->request(self::METHOD_GET, '');
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function transactions(): ?array
    {
        return $this->request(self::METHOD_GET, 'transactions');
    }

    /**
     * @param string $transactionId
     * @param bool $externalId
     * @return array|null
     * @throws \Exception
     */
    public function transaction(string $transactionId, bool $externalId = false): ?array
    {
        if ($externalId === true) {
            return $this->request(self::METHOD_GET, 'transaction?external_id='.urlencode($externalId));
        } else {
            return $this->request(self::METHOD_GET, 'transaction/'.urlencode($transactionId));
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $params
     * @return array|null
     * @throws \Exception
     */
    private function request(string $method, string $url, array $params = null): ?array
    {
        try {
            switch (strtoupper($method)) {
                case self::METHOD_GET:
                case self::METHOD_POST:
                case self::METHOD_PUT:
                case self::METHOD_PATCH:
                case self::METHOD_DELETE:
                    break;
                default:
                    throw new ServiceDispatchException('Method not allowed [' . strtoupper($method) . ']', -405);
            }

            $headers = [
                'Content-Type' => 'application/json',
                'Dispatch-Client-Version' => '1.0.7',
                'Authorization' => 'Basic ' . base64_encode($this->userName . ':' . $this->secretKey),
            ];

            $request = [
                'body' => \json_encode($params),
                'headers' => $headers,
            ];

            $request = $this->client->request($method, $url, $request);

            if ($this->debug === true && $this->logger instanceof LoggerInterface) {
                $this->logger->debug('['.__METHOD__.':headers] '.json_encode($headers));
                $this->logger->debug('['.__METHOD__.':request] '.json_encode($headers));
            }

            $contentJson = $request->getBody()->getContents();
            return \json_decode($contentJson, JSON_OBJECT_AS_ARRAY);
        } catch (GuzzleException $guzzleException) {
            if ($this->debug === true && $this->logger instanceof LoggerInterface) {
                $this->logger->error('['.__METHOD__.']['.get_class($guzzleException).'] '.$guzzleException->getMessage());
            }
            throw new ServiceDispatchException('Error with ServiceDispatch', -1, $guzzleException);
        }
    }
}