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

use PHPUnit\Framework\TestCase;

use SimpleImport\Hydrator\JobHydrator;
use SimpleImport\Job\GeocodeLocation;
use SimpleImport\Hydrator\Job\ClassificationsHydrator;
use Jobs\Entity\Job;
use Jobs\Entity\Location;
use Jobs\Entity\AtsMode;
use stdClass;

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
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->geocodeLocation = $this->getMockBuilder(GeocodeLocation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->classificationsHydrator = $this->getMockBuilder(ClassificationsHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->target = new JobHydrator($this->geocodeLocation, $this->classificationsHydrator);
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
        $this->geocodeLocation->expects($this->exactly(2))
            ->method('getLocations')
            ->with($this->equalTo($data['location']))
            ->willReturn($locations);

        $this->classificationsHydrator->expects($this->exactly(2))
            ->method('hydrate')
            ->with($this->equalTo($data['classifications']), $this->identicalTo($job->getClassifications()));

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
        $this->assertSame(AtsMode::MODE_NONE, $atsMode->getMode());

        $data['datePublishEnd'] = '2017-12-24';
        $data['linkApply'] = 'job link apply';
        $this->target->hydrate($data, $job);
        $this->assertSame($data['datePublishEnd'], $job->getDatePublishEnd()->format('Y-m-d'));
        $atsMode = $job->getAtsMode();
        $this->assertInstanceOf(AtsMode::class, $atsMode);
        $this->assertSame(AtsMode::MODE_URI, $atsMode->getMode());
        $this->assertSame($data['linkApply'], $atsMode->getUri());
    }

    /**
     * @covers ::__construct()
     * @covers ::hydrate()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Object must be instance
     */
    public function testHydrateInvalidObjectPassed()
    {
        $this->target->hydrate([], new stdClass());
    }
}
