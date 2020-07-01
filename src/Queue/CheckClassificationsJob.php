<?php declare(strict_types=1);
/**
 * YAWIK-SimpleImport
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2019 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImport\Queue;

use Core\Form\Hydrator\Strategy\TreeSelectStrategy;
use Core\Queue\Job\MongoJob;
use Jobs\Repository\Categories;
use Jobs\Repository\Job;
use SimpleImport\Entity\CheckClassificationsMetaData;
use SimpleImport\Listener\SolrJobEventListenerFacade;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;

/**
 * ${CARET}
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class CheckClassificationsJob extends MongoJob implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private static $flushCount = 1;
    private $repository;
    private $categoriesRepository;
    private $solr;

    public function __construct(
        Job $repository,
        Categories $categories,
        SolrJobEventListenerFacade $solr
    ) {
        $this->repository = $repository;
        $this->categoriesRepository = $categories;
        $this->solr = $solr;
    }

    public function execute()
    {
        if (!$this->repository || !$this->categoriesRepository || !$this->solr) {
            return $this->failure('Cannot execute without dependencies.');
        }

        /** @var \Jobs\Entity\Job $job */
        $logger = $this->getLogger();
        $jobId = $this->getJobId();
        $job   = $this->repository->find($jobId);

        if (!$job) {
            return $this->failure('A job with the id "' . $jobId . '" does not exist.');
        }

        $logger->info('Processing job: ' . $job->getId());
        /** @var \Core\Entity\Tree\EmbeddedLeafs $classifications */
        $category = $this->getRootCategory();
        $root = $this->categoriesRepository->findOneBy(['value' => $category]);
        $metaData = CheckClassificationsMetaData::fromJob($job, $category);
        $requiredCategories = $this->getRequiredCategories();
        $replaceCategories = $this->getReplaceCategories();
        $deleteCategories = $this->getDeleteCategories();
        $classifications = $job->getClassifications()->{"get$category"}();
        $items = $classifications->getItems();
        $currentValues = [];
        $removedValues = [];
        $replacedValues = [];

        $logger->info('Check job for ' . $category . ' -> ' . join(', ', $requiredCategories));

        foreach ($items as $item) {
            foreach ($deleteCategories as $deleteCategory) {
                if ($item->getName() == $deleteCategory || $item->getValue == $deleteCategory) {
                    $removedValues[] = $deleteCategory;
                    continue 2;
                }
            }

            foreach ($replaceCategories as $old => $new) {
                if ($item->getName() == $old || $item->getValue() == $old) {
                    $currentValues[] = $new;
                    $replacedValues[] = "$old==$new";
                    continue 2;
                }
            }

            $currentValues[] = $item->getName();
            foreach ($requiredCategories as $requiredCategory) {
                if ($item->getName() == $requiredCategory
                    || $item->getValue() == $requiredCategory
                ) {
                    $requiredCategories = array_filter(
                        $requiredCategories,
                        function ($i) use ($requiredCategory) {
                            return $i != $requiredCategory;
                        }
                    );
                    continue 2;
                }
            }


        }

        if (!count($requiredCategories) && !count($removedValues) && !count($replacedValues)) {
            $metaData->checked('Job had all required categories, nothing to replace/remove.');
            $metaData->storeIn($job);
            return $this->success('Job has already all required categories, nothing to delete or replace');
        }

        $message = '';

        if (count($requiredCategories)) {
            $message .= 'Added: ' . join(', ', $requiredCategories) . PHP_EOL;
        }

        if (count($removedValues)) {
            $message .= 'Removed: ' . join(', ', $removedValues) . PHP_EOL;
        }

        if (count($replacedValues)) {
            $message .= 'Replaced: ' . join(', ', $replacedValues) . PHP_EOL;
        }

        $logger->info(PHP_EOL . $message);
        $treeStrategy = new TreeSelectStrategy();
        $treeStrategy->setTreeRoot($root);
        $treeStrategy->setShouldCreateLeafs(true);
        $treeStrategy->setShouldUseNames(true);
        $treeStrategy->setAllowSelectMultipleItems(true);
        $treeStrategy->setAttachedLeafs($classifications);
        $newValues = array_merge($currentValues, $requiredCategories);

        $treeStrategy->hydrate($newValues);

        $metaData->checked($message);
        $metaData->storeIn($job);

        $this->solr->forceUpdate($job);

        /* We do not want to wait for the auto flush of the core module
         * because the queue process could be run a long time.
         * Yet we do not want to flush after every job. So we only flush
         * after 100 jobs.
         * TODO: Make flushCount limit configurable
         */
        if (! (self::$flushCount++ % 100)) {
            $logger->notice('Flush to database...');
            $this->repository->getDocumentManager()->flush();
        }

        return $this->success('Added categories:' . join(', ', $requiredCategories));
    }

    public function setJobId($jobId)
    {
        $this->content['jobId'] = $jobId;
    }

    public function getJobId()
    {
        return $this->content['jobId'] ?? 0;
    }

    public function getRootCategory(): ?string
    {
        return $this->content['root'] ?? null;
    }

    public function getRequiredCategories(): array
    {
        $categories = (array) ($this->content['categories'] ?? '');
        $categories = array_filter($categories, function ($i) { return strpos($i, '--') !== 0 && strpos($i, '==') === false; });

        return $categories;
    }

    public function getReplaceCategories(): array
    {
        $categories = (array) ($this->content['categories'] ?? '');
        $categories = array_filter($categories, function ($i) { return strpos($i, '==') !== false; });
        $return = [];
        foreach ($categories as $category) {
            [$old, $new] = explode('==', $category, 2);
            $return[trim($old)] = trim($new);
        }
        return $return;
    }

    public function getDeleteCategories(): array
    {
        $categories = (array) ($this->content['categories'] ?? '');
        $categories = array_filter($categories, function ($i) { return strpos($i, '--') === 0; });
        $categories = array_map(function ($i) { return substr($i, 2); }, $categories);

        return $categories;
    }

    public function setContent($value)
    {
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new \InvalidArgumentException('Payload must be an array or \Traversable');
        }

        return parent::setContent($value);
    }
}
