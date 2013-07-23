<?php
namespace AssetManager\Service;

use PHPUnit_Framework_TestCase;

class MimeResolverTest extends PHPUnit_Framework_TestCase
{
    public function testGetMimeType()
    {
        //Fails
        $mimeResolver = new MimeResolver;
        $this->assertEquals('text/plain', $mimeResolver->getMimeType('bacon.porn'));

        //Success
        $this->assertEquals('application/x-httpd-php', $mimeResolver->getMimeType(__FILE__));
        $this->assertEquals('application/x-httpd-php', $mimeResolver->getMimeType(strtoupper(__FILE__)));
    }

    public function testGetExtension()
    {
        $mimeResolver = new MimeResolver;

        $this->assertEquals('css', $mimeResolver->getExtension('text/css'));
        $this->assertEquals('js', $mimeResolver->getExtension('application/javascript'));
    }

    public function testGetUrlMimeType()
    {
        $mimeResolver = new MimeResolver;

        $this->assertEquals('application/javascript', $mimeResolver->getMimeType('http://foo.bar/file.js'));
    }
}
