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

use PHPUnit\Framework\TestCase;

use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use Cross\TestUtils\TestCase\SetupTargetTrait;
use Jobs\Entity\StatusInterface;
use SimpleImport\Entity\JobOptions;

/**
 * @coversDefaultClass \SimpleImport\Entity\JobOptions
 */
class JobOptionsTest extends TestCase
{
    use TestSetterAndGetterTrait;
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
    private $setterAndGetter= [
        [ 'initialState', [ 'value' => StatusInterface::ACTIVE, 'setter' => false ]],
        [ 'initialState', [ 'value' => 'someState' ]],
        [ 'recoverState', [ 'value' => StatusInterface::ACTIVE, 'setter' => false ]],
        [ 'recoverState', [ 'value' => 'someState' ]],
    ];
}
