<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Controller;

use OutOfBoundsException;
use SimpleImport\Entity\CheckClassificationsMetaData;
use SimpleImport\Queue\CheckClassificationsJob;
use Laminas\Mvc\Console\Controller\AbstractConsoleController;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class CheckClassificationsConsoleController extends AbstractConsoleController
{
    public static function getConsoleUsage()
    {
        return [
            'simpleimport check-classifications <root> <categories> [<query>]'
            =>
            'Check job classifications.',
            ['  <root>', 'Root category ("industrues", "professions" or "employmentTypes")'],
            ['', ''],
            ['  <categories>', 'Required categories, comma separated. E.g. "Fulltime, Internship"'],
            ['',               '(Replace categories with "old-category==new-category)'],
            ['',               '(Delete categories with --category)'],
            ['',               'E.g.: "Fulltime, part-time==Parttime, --internship"'],
            ['', ''],
            ['  <query>', 'Search query for selecting jobs.'],
            '',
            ['  --force', 'Do not ignore already checked jobs.'],
        ];
    }

    public function indexAction()
    {
        /** @var \Laminas\Paginator\Paginator $jobs */
        $jobs = $this->paginator('Jobs/Board', [], [
            'q' => $this->params('query'),
        ]);

        printf("Found %d jobs.\n", $jobs->getTotalItemCount());
        $jobs->setItemCountPerPage($jobs->getTotalItemCount());

        $categories = array_map('trim', explode(',', $this->params('categories', '')));
        $root = $this->params('root');
        if (!in_array($root, ['industries', 'professions', 'employmentTypes'])) {
            throw new OutOfBoundsException('Root must be one of "industries", "professions" or "employmentTypes"');
        }

        $queue = $this->queue('simpleimport');
        $force = $this->params('force', false);

        foreach ($jobs as $job) {
            /** @var \Jobs\Entity\JobInterface $job */
            $metaData = CheckClassificationsMetaData::fromJob($job, $root);

            if (!$force && !$metaData->isUnchecked()) {
                printf('Job %s already checked.' . PHP_EOL, $job->getId());
                continue;
            }

            $queue->pushJob(CheckClassificationsJob::create(
                [
                    'jobId' => $job->getId(),
                    'root' => $root,
                    'categories' => $categories,
                ]
            ));
            $metaData->queued('Queued job for processing');
            $metaData->storeIn($job);
            printf('Pushed job %s in the queue.' . PHP_EOL, $job->getId());
        }
    }
}
