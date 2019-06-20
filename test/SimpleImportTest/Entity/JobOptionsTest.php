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

namespace SimpleImportTest\Entity;

use CoreTestUtils\TestCase\TestSetterGetterTrait;
use CoreTestUtils\TestCase\SetupTargetTrait;
use Jobs\Entity\StatusInterface;
use PHPUnit\Framework\TestCase;
use SimpleImport\Entity\JobOptions;

/**
 * @coversDefaultClass \SimpleImport\Entity\JobOptions
 */
class JobOptionsTest extends TestCase
{
    use TestSetterGetterTrait;
    use SetupTargetTrait;

    /**
     * @var JobOptions
     */
    private $target = JobOptions::class;

    /**
     * @see TestSetterGetterTrait
     *
     * @var array
     */
    private $properties = [
        [ 'initialState', [ 'default' => StatusInterface::ACTIVE, 'value' => 'someState' ]],
        [ 'recoverState', [ 'default' => StatusInterface::ACTIVE, 'value' => 'someState' ]],
    ];
}
