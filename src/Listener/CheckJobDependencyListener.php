<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Listener;

use Core\Service\EntityEraser\AbstractDependenciesListener;
use Core\Service\EntityEraser\DependencyResultEvent;
use Jobs\Entity\Job;
use SimpleImport\Entity\Crawler;

/**
 * Check dependencies on job deletion.
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class CheckJobDependencyListener extends AbstractDependenciesListener
{
    protected $entityClasses = [ Job::class ];

    protected function dependencyCheck(DependencyResultEvent $event)
    {
        /* @var Crawler $crawler
         * @var \SimpleImport\Repository\Crawler $repository */
        $repository = $event->getRepository(Crawler::class);
        $crawler    = $repository->findOneBy(['metaData.documentIds' => $event->getEntity()->getId()]);

        if (!$crawler) { return null; }

        if ($event->isDelete()) {
            $items = $crawler->getItemsCollection();
            foreach ($items as $item) {
                if ($event->getEntity()->getId() == $item->getDocumentId()) {
                    $items->removeElement($item);
                    break;
                }
            }

            $desc = 'removed item from collection [ ' . $crawler->getName() . ' ]';
        } else {
            $desc = 'Item references this job will be removed from crawler item collection';
        }

        return ['SimpleImport/CrawlerItem', [$crawler], $desc];
    }
}
