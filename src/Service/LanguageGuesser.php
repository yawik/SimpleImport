<?php
/**
 * YAWIK-SimpleImport
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2019 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Service;

use Zend\Http\Client;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class LanguageGuesser 
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function guess($text, $languages = ['de', 'en', 'fr'])
    {
        $this->client->resetParameters();
        $this->client->setParameterPost([
            'languages' => $languages,
            'text' => $text,
        ]);
        $this->client->setEncType(Client::ENC_FORMDATA);
        $this->client->setMethod('POST');

        $response = $this->client->send();

        if (200 != $response->getStatusCode()) {
            return [
                'ok' => false,
                'error' => $response->getBody()
            ];
        }

        $lang = \Zend\Json\Json::decode($response->getBody(), \Zend\Json\Json::TYPE_ARRAY);

        return [
            'ok' => true,
            'language' => $lang['language'],
        ];
    }
}
