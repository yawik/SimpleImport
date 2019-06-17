<?php
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImport\Controller;

use SimpleImport\InputFilter\CrawlerInputFilter;
use Zend\Console\ColorInterface;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

/**
 * Update crawler configuration or displays crawler information.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class UpdateCrawlerConsoleController extends AbstractConsoleController
{
    /**
     * @var CrawlerInputFilter
     */
    private $inputFilter;

    /**
     * @param CrawlerInputFilter $inputFilter
     *
     * @return self
     */
    public function setInputFilter(CrawlerInputFilter $inputFilter)
    {
        $this->inputFilter = $inputFilter;

        return $this;
    }

    /**
     * displays list of crawlers or information about one crawler
     *
     * @return string|null
     */
    public function indexAction()
    {
        /* @var \SimpleImport\Controller\Plugin\LoadCrawler $loader */

        $loader = $this->plugin('siLoadCrawler');

        if ($this->params('name')) {
            $crawler = $loader();

            return $this->info($crawler);
        }

        $set = $loader->loadAll();

        echo PHP_EOL;
        foreach ($set as $crawler) {
            printf("%'.-40s (%s)" . PHP_EOL, $crawler->getName(), $crawler->getId());
        }
    }

    /**
     * generates cralwer information message.
     *
     * @param \SimpleImport\Entity\Crawler $crawler
     *
     * @return string
     */
    private function info(\SimpleImport\Entity\Crawler $crawler)
    {
        return sprintf(<<<EOF

Name:         %s (%s) [%s]
Organization: %s (%s)
Feed-URI:     %s
Run delay:    %s

Jobs initial state: %s
Jobs recover state: %s
Date last run:      %s

EOF
            , $crawler->getName(), $crawler->getId(), $crawler->getType(),
            $crawler->getOrganization()->getOrganizationName()->getName(),
            $crawler->getOrganization()->getId(),
            $crawler->getFeedUri(),
            $crawler->getRunDelay(),
            $crawler->getOptions()->getInitialState(),
            $crawler->getOptions()->getRecoverState(),
            $crawler->getDateLastRun()->format('d.m.Y H:i:s')
        );

    }


    /**
     * update the configuration of a crawler
     *
     * @return string
     */
    public function updateAction()
    {
        /* @var \SimpleImport\Entity\Crawler $crawler */

        $loader  = $this->plugin('siLoadCrawler');
        $crawler = $loader();
        $values  = $this->validateInput($crawler);
        $console = $this->getConsole();

        $crawler->setName($values['name']);
        /** @noinspection PhpParamsInspection */
        $crawler->setOrganization($values['organization']);
        $crawler->setFeedUri($values['feedUri']);
        $crawler->setRunDelay((int) $values['runDelay']);
        $crawler->setType($values['type']);
        $crawler->setOptionsFromArray((array) $values['options']);

        $loader->store($crawler);

        $console->writeLine('Crawler updated.', ColorInterface::GREEN);
        return $this->info($crawler);
    }

    /**
     * Validates the command line arguments.
     *
     * @param \SimpleImport\Entity\Crawler $crawler
     *
     * @return array
     * @throws \RuntimeException if validation fails.
     */
    private function validateInput(\SimpleImport\Entity\Crawler $crawler)
    {
        $params = $this->plugin('params');

        $this->inputFilter->setData([
            'name' => $params('rename', $crawler->getName()),
            'feedUri' => $params('feed-uri', $crawler->getFeedUri()),
            'organization' => $params('organization', $crawler->getOrganization()),
            'runDelay' => $params('rundelay', $crawler->getRunDelay()),
            'type' => $params('type', $crawler->getType()),
            'options' => [
                'initialState' => $params('jobInitialState', $crawler->getOptions()->getInitialState()),
                'recoverState' => $params('jobRecoverState', $crawler->getOptions()->getRecoverState())
            ],
        ]);

        if (!$this->inputFilter->isValid()) {
            $message = ['Invalid parameters!'];
            foreach ($this->inputFilter->getMessages() as $name => $messages) {
                $message[] = sprintf(' - %s: %s', $name, join(PHP_EOL . str_repeat(' ', strlen($name) + 5), $messages));
            }

            throw new \RuntimeException(join(PHP_EOL . PHP_EOL, $message) . PHP_EOL);
        }

        $values = array_filter($this->inputFilter->getValues(), function($i) { return !empty($i); });
        return $values;
    }

}
