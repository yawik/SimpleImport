<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\DataFetch;

use PHPUnit\Framework\TestCase;
use SimpleImport\DataFetch\HttpFetch;
use Zend\Http\Client;
use Zend\Http\Response;
use Exception;

/**
 * @coversDefaultClass \SimpleImport\DataFetch\HttpFetch
 */
class HttpFetchTest extends TestCase
{

    /**
     * @var HttpFetch
     */
    private $target;

    /**
     * @var Client
     */
    private $client;

    /**
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->target = new HttpFetch($this->client);
    }

    /**
     * @covers ::__construct()
     * @covers ::fetch()
     */
    public function testFetchWithSuccessfulResponse()
    {
        $uri = 'uriString';
        $body = 'bodyString';

        $this->client->expects($this->once())
            ->method('setUri')
            ->with($uri);

        $response = new Response();
        $response->setContent($body);

        $this->client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $this->assertSame($body, $this->target->fetch($uri));
    }

    /**
     * @covers ::__construct()
     * @covers ::fetch()
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to fetch remote data
     */
    public function testUnableFetchRemoteData()
    {
        $this->client->expects($this->once())
            ->method('send')
            ->will($this->throwException(new Exception()));

        $this->target->fetch('uriString');
    }

    /**
     * @covers ::__construct()
     * @covers ::fetch()
     * @expectedException RuntimeException
     * @expectedExceptionMessage Invalid HTTP status
     */
    public function testFetchWithUnsuccessfulResponse()
    {
        $response = new Response();
        $response->setStatusCode(404);

        $this->client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $this->target->fetch('uriString');
    }
}
