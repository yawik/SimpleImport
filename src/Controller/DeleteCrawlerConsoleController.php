<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Controller;

use Zend\Console\ColorInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Mvc\Console\View\ViewModel;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class DeleteCrawlerConsoleController extends AbstractConsoleController
{
    private $crawlerRepository;
    private $jobRepository;

    public function __construct(\SimpleImport\Repository\Crawler $crawlerRepository, \Jobs\Repository\Job $jobRepository)
    {
        $this->crawlerRepository = $crawlerRepository;
        $this->jobRepository     = $jobRepository;
    }

    public function indexAction()
    {
        $crawler = $this->siLoadCrawler();

        foreach ($crawler->getItems() as $item) {
            $job = $this->jobRepository->find($item->getDocumentId());
            if ($job) { $job->delete(); }
        }

        $this->crawlerRepository->remove($crawler);

        $this->getConsole()->writeLine(sprintf(
            'Crawler "%s" (%s) deleted.',
            $crawler->getName(), $crawler->getId()
        ), ColorInterface::GREEN);
    }
}
