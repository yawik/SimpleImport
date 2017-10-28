<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\DataFetch;

use RuntimeException;

class JsonFetch
{
    
    /**
     * @var HttpFetch
     */
    private $httpFetch;
    
    /**
     * @param HttpFetch $httpFetch
     */
    public function __construct(HttpFetch $httpFetch)
    {
        $this->httpFetch = $httpFetch;
    }

    /**
     * @param string $uri
     * @throws RuntimeException
     * @return mixed
     */
    public function fetch($uri)
    {
        $data = json_decode($this->httpFetch->fetch($uri), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf('Invalid data, reason: "%s"', json_last_error_msg()));
        }
        
        return $data;
    }
}