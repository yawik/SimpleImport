<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Repository;

use Core\Repository\AbstractRepository;
use Organizations\Entity\Organization;
use DateTime;

class Crawler extends AbstractRepository
{
    
    /**
     * {@inheritDoc}
     * @see \Core\Repository\AbstractRepository::create()
     */
    public function create(array $data = null, $persist = false)
    {
        if (isset($data['organization']) && !$data['organization'] instanceof Organization) {
            $data['organization'] = $this->dm->getReference(Organization::class, $data['organization']);
        }
        
        if (!isset($data['dateLastRun'])) {
            $data['dateLastRun'] = new DateTime();
        }
        
        return parent::create($data, $persist);
    }

    /**
     * @param DateTime $runDelay
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Cursor|\SimpleImport\Entity\Crawler[]
     */
    public function getCrawlersToImport(DateTime $runDelay, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('dateLastRun.date')->lt($runDelay)
            ->sort(['dateLastRun.date' => 1]);
        
        if (isset($limit)) {
            $qb->limit($limit);
        }
        
        return $qb->getQuery()->execute();
    }
}