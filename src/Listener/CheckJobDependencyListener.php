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
use Core\Service\EntityEraser\DependencyResult;
use Core\Service\EntityEraser\DependencyResultEvent;
use Jobs\Entity\Job;
use SimpleImport\Entity\Crawler;
use SimpleImport\Entity\Item;

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
        /**
         * @var Crawler $crawler
         * @var \SimpleImport\Entity\Item $item
         */
        $repository = $event->getRepository(Item::class);
        $jobId = $event->getEntity()->getId();
        $item    = $repository->findOneBy(['documentId' => $jobId]);

        if (!$item) { return null; }

        if ($event->isDelete()) {
            try {
                $crawler = $item->getCrawler();
                $options = [
                    'mode' => DependencyResult::MODE_DELETE,
                    'description' => 'deleted.',
                ];
                $ids = $crawler->getMetaData('documentIds');
                $ids = array_filter(
                    $ids,
                    function ($i) use ($jobId) {
                        return $i != $jobId;
                    }
                );
                if (is_array($ids)) {
                    $crawler->setItemsMetaData($ids);
                }
            } catch (\Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
            }
        } else {
            $options = 'of ' . $item->getCrawler();
        }

        return ['SimpleImport/CrawlerItem', [$item], $options];
    }
}
