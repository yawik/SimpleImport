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

namespace SimpleImportTest\Validator;

use SimpleImport\Validator\CrawlerOptions;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use Zend\Validator\AbstractValidator;
use SimpleImport\Entity\Crawler;
use Jobs\Entity\Status;

/**
 * @coversDefaultClass \SimpleImport\Validator\CrawlerOptions
 */
class CrawlerOptionsTest extends \PHPUnit_Framework_TestCase
{

    use TestInheritanceTrait;

    /**
     * @var CrawlerOptions
     */
    private $target = CrawlerOptions::class;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [AbstractValidator::class];

    /**
     * @covers ::isValid()
     * @expectedException LogicException
     * @expectedExceptionMessage There is no type key in the context
     */
    public function testIsValidWithInvalidContext()
    {
        $this->target->isValid('some value');
    }

    /**
     * @covers ::isValid()
     */
    public function testIsValidWithUnknownType()
    {
        $isValid = $this->target->isValid('some value', ['type' => 'unkn0wn']);
        $this->assertTrue($isValid);
    }

    /**
     * @covers ::isValid()
     */
    public function testIsValidWithInvalidJobState()
    {
        $isValid = $this->target->isValid(['initialState' => 'inv4lid'], ['type' => Crawler::TYPE_JOB]);
        $this->assertFalse($isValid);
    }

    /**
     * @covers ::isValid()
     */
    public function testIsValidWithValidJobState()
    {
        $isValid = $this->target->isValid(['initialState' => Status::PUBLISH], ['type' => Crawler::TYPE_JOB]);
        $this->assertTrue($isValid);
    }
}
