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

namespace SimpleImportTest\Hydrator\Job;

use PHPUnit\Framework\TestCase;

use SimpleImport\Hydrator\Job\ClassificationsHydrator;
use Core\Form\Hydrator\Strategy\TreeSelectStrategy;
use Jobs\Repository\Categories;
use Jobs\Entity\Classifications;
use Jobs\Entity\Category;
use Core\Entity\Tree\EmbeddedLeafs;
use stdClass;
use InvalidArgumentException;

/**
 * @coversDefaultClass \SimpleImport\Hydrator\Job\ClassificationsHydrator
 */
class ClassificationsHydratorTest extends TestCase
{

    /**
     * @var ClassificationsHydrator
     */
    private $target;

    /**
     * @var TreeSelectStrategy
     */
    private $treeStrategy;

    /**
     * @var Categories
     */
    private $categories;

    /**
     * @var array
     */
    private $availableClassifications;

    /**
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->treeStrategy = $this->getMockBuilder(TreeSelectStrategy::class)
            ->getMock();

        $this->categories = $this->getMockBuilder(Categories::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->availableClassifications = [
            'professions',
            'industries',
            'employmentTypes',
        ];

        $this->target = new ClassificationsHydrator($this->treeStrategy, $this->categories, $this->availableClassifications);
    }

    /**
     * @covers ::__construct()
     * @covers ::hydrate()
     * @covers ::getTreeRoot()
     */
    public function testHydrate()
    {
        $data = [
            'professions' => [
                'first profession',
                'second profession'
            ],
            'industries' => [
                'first industry',
                'second industry'
            ],
            'employmentTypes' => [
                'first employmentType',
                'second employmentType'
            ]
        ];
        $classifications = new Classifications();
        $classifications->setProfessions($professions = new EmbeddedLeafs());
        $classifications->setIndustries($industries = new EmbeddedLeafs());
        $classifications->setEmploymentTypes($employmentTypes = new EmbeddedLeafs());
        $numberOfAvailableClassifications = count($this->availableClassifications);

        $this->treeStrategy->expects($this->once())
            ->method('setAllowSelectMultipleItems')
            ->with($this->identicalTo(true))
            ->willReturnSelf();
        $this->treeStrategy->expects($this->once())
            ->method('setShouldCreateLeafs')
            ->with($this->identicalTo(true))
            ->willReturnSelf();
        $this->treeStrategy->expects($this->exactly($numberOfAvailableClassifications))
            ->method('setAttachedLeafs')
            ->withConsecutive(
                 [$this->identicalTo($professions)],
                 [$this->identicalTo($industries)],
                 [$this->identicalTo($employmentTypes)]
            )
            ->willReturnSelf();
        $this->treeStrategy->expects($this->exactly($numberOfAvailableClassifications))
            ->method('setTreeRoot')
            ->willReturnSelf();
        $this->treeStrategy->expects($this->exactly($numberOfAvailableClassifications))
            ->method('hydrate')
            ->withConsecutive(
                 [$this->identicalTo($data['professions'])],
                 [$this->identicalTo($data['industries'])],
                 [$this->identicalTo($data['employmentTypes'])]
            );

        $this->categories->expects($this->exactly($numberOfAvailableClassifications))
            ->method('findOneBy')
            ->withConsecutive(
                 [$this->equalTo(['value' => $this->availableClassifications[0]])],
                 [$this->equalTo(['value' => $this->availableClassifications[1]])],
                 [$this->equalTo(['value' => $this->availableClassifications[2]])]
            )
            ->willReturn(new Category());

        $this->target->hydrate($data, $classifications);
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
     * @covers ::__construct()
     * @covers ::hydrate()
     */
    public function testHydrateEmptyAvailableClassifications()
    {
        $this->target = new ClassificationsHydrator($this->treeStrategy, $this->categories, []);

        $this->treeStrategy->expects($this->never())
            ->method('setAllowSelectMultipleItems');

        $this->target->hydrate([], new Classifications());
    }

    /**
     * @covers ::__construct()
     * @covers ::hydrate()
     * @covers ::getTreeRoot()
     */
    public function testTreeRootIsLazyloaded()
    {
        $data = [
            'professions' => [],
            'industries' => [],
            'employmentTypes' => []
        ];
        $classifications = new Classifications();

        $this->treeStrategy->method('setAllowSelectMultipleItems')
            ->willReturnSelf();
        $this->treeStrategy->method('setShouldCreateLeafs')
            ->willReturnSelf();
        $this->treeStrategy->method('setAttachedLeafs')
            ->willReturnSelf();
        $this->treeStrategy->method('setTreeRoot')
            ->willReturnSelf();

        $this->categories->expects($this->exactly(count($this->availableClassifications)))
            ->method('findOneBy')
            ->withConsecutive(
                 [$this->equalTo(['value' => $this->availableClassifications[0]])],
                 [$this->equalTo(['value' => $this->availableClassifications[1]])],
                 [$this->equalTo(['value' => $this->availableClassifications[2]])]
            )
            ->willReturn(new Category());

        for ($i = 0; $i < 3; $i++) {
            $this->target->hydrate($data, $classifications);
        }
    }
}
