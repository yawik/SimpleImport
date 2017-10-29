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

class PlainTextFetch
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
     * @return string
     */
    public function fetch($uri)
    {
        $html = $this->httpFetch->fetch($uri);
        $matches = [];
    
        // extract content of the body tag
        preg_match('~<body.*?>(.+)?</body>~si', $html, $matches);
        
        if (!isset($matches[1])) {
            throw new RuntimeException('Cannot find a <body> tag');
        }
        
        // remove non-content tags including their content
        $body = preg_replace('~<(script|style).*?>.*?</\1>~si', '', $matches[1]);
        
        // remove all tags keeping their content
        $body = strip_tags($body);
        
        // replace multiple white spaces with a single one
        $body = trim(preg_replace('/\s+/', ' ', $body));
        
        if (!$body) {
            throw new RuntimeException('No content');
        }
        
        return $body;
    }
}