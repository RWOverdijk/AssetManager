<?php
use Assetic\Contracts\Asset\AssetInterface;
class CustomFilter implements Assetic\Contracts\Filter\FilterInterface
{
    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
        $asset->setContent('called');
    }
}
