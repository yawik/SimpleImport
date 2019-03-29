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

use Jobs\Entity\Status;
use SimpleImport\Queue\GuessLanguageJob;
use Zend\Console\ColorInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

/**
 * Update crawler configuration or displays crawler information.
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class GuessLanguageConsoleController extends AbstractConsoleController
{

    private $repository;

    public static function getConsoleUsage()
    {
        return [
            'simpleimport guess-language [--limit]' => 'Find jobs without language set and pushes a guess-language job into the queue for each.',
            ['--limit=INT', 'Maximum number of jobs to fetch. 0 means fetch all.'],
            ''
        ];
    }

    public function __construct(\Jobs\Repository\Job $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * @return string|null
     */
    public function indexAction()
    {
        $qb = $this->repository->createQueryBuilder();
        $qb->field('status.name')->in([
            Status::ACTIVE, Status::WAITING_FOR_APPROVAL, Status::CREATED
        ]);
        $qb->addOr(
            $qb->expr()->field('language')->exists(false),
            $qb->expr()->field('language')->equals('')
        );
        $qb->limit(10);

        $query = $qb->getQueryArray();
        $jobs = $qb->getQuery()->execute();

        if (!count($jobs)) { echo "Nothing to do. No jobs without language found.\n\n"; return; }

        $queue = $this->queue('simpleimport');
        foreach ($jobs as $job) {
            $queue->push(GuessLanguageJob::class, ['jobId' => $job->getId()]);
            printf('Pushed job %s in the queue.' . PHP_EOL, $job->getId());
        }
    }
}
