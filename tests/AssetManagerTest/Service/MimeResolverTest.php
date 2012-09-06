<?php
namespace AssetManager\Service;

use PHPUnit_Framework_TestCase;

class MimeResolverTest extends PHPUnit_Framework_TestCase
{
    public function testGetMimeType()
    {
        //Fails
        $minetype = new MimeResolver();
        $this->assertEquals('text/plain', $minetype->getMimeType('bacon.porn'));

        //Success
        $minetype = new MimeResolver();
        $this->assertEquals('application/x-httpd-php', $minetype->getMimeType(__FILE__));
    }
}
