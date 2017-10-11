<?php
namespace LogAnalyzer\Aggregates;

use LogAnalyzer\Entries\Entry;
use LogAnalyzer\Entries\EntryInterface;
use LogAnalyzer\TestCase;

class EntryAggregateTest extends TestCase
{
    public function testDimension()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => 'value1']),
            new Entry(['column' => 'value1']),
            new Entry(['column' => 'value2']),
        ]);

        $view = $aggregate->dimension('column');
        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getAggregate('value1')->count());
        $this->assertEquals(1, $view->getAggregate('value2')->count());
    }

    public function testDimensionByClosure()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => '<methodCall><methodName>getBlogInfo</methodName><params><param>111</param></params></methodCall>']),
            new Entry(['column' => '<methodCall><methodName>getAdView</methodName><params><param>account</param></params></methodCall>']),
            new Entry(['column' => '<methodCall><methodName>getBlogInfo</methodName><params><param>222</param></params></methodCall>']),
        ]);

        $view = $aggregate->dimension('methodName', function (EntryInterface $entry) {
            if (($xml = simplexml_load_string($entry->get('column'))) !== false) {
                return (string)$xml->methodName;
            }

            return null;
        });
        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getAggregate('getBlogInfo')->count());
        $this->assertEquals(1, $view->getAggregate('getAdView')->count());
    }

    public function testImplode()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => 'value1']),
            new Entry(['column' => 'value1']),
            new Entry(['column' => 'value2']),
        ]);

        $implode = $aggregate->sum('column');

        $this->assertEquals(['value1', 'value1', 'value2'], $implode);
    }

    public function testImplodeByClosure()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => '1']),
            new Entry(['column' => '2']),
            new Entry(['column' => '3']),
        ]);

        $implode = $aggregate->sum(function ($carry, EntryInterface $entry) {
            $carry += $entry->get('column');
            return $carry;
        });

        $this->assertEquals(6, $implode);
    }

    public function testExtract()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => 'value1', 'should_extract_key' => 1]),
            new Entry(['column' => 'value2']),
            new Entry(['column' => 'value3', 'should_extract_key' => 1]),
        ]);

        $new_aggregate = $aggregate->filter(function (EntryInterface $entry) {
            return $entry->have('should_extract_key');
        });
        $this->assertEquals(2, $new_aggregate->count());
    }
}