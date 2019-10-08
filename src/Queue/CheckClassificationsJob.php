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
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;

/**
 * ${CARET}
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class CheckClassificationsJob extends MongoJob implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        $classifications = $job->getClassifications()->{"get$category"}();
        $items = $classifications->getItems();
        $currentValues = [];

        $logger->info('Check job for ' . $category . ' -> ' . join(', ', $requiredCategories));

        foreach ($items as $item) {
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
                }
            }
        }

        if (!count($requiredCategories)) {
            $metaData->checked('Job had all required categories.');
            $metaData->storeIn($job);
            return $this->success('Job has already all required categories');
        }

        $logger->info('Job missing categories:' . join(', ', $requiredCategories));
        $treeStrategy = new TreeSelectStrategy();
        $treeStrategy->setTreeRoot($root);
        $treeStrategy->setShouldCreateLeafs(true);
        $treeStrategy->setShouldUseNames(true);
        $treeStrategy->setAllowSelectMultipleItems(true);
        $treeStrategy->setAttachedLeafs($classifications);
        $newValues = array_merge($currentValues, $requiredCategories);

        $treeStrategy->hydrate($newValues);

        $metaData->checked('Added categories:' . join(', ', $requiredCategories));
        $metaData->storeIn($job);

        $this->solr->forceUpdate($job);

        /* We do not want to wait for the auto flush of the core module
         * because the queue process could be run a long time */
        $this->repository->getDocumentManager()->flush();

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
        return (array) ($this->content['categories'] ?? '');
    }

    public function setContent($value)
    {
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new \InvalidArgumentException('Payload must be an array or \Traversable');
        }

        return parent::setContent($value);
    }
}
