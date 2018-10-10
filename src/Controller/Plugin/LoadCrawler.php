<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Controller\Plugin;

use SimpleImport\Repository\Crawler;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Plugin to load a crawler entity according to route parameters.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class LoadCrawler extends AbstractPlugin
{
    /**
     * Crawler repository
     *
     * @var Crawler
     */
    private $repository;


    /**
     * LoadCrawler constructor.
     *
     * @param Crawler $repository
     */
    public function __construct(Crawler $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Loads a crawler entity.
     *
     * Uses the `name` parameter as either the crawlers' name or its id, if the `--id` flag was given.
     *
     * @return \SimpleImport\Entity\Crawler
     * @throws \RuntimeException if no crawler is found.
     */
    public function __invoke()
    {
        /* @var \Zend\Mvc\Controller\AbstractActionController $controller */
        $controller = $this->getController();
        $params     = $controller->plugin('params');

        $crawlerName = $params('name');
        $isCrawlerId = $params('id');
        $crawler     = $isCrawlerId ? $this->repository->find($crawlerName) : $this->repository->findOneByName($crawlerName);

        if (!$crawler) {
            throw new \RuntimeException(sprintf('Crawler with %s "%s" does not exist.', $isCrawlerId ? 'id' : 'name', $crawlerName));
        }

        return $crawler;
    }
}
