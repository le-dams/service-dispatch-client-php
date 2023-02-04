<?php

namespace Bourcy\Service\Dispatch\Client;

use GuzzleHttp\Exception\GuzzleException;
use Bourcy\Service\Dispatch\Client\Exception\ServiceDispatchException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client
{
    const VERSION = '2.0.0';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    /**
     * @var string
     */
    private string $secretKey;

    /**
     * @var \GuzzleHttp\Client
     */
    private \GuzzleHttp\Client $client;

    /**
     * @var bool
     */
    private bool $debug = false;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(string $baseUri, string $secretKey = null, bool $debug = false, LoggerInterface $logger = null)
    {
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
        $items = [];
        if (isset($responseArray['items']) && is_array($responseArray['items'])) {
            foreach ($responseArray['items'] as $itemArray) {
                $item = new ResponseItem();
                if (isset($itemArray['success']) && is_bool($itemArray['success'])) {
                    $item->setSuccess($itemArray['success']);
                }
                if (isset($itemArray['status_code']) && is_int($itemArray['status_code'])) {
                    $item->setStatusCode($itemArray['status_code']);
                }
                if (isset($itemArray['service']) && is_string($itemArray['service'])) {
                    $item->setService($itemArray['service']);
                }
                if (isset($itemArray['content_type']) && is_string($itemArray['content_type'])) {
                    $item->setContentType($itemArray['content_type']);
                }
                if (isset($itemArray['response']) && is_string($itemArray['response'])) {
                    $item->setResponse($itemArray['response']);
                }
                if (isset($itemArray['transaction_item_id']) && is_string($itemArray['transaction_item_id'])) {
                    $item->setTransactionItemId($itemArray['transaction_item_id']);
                }
                $items[] = $item;
            }
        }
        return new Response($responseArray['transaction_id'], $items);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function ping(): bool
    {
        try {
            $response = $this->request(self::METHOD_GET, '');
            if (null === $response) {
                if (true === $this->debug) {
                    $this->logger->warning('[ping] Response empty');
                }
                return false;
            }
            if (false === array_key_exists('application', $response)) {
                if (true === $this->debug) {
                    $this->logger->warning('[ping] Wrong response', [
                        'response' => $response
                    ]);
                }
                return false;
            }
            return true;
        } catch(\Exception $e) {
            $this->logger->error($e);
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
                'Dispatch-Client-Version' => self::VERSION,
                'Authorization' => 'Bearer ' . $this->secretKey,
            ];

            $request = [
                'body' => \json_encode($params),
                'headers' => $headers,
            ];

            $request = $this->client->request($method, $url, $request);

            if (true === $this->debug) {
                $this->logger->debug('['.__METHOD__.'] ', [
                    'params' => is_array($params) ? array_keys($params) : null,
                    'headers' => $headers
                ]);
            }

            $contentJson = $request->getBody()->getContents();
            return \json_decode($contentJson, true);
        } catch (GuzzleException $guzzleException) {
            $this->logger->error($guzzleException);
            throw new ServiceDispatchException('Error with ServiceDispatch', -1, $guzzleException);
        }
    }
}