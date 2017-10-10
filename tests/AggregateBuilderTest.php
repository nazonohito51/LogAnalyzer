<?php
namespace LogAnalyzer;

class AggregateBuilderTest extends TestCase
{
    public function testCount()
    {
        $builder = new AggregateBuilder();
        $builder->add($this->getFixturePath('log.ltsv'));
        $aggregator = $builder->build();

        $this->assertEquals(8, $aggregator->count());
    }
}
