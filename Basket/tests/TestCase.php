<?php
/**
 * Created by PhpStorm.
 * User: samoilenko
 * Date: 2018-12-07
 * Time: 23:38
 */
declare(strict_types=1);

namespace App\BasketTest;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;

class TestCase extends PHPUnitTestCase
{
    /**
     * @var AggregateTranslator
     */
    private $aggregateTranslator;

    protected function popRecordedEvents(AggregateRoot $aggregateRoot): array
    {
        return $this->getAggregateTranslator()->extractPendingStreamEvents($aggregateRoot);
    }
    /**
     * @return object
     */
    protected function reconstituteAggregateFromHistory(string $aggregateRootClass, array $events)
    {
        return $this->getAggregateTranslator()->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass($aggregateRootClass),
            new \ArrayIterator($events)
        );
    }

    private function getAggregateTranslator(): AggregateTranslator
    {
        if (null === $this->aggregateTranslator) {
            $this->aggregateTranslator = new AggregateTranslator();
        }
        return $this->aggregateTranslator;
    }
}
