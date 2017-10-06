<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\RemoteFetch;

use Zend\Http\Client;
use Exception;
use RuntimeException;

class JsonRemoteFetch
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
     * @return array
     * @throws RuntimeException
     */
    public function fetch($uri)
    {
        $this->client->setUri($uri);
        
        try {
            $response = $this->client->send();
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Unable to fetch remote data, reason: "%s"', $e->getMessage()));
        }
        
        if (!$response->isOk()) {
            throw new RuntimeException(sprintf('Invalid HTTP status: "%d"', $response->getStatusCode()));
        }
        
        $data = json_decode($response->getBody(), true);
        
        if (!is_array($data)) {
            throw new RuntimeException(sprintf('Invalid data, reason: "%s"', json_last_error_msg()));
        }
        
        if (!isset($data['jobs']) || !is_array($data['jobs'])) {
            throw new RuntimeException('Invalid data, a jobs key is missing or invalid');
        }
        
        return $data['jobs'];
    }
}