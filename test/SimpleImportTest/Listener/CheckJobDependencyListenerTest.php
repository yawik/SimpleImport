<?php

/**
 * YAWIK SimpleImport
 *
 * @copyright 2013-2021 Cross Solution
 * @license   MIT
 */

declare(strict_types=1);

namespace SimpleImportTest\Listener;

use Core\Service\EntityEraser\AbstractDependenciesListener;
use Core\Service\EntityEraser\DependencyResultEvent;
use Cross\TestUtils\TestCase\ContainerDoubleTrait;
use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Jobs\Entity\Job;
use PHPUnit\Framework\TestCase;
use SimpleImport\Entity\Item;
use SimpleImport\Listener\CheckJobDependencyListener;

/**
 * Tests for \SimpleImport\Listener\CheckJobDependencyListener
 *
 * @author Mathias Gelhausen
 * @covers \SimpleImport\Listener\CheckJobDependencyListener
 * @group SimpleImport
 * @group SimpleImport.Listener
 * @group SimpleImport.Listener.CheckJobDependencyListener
 */
class CheckJobDependencyListenerTest extends TestCase
{
    use SetupTargetTrait, TestInheritanceTrait, ContainerDoubleTrait;

    /** @var string|CheckJobDependencyListener */
    private $target = CheckJobDependencyListener::class;

    private $inheritance = [AbstractDependenciesListener::class];

    public function testReturnsNullIfNoItemIsFound()
    {
        $repository = $this->prophesize(DocumentRepository::class);
        $repository
            ->findOneBy(['documentId' => 'TestId'])
            ->willReturn(null)
            ->shouldBeCalled()
        ;
        $repositories = $this->createContainerDouble([
            Item::class => [$repository->reveal(), 1],
        ]);
        $event = new DependencyResultEvent();
        $event->setRepositories($repositories);
        $job = new Job();
        $job->setId('TestId');
        $event->setEntity($job);

        static::assertNull($this->target->__invoke($event));
    }

    public function testDoesThrowExceptionWhenCrawlerDoesNotExist()
    {
        $item = $this->prophesize(Item::class);
        $item
            ->getCrawler()
            ->willThrow(DocumentNotFoundException::class)
        ;
        $itemDouble = $item->reveal();
        $repository = $this->prophesize(DocumentRepository::class);
        $repository
            ->findOneBy(['documentId' => 'TestId'])
            ->willReturn($itemDouble)
        ;
        $repositories = $this->createContainerDouble([
            Item::class => [$repository->reveal(), 1],
        ]);
        $event = new DependencyResultEvent();
        $event->setRepositories($repositories);
        $job = new Job();
        $job->setId('TestId');
        $event->setEntity($job);
        $event->setName(DependencyResultEvent::DELETE);
        $expect = [
            'SimpleImport/CrawlerItem',
            [$itemDouble],
            null
        ];

        $result = $this->target->__invoke($event);

        static::assertEquals($expect, $result);
    }

}
