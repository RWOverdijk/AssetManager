<?php
namespace AssetManager\Service;

use PHPUnit\Framework\TestCase;

class MimeResolverTest extends TestCase
{
    public function testGetMimeType(): void
    {
        //Fails
        $mimeResolver = new MimeResolver;
        $this->assertEquals('text/plain', $mimeResolver->getMimeType('bacon.porn'));

        //Success
        $this->assertEquals('application/x-httpd-php', $mimeResolver->getMimeType(__FILE__));
        $this->assertEquals('application/x-httpd-php', $mimeResolver->getMimeType(strtoupper(__FILE__)));
    }

    public function testGetExtension(): void
    {
        $mimeResolver = new MimeResolver;

        $this->assertEquals('css', $mimeResolver->getExtension('text/css'));
        $this->assertEquals('js', $mimeResolver->getExtension('application/javascript'));
    }

    public function testGetUrlMimeType(): void
    {
        $mimeResolver = new MimeResolver;

        $this->assertEquals('application/javascript', $mimeResolver->getMimeType('http://foo.bar/file.js'));
    }
}
