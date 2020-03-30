<?php

namespace Tests\Bourcy\Service\Dispatch\Client;

use Bourcy\Service\Dispatch\Client\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testIndex()
    {
        $baseUri = getenv('SD_BASE_URI');
        $userName = getenv('SD_USERNAME');
        $secret = getenv('SD_SECRET');

        if (!$baseUri) {
            $this->markTestSkipped('[SD_BASE_URI] must be setted');
        }

        $client = new Client($baseUri, $userName, $secret);

        try {
            $this->assertTrue($client->ping());
        } catch (\Exception $e) {
            $this->assertTrue(false, $e->getMessage());
        }
    }
}
