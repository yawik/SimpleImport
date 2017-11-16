<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Hydrator;

use Zend\Hydrator\HydrationInterface;
use Jobs\Entity\AtsMode;
use SimpleImport\Job\GeocodeLocation;
use Doctrine\Common\Collections\ArrayCollection;

class JobHydrator implements HydrationInterface
{
    
    /**
     * @var GeocodeLocation
     */
    private $geocodeLocation;
    
    /**
     * @param GeocodeLocation $geocodeLocation
     */
    public function __construct(GeocodeLocation $geocodeLocation)
    {
        $this->geocodeLocation = $geocodeLocation;
    }
    
    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\HydrationInterface::hydrate()
     */
    public function hydrate(array $data, $job)
    {
        /** @var \Jobs\Entity\Job $job */
        $job->setTitle($data['title'])
            ->setLocation($data['location'])
            ->setCompany($data['company'])
            ->setReference($data['reference'])
            ->setContactEmail($data['contactEmail'])
            ->setLanguage($data['language'])
            ->setLink($data['link'])
            ->setDatePublishStart($data['datePublishStart'])
            ->setLogoRef($data['logoRef']);
        
        if ($data['datePublishEnd']) {
            $job->setDatePublishEnd($data['datePublishEnd']);
        }

        if ($data['linkApply']) {
            $job->setAtsMode(new AtsMode(AtsMode::MODE_URI, $data['linkApply']));
        } else {
            $job->setAtsMode(new AtsMode(AtsMode::MODE_NONE));
        }
        
        $locations = $this->geocodeLocation->getLocations($data['location']);
        $job->setLocations(new ArrayCollection($locations));
            
        // TODO: implement classifications
    }
}
