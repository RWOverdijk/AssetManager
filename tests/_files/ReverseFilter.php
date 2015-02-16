<?php


use Assetic\Asset\AssetInterface;

class ReverseFilter implements Assetic\Filter\FilterInterface
{
    private static $executed;
    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
        self::$executed++;
        $content = $asset->getContent();
        $asset->setContent(self::$executed . strrev($content));
    }
}
