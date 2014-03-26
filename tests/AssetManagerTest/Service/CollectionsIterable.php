<?php

namespace AssetManagerTest\Service;

class CollectionsIterable implements \IteratorAggregate
{
    public $collectionName1 = array(
        'collection 1.1',
        'collection 1.2',
        'collection 1.3',
        'collection 1.4',
    );

    public $collectionName2 = array(
        'collection 2.1',
        'collection 2.2',
        'collection 2.3',
        'collection 2.4',
    );

    public $collectionName3 = array(
        'collection 3.1',
        'collection 3.2',
        'collection 3.3',
        'collection 3.4',
    );

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
