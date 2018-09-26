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
        /* @var \SimpleImport\Entity\Crawler $crawler */
        $crawlerName = $this->params('name');
        $isCrawlerId = $this->params('id');
        $crawler     = $isCrawlerId ? $this->crawlerRepository->find($crawlerName) : $this->crawlerRepository->findOneByName($crawlerName);

        if (!$crawler) {
            $model = new ViewModel();
            $model->setErrorLevel(2);
            $model->setResult(sprintf('Crawler with %s "%s" does not exist.', $isCrawlerId ? 'id' : 'name', $crawlerName));
            return $model;
        }

        foreach ($crawler->getItems() as $item) {
            $this->jobRepository->find($item->getDocumentId())->delete();
        }

        $this->crawlerRepository->remove($crawler);

        $this->getConsole()->writeLine(sprintf(
            'Crawler "%s" (%s) deleted.',
            $crawler->getName(), $crawler->getId()
        ), ColorInterface::GREEN);
    }
}
