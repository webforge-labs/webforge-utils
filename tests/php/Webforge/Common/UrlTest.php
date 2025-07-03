<?php declare(strict_types=1);

namespace Webforge\Common;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideTestAPI
     */
    public function testAPI($sURL, $expectedURL, $scheme, $hostParts, $path, $query): void
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

    public static function provideTestApi(): array
    {
        $urls = [];
        $urls[0] = [
            'http://stechuhr.ps-webforge.com/index.php',
            null,
            'http',
            ['stechuhr', 'ps-webforge', 'com'],
            ['index.php'],
            []
        ];

        $urls[1] = [
            'http://sebastian-bergmann.de/archives/797-Global-Variables-and-PHPUnit.html',
            null,
            'http',
            ['sebastian-bergmann', 'de'],
            ['archives', '797-Global-Variables-and-PHPUnit.html'],
            []
        ];

        $urls[2] = [
            'http://tiptoi.philipp.zpintern/test',
            null,
            'http',
            ['tiptoi', 'philipp', 'zpintern'],
            ['test'],
            []
        ];

        $urls[3] = [
            'http://www.google.com/',
            null,
            'http',
            ['www', 'google', 'com'],
            [],
            []
        ];

        $urls[4] = [
            'https://www.google.com/analytics',
            null,
            'https',
            ['www', 'google', 'com'],
            ['analytics'],
            [],
        ];

        // %20 oder + in der url als whitespace?
        $urls[5] = [
            'http://www.google.de/search?q=symfony+request+handler&ie=utf-8',
            null,
            'http',
            ['www', 'google', 'de'],
            ['search'],
            ['q' => 'symfony request handler', 'ie' => 'utf-8']
        ];

        $urls[6] = [
            'http://127.0.0.1:8888/',
            null,
            'http',
            ['127', '0', '0', '1'],
            [],
            []
        ];

        $urls[7] = [
            'http://ongaku.de/?lang=en',
            null,
            'http',
            ['ongaku', 'de'],
            [],
            ['lang' => 'en'],
        ];

        $urls[8] = [
            'http://stechuhr.ps-webforge.com/index.php/path/to/heaven?lang=nix',
            null,
            'http',
            ['stechuhr', 'ps-webforge', 'com'],
            ['index.php', 'path', 'to', 'heaven'],
            ['lang' => 'nix']
        ];

        $urls[9] = [
            'http://stechuhr.ps-webforge.com/index.php/path/to/heaven/?lang=nix',
            null,
            'http',
            ['stechuhr', 'ps-webforge', 'com'],
            ['index.php', 'path', 'to', 'heaven'],
            ['lang' => 'nix']
        ];

        return $urls;
    }

    /**
     * @dataProvider provideAbsRelativeUrls
     */
    public function testAddingRelativeUrlsToAbsoluteOnesWithDirectory($absUrl, $relativeUrl, $resultUrl): void
    {
        $url = new Url($absUrl);

        $combinedUrl = $url->addRelativeUrl($relativeUrl);

        self::assertSame($combinedUrl, $url);
        self::assertEquals(
            $resultUrl,
            (string)$combinedUrl
        );
    }

    public static function provideAbsRelativeUrls(): array
    {
        $tests = [];

        $tests[] = [
            'http://www.example.com',
            '/relative/url/file.html',
            'http://www.example.com/relative/url/file.html'
        ];

        $tests[] = [
            'http://www.example.com/',
            '/relative/url/file.html',
            'http://www.example.com/relative/url/file.html'
        ];

        $tests[] = [
            'http://www.example.com/sub/dir',
            '/relative/url/file.html',
            'http://www.example.com/sub/dir/relative/url/file.html'
        ];

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

    public function testNoPartsException(): void
    {
        $this->expectException(\RuntimeException::class);
        new Url('myproject.dev1.domain');
    }

    public function testAddSubdomain(): void
    {
        $url = new Url('http://tvstt.laptop.ps-webforge.net/');
        $url->addSubDomain('test');

        self::assertEquals(
            'http://test.tvstt.laptop.ps-webforge.net/',
            (string)$url
        );
    }

    public function testPathTrailingSlashCanBeSet(): void
    {
        $url = new Url($s = 'https://www.google.com/analytics');
        $url->setPathTrailingSlash(true);

        self::assertEquals((string)$url, $s . '/');

        $url->setPathTrailingSlash(false);

        self::assertEquals((string)$url, $s);
    }
}
