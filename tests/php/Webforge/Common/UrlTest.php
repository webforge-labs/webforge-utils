<?php

namespace Webforge\Common;

use http\Exception\RuntimeException;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideTestAPI
     */
    public function testAPI($sURL, $expectedURL, $scheme, $hostParts, $path, $query)
    {
        $verbose = '';

        if ($expectedURL === null) {
            $expectedURL = $sURL;
        }


        $url = new Url($sURL);

        /* String Parsing + Conversion */
        self::assertEquals($expectedURL, $url->toString(), $verbose);
        self::assertEquals($expectedURL, (string)$url, $verbose);

        /* Scheme */
        self::assertEquals($scheme, $url->getScheme(), $verbose);

        if ($url->getScheme() === 'http') {
            self::assertTrue($url->isHTTP(), $verbose);
            self::assertEquals($url->getScheme(), Url::HTTP);
        }

        if ($url->getScheme() === 'https') {
            self::assertTrue($url->isHTTPs(), $verbose);
            self::assertEquals($url->getScheme(), Url::HTTPS);
        }

        /* host und host parts */
        self::assertEquals($hostParts, $url->getHostParts(), $verbose);
        self::assertEquals(implode('.', $hostParts), $url->getHost(), $verbose);
        self::assertInstanceOf(__NAMESPACE__ . '\Url', $url->getHostURL(), $verbose);

        /* Path */
        self::assertEquals($path, $url->getPath(), $verbose);

        /* QueryString */
        self::assertEquals($query, $url->getQuery(), $verbose);
    }

    public function provideTestApi()
    {
        $urls = array();
        $urls[0] = array(
            'http://stechuhr.ps-webforge.com/index.php',
            null,
            'http',
            array('stechuhr', 'ps-webforge', 'com'),
            array('index.php'),
            array()
        );

        $urls[1] = array(
            'http://sebastian-bergmann.de/archives/797-Global-Variables-and-PHPUnit.html',
            null,
            'http',
            array('sebastian-bergmann', 'de'),
            array('archives', '797-Global-Variables-and-PHPUnit.html'),
            array()
        );

        $urls[2] = array(
            'http://tiptoi.philipp.zpintern/test',
            null,
            'http',
            array('tiptoi', 'philipp', 'zpintern'),
            array('test'),
            array()
        );

        $urls[3] = array(
            'http://www.google.com/',
            null,
            'http',
            array('www', 'google', 'com'),
            array(),
            array()
        );

        $urls[4] = array(
            'https://www.google.com/analytics',
            null,
            'https',
            array('www', 'google', 'com'),
            array('analytics'),
            array(),
        );

        // %20 oder + in der url als whitespace?
        $urls[5] = array(
            'http://www.google.de/search?q=symfony+request+handler&ie=utf-8',
            null,
            'http',
            array('www', 'google', 'de'),
            array('search'),
            array('q' => 'symfony request handler', 'ie' => 'utf-8')
        );

        $urls[6] = array(
            'http://127.0.0.1:8888/',
            null,
            'http',
            array('127', '0', '0', '1'),
            array(),
            array()
        );

        $urls[7] = array(
            'http://ongaku.de/?lang=en',
            null,
            'http',
            array('ongaku', 'de'),
            array(),
            array('lang' => 'en'),
        );

        $urls[8] = array(
            'http://stechuhr.ps-webforge.com/index.php/path/to/heaven?lang=nix',
            null,
            'http',
            array('stechuhr', 'ps-webforge', 'com'),
            array('index.php', 'path', 'to', 'heaven'),
            array('lang' => 'nix')
        );

        $urls[9] = array(
            'http://stechuhr.ps-webforge.com/index.php/path/to/heaven/?lang=nix',
            null,
            'http',
            array('stechuhr', 'ps-webforge', 'com'),
            array('index.php', 'path', 'to', 'heaven'),
            array('lang' => 'nix')
        );

        return $urls;
    }

    /**
     * @dataProvider provideAbsRelativeUrls
     */
    public function testAddingRelativeUrlsToAbsoluteOnesWithDirectory($absUrl, $relativeUrl, $resultUrl)
    {
        $url = new Url($absUrl);

        $combinedUrl = $url->addRelativeUrl($relativeUrl);

        self::assertSame($combinedUrl, $url);
        self::assertEquals(
            $resultUrl,
            (string)$combinedUrl
        );
    }

    public static function provideAbsRelativeUrls()
    {
        $tests = array();

        $tests[] = array(
            'http://www.example.com',
            '/relative/url/file.html',
            'http://www.example.com/relative/url/file.html'
        );

        $tests[] = array(
            'http://www.example.com/',
            '/relative/url/file.html',
            'http://www.example.com/relative/url/file.html'
        );

        $tests[] = array(
            'http://www.example.com/sub/dir',
            '/relative/url/file.html',
            'http://www.example.com/sub/dir/relative/url/file.html'
        );

        /* YAGNI? */
        /*
        $tests[] = array(
          'http://www.example.com/sub/dir/some/file.html',
          '/relative/url/file.html',
          'http://www.example.com/sub/dir/some/relative/url/file.html'
        );
        */

        return $tests;
    }

    public function testNoPartsException()
    {
        $this->expectException(\RuntimeException::class);
        new Url('myproject.dev1.domain');
    }

    public function testAddSubdomain()
    {
        $url = new Url('http://tvstt.laptop.ps-webforge.net/');
        $url->addSubDomain('test');

        self::assertEquals(
            'http://test.tvstt.laptop.ps-webforge.net/',
            (string)$url
        );
    }

    public function testPathTrailingSlashCanBeSet()
    {
        $url = new Url($s = 'https://www.google.com/analytics');
        $url->setPathTrailingSlash(true);

        self::assertEquals((string)$url, $s . '/');

        $url->setPathTrailingSlash(false);

        self::assertEquals((string)$url, $s);
    }
}
