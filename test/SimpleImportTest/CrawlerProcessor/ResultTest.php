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

namespace SimpleImportTest\CrawlerProcessor;

use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\Factory\ProgressBarFactory;
use Core\Console\ProgressBar;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SimpleImport\CrawlerProcessor\Result
 */
class ResultTest extends TestCase
{
    use TestSetterAndGetterTrait;

    /**
     * @var Result
     */
    private $target;

    /**
     * @var ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->progressBarFactory = $this->getMockBuilder(ProgressBarFactory::class)
            ->getMock();

        $this->target = new Result($this->progressBarFactory);
    }

    public function setterAndGetterData()
    {
        return [
            ['toProcess', '40'],
            ['inserted', [
                'value' => 0,
                'setter' => false,
            ]],
            ['updated', [
                'value' => 0,
                'setter' => false,
            ]],
            ['deleted', [
                'value' => 0,
                'setter' => false,
            ]],
            ['invalid', [
                'value' => 0,
                'setter' => false,
            ]],
            ['unchanged', [
                'value' => 0,
                'setter' => false,
            ]],
        ];
    }

    /**
     * @covers ::setToProcess()
     */
    public function testsetToProcessCreatesProgressBar()
    {
        $toProcess = 5;

        $this->progressBarFactory->expects($this->once())
            ->method('factory')
            ->with($this->identicalTo($toProcess))
            ->willReturn(new ProgressBar($toProcess, 'preventOutput'));

        $this->assertSame($this->target, $this->target->setToProcess($toProcess));
        $this->assertSame($toProcess, $this->target->getToProcess());
    }

    /**
     * @param string $property
     * @param int $increment
     * @param int $expected
     *
     * @covers ::incrementInserted()
     * @covers ::incrementUpdated()
     * @covers ::incrementDeleted()
     * @covers ::incrementInvalid()
     * @covers ::incrementUnchanged()
     * @covers ::updateProgressBar()
     * @dataProvider dataIncrements
     */
    public function testIncrements($property, $increment)
    {
        $anotherIncrement = 3;

        $progressBar = $this->getMockBuilder(ProgressBar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $progressBar->expects($this->exactly(2))
            ->method('next')
            ->withConsecutive([$increment], [$anotherIncrement]);

        $this->progressBarFactory
            ->method('factory')
            ->willReturn($progressBar);

        $this->target->setToProcess(5);
        $this->assertSame($this->target, $this->target->{"increment$property"}($increment));
        $this->assertSame($increment, $this->target->{"get$property"}());
        $this->assertSame($this->target, $this->target->{"increment$property"}($anotherIncrement));
        $this->assertSame($increment + $anotherIncrement, $this->target->{"get$property"}());
    }

    /**
     * @return array
     */
    public function dataIncrements()
    {
        return [
            ['inserted', 1],
            ['updated', 4],
            ['deleted', 2],
            ['invalid', 5],
            ['unchanged', 20],
        ];
    }
}
