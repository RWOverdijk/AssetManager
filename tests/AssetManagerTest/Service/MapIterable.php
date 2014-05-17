<?php

namespace AssetManagerTest\Service;

class MapIterable implements \IteratorAggregate
{
    public $mapName1 = array(
        'map 1.1',
        'map 1.2',
        'map 1.3',
        'map 1.4',
    );

    public $mapName2 = array(
        'map 2.1',
        'map 2.2',
        'map 2.3',
        'map 2.4',
    );

    public $mapName3 = array(
        'map 3.1',
        'map 3.2',
        'map 3.3',
        'map 3.4',
    );

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
