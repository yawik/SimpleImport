<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Hydrator;

use Core\Entity\Collection\ArrayCollection;
use PHPUnit\Framework\TestCase;

use SimpleImport\Hydrator\JobHydrator;
use SimpleImport\Job\GeocodeLocation;
use SimpleImport\Hydrator\Job\ClassificationsHydrator;
use Jobs\Entity\Job;
use Jobs\Entity\Location;
use Jobs\Entity\AtsMode;
use stdClass;
use InvalidArgumentException;
use SimpleImport\Filter\ShufflePublishDateFilter;

/**
 * @coversDefaultClass \SimpleImport\Hydrator\JobHydrator
 */
class JobHydratorTest extends TestCase
{

    /**
     * @var JobHydrator
     */
    private $target;

    /**
     * @var GeocodeLocation
     */
    private $geocodeLocation;

    /**
     * @var ClassificationsHydrator
     */
    private $classificationsHydrator;

    /**
     * @var ShufflePublishDateFilter
     */
    private $shufflePublishDateFilter;

    /**
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->geocodeLocation = $this->getMockBuilder(GeocodeLocation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->classificationsHydrator = $this->getMockBuilder(ClassificationsHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shufflePublishDateFilter = $this->getMockBuilder(ShufflePublishDateFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->target = new JobHydrator($this->geocodeLocation, $this->classificationsHydrator, $this->shufflePublishDateFilter);
    }

    /**
     * @covers ::__construct()
     * @covers ::hydrate()
     */
    public function testHydrate()
    {
        $data = [
            'title' => 'job title',
            'location' => 'job location',
            'company' => 'job company',
            'reference' => 'job reference',
            'contactEmail' => 'job contactEmail',
            'language' => 'job language',
            'link' => 'job link',
            'datePublishStart' => '2017-11-24',
            'logoRef' => 'job logoRef',
            'datePublishEnd' => null,
            'linkApply' => null,
            'classifications' => [
                'professions' => [
                    'first profession',
                    'second profession'
                ]
            ],
        ];
        $job = new Job();

        $locations = [new Location()];
        $this->geocodeLocation->expects($this->exactly(3))
            ->method('getLocations')
            ->with($this->equalTo($data['location']))
            ->willReturn($locations);

        $this->classificationsHydrator->expects($this->exactly(3))
            ->method('hydrate')
            ->with($this->equalTo($data['classifications']), $this->identicalTo($job->getClassifications()));

            $this->shufflePublishDateFilter->expects($this->exactly(3))->method('filter')->will($this->returnArgument(0));
        $this->target->hydrate($data, $job);
        $this->assertSame($data['title'], $job->getTitle());
        $this->assertSame($data['location'], $job->getLocation());
        $this->assertSame($data['company'], $job->getCompany());
        $this->assertSame($data['reference'], $job->getReference());
        $this->assertSame($data['contactEmail'], $job->getContactEmail());
        $this->assertSame($data['language'], $job->getLanguage());
        $this->assertSame($data['datePublishStart'], $job->getDatePublishStart()->format('Y-m-d'));
        $this->assertSame($data['logoRef'], $job->getLogoRef());
        $this->assertNull($job->getDatePublishEnd());
        $atsMode = $job->getAtsMode();
        $this->assertInstanceOf(AtsMode::class, $atsMode);
        $this->assertSame(AtsMode::MODE_EMAIL, $atsMode->getMode());

        $data['datePublishEnd'] = '2017-12-24';
        $data['linkApply'] = 'job link apply';
        $this->target->hydrate($data, $job);
        $this->assertSame($data['datePublishEnd'], $job->getDatePublishEnd()->format('Y-m-d'));
        $atsMode = $job->getAtsMode();
        $this->assertInstanceOf(AtsMode::class, $atsMode);
        $this->assertSame(AtsMode::MODE_URI, $atsMode->getMode());
        $this->assertSame($data['linkApply'], $atsMode->getUri());

        // check mode none if linkApply and contactEmail is null
        $data['linkApply'] = null;
        $data['contactEmail'] = null;
        $this->target->hydrate($data, $job);
        $atsMode = $job->getAtsMode();
        $this->assertSame(AtsMode::MODE_NONE, $atsMode->getMode());
    }

    /**
     * @covers ::__construct()
     * @covers ::hydrate()
     */
    public function testHydrateInvalidObjectPassed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Object must be instance');
        $this->target->hydrate([], new stdClass());
    }

    /**
     * @testWith    [null]
     *              [""]
     */
    public function testRemoveOldLocationIfNewLocationIsEmpty($value)
    {
        $job = new Job();
        $location = new Location();
        $location->setCity('Job City');
        $job->setLocation('Job Location');
        $job->setLocations(new ArrayCollection([$location]));

        $data = [
            'title' => 'job title',
            'location' => $value,
            'company' => 'job company',
            'reference' => 'job reference',
            'contactEmail' => 'job contactEmail',
            'language' => 'job language',
            'link' => 'job link',
            'datePublishStart' => '2017-11-24',
            'logoRef' => 'job logoRef',
            'datePublishEnd' => null,
            'linkApply' => null,
            'classifications' => [
                'professions' => [
                    'first profession',
                    'second profession'
                ]
            ],
        ];

        $this->target->hydrate($data, $job);

        $this->assertEmpty($job->getLocation());
        $this->assertEmpty($job->getLocations());
    }
}
