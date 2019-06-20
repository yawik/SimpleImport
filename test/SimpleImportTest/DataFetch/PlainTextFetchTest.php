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

namespace SimpleImportTest\DataFetch;

use PHPUnit\Framework\TestCase;
use SimpleImport\DataFetch\HttpFetch;
use SimpleImport\DataFetch\PlainTextFetch;

/**
 * @coversDefaultClass \SimpleImport\DataFetch\PlainTextFetch
 */
class PlainTextFetchTest extends TestCase
{

    /**
     * @var PlainTextFetch
     */
    private $target;

    /**
     * @var HttpFetch
     */
    private $httpFetch;

    /**
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->httpFetch = $this->getMockBuilder(HttpFetch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->target = new PlainTextFetch($this->httpFetch);
    }

    /**
     * @covers ::__construct()
     * @covers ::fetch()
     */
    public function testFetchTextExtraction()
    {
        $uri = 'uriString';
        $extracted = 'first part second part another part';
        $html = '
            <html>
                <body>
                    <div>first part</div>
                    <style>style rules</style>
                    <p>second <strong>part</strong></p>
                    <script>
                        this.code.shouldBeStrippedOff();
                    </script>
                    <form>
                        <button>label</button>
                    </form>
                    another <a href="uri">link</a> part
                </body>
            </html>
        ';

        $this->httpFetch->expects($this->once())
            ->method('fetch')
            ->with($uri)
            ->willReturn($html);

        $this->assertSame($extracted, $this->target->fetch($uri));
    }

    /**
     * @covers ::__construct()
     * @covers ::fetch()
     * @expectedException RuntimeException
     * @expectedExceptionMessage No content
     */
    public function testFetchNoContent()
    {
        $this->httpFetch->expects($this->once())
            ->method('fetch')
            ->willReturn('<body><a>empty</a></body>');

        $this->target->fetch('uriString');
    }
}
