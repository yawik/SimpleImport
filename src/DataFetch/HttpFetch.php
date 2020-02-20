<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\DataFetch;

use Laminas\Http\Client;
use Exception;
use RuntimeException;

class HttpFetch
{
    
    /**
     * @var Client
     */
    private $client;
    
    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * @param string $uri
     * @return string
     * @throws RuntimeException
     */
    public function fetch($uri)
    {
        try {
            $this->client->setUri($uri);
            $response = $this->client->send();
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Unable to fetch remote data, reason: "%s"', $e->getMessage()));
        }
        
        if (!$response->isOk()) {
            throw new RuntimeException(sprintf('Invalid HTTP status: "%d"', $response->getStatusCode()));
        }
        
        return $response->getBody();
    }
}