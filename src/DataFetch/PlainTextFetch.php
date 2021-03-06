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

        if (preg_match("//u", $html)) {
            // $html is valid UTF-8
            $html=preg_replace("/iso-8859-1/","utf-8",$html);

        } else if ($encoding = mb_detect_encoding($html, ['iso-8859-15', 'latin1'])) {
            $html = mb_convert_encoding($html, 'utf8', $encoding);
            $html = preg_replace('~' . preg_quote($encoding) . '|iso-8859-.{1,2}~', 'utf-8', $html);

        } else {
            $html = utf8_encode($html);
        }

        // remove non-content tags including their content.

        $oldErrorReporting = error_reporting(0);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        // delete js
        while($elem = $dom->getElementsByTagName("script")->item(0)) {
            $elem->parentNode->removeChild($elem);
        }
        // delete style
        while($elem = $dom->getElementsByTagName("style")->item(0)) {
            $elem->parentNode->removeChild($elem);
        }
        // delete forms
        while($elem = $dom->getElementsByTagName("form")->item(0)) {
            $elem->parentNode->removeChild($elem);
        }
        // delete links
        while($elem = $dom->getElementsByTagName("a")->item(0)) {
            $elem->parentNode->removeChild($elem);
        }

        $body = $dom->saveHTML();
        error_reporting($oldErrorReporting);

        $body=html_entity_decode($body);

        // make sure, that ther is allways a space in front of a tag
        $body = trim(strip_tags(str_replace('<', ' <', $body)));

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
