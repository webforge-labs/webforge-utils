<?php

namespace Webforge\Common;

class UrlTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider provideTestAPI
   */
  public function testAPI($sURL, $expectedURL, $scheme, $hostParts, $path, $query) {
    //$verbose = 'aktuelle URL: '.$sURL;
    $verbose = NULL; // geilo der der Sebastian hats schon geil gebaut
    
    if ($expectedURL === NULL)
      $expectedURL = $sURL;
      
    
    $url = new Url($sURL);

    /* String Parsing + Conversion */
    $this->assertEquals($expectedURL, $url->toString(), $verbose);
    $this->assertEquals($expectedURL, (string) $url, $verbose);
    
    /* Scheme */
    $this->assertEquals($scheme, $url->getScheme(), $verbose);
    
    if ($url->getScheme() === 'http') {
      $this->assertTrue($url->isHTTP(), $verbose);
      $this->assertEquals($url->getScheme(), Url::HTTP);
    }

    if ($url->getScheme() === 'https') {
      $this->assertTrue($url->isHTTPs(), $verbose);
      $this->assertEquals($url->getScheme(), Url::HTTPS);
    }
    
    /* host und host parts */
    $this->assertEquals($hostParts, $url->getHostParts(), $verbose);
    $this->assertEquals(implode('.',$hostParts), $url->getHost(), $verbose);
    $this->assertInstanceOf(__NAMESPACE__.'\Url',$url->getHostURL(), $verbose);
    
    /* Path */
    $this->assertEquals($path, $url->getPath(), $verbose);
    
    /* QueryString */
    $this->assertEquals($query, $url->getQuery(), $verbose);
  }

  public function provideTestApi() {
    $urls = array();
    $urls[0] = array(
      'http://stechuhr.ps-webforge.com/index.php',
      NULL,
      'http',
      array('stechuhr','ps-webforge','com'),
      array('index.php'),
      array()
    );
    
    $urls[1] = array(
      'http://sebastian-bergmann.de/archives/797-Global-Variables-and-PHPUnit.html',
      NULL,
      'http',
      array('sebastian-bergmann','de'),
      array('archives', '797-Global-Variables-and-PHPUnit.html'),
      array()
    );
    
    $urls[2] = array(
      'http://tiptoi.philipp.zpintern/test',
      NULL,
      'http',
      array('tiptoi','philipp','zpintern'),
      array('test'),
      array()
    );
    
    $urls[3] = array(
      'http://www.google.com/',
      NULL,
      'http',
      array('www','google','com'),
      array(),
      array()
    );
    
    $urls[4] = array(
      'https://www.google.com/analytics',
      NULL,
      'https',
      array('www','google','com'),
      array('analytics'),
      array(),
    );
    
    // %20 oder + in der url als whitespace?
    $urls[5] = array(
      'http://www.google.de/search?q=symfony+request+handler&ie=utf-8',
      NULL,
      'http',
      array('www','google','de'),
      array('search'),
      array('q'=>'symfony request handler', 'ie'=>'utf-8')
    );
   
    $urls[6] = array(
      'http://127.0.0.1:8888/',
      NULL,
      'http',
      array('127','0','0','1'),
      array(),
      array()
    );
    
    $urls[7] = array(
      'http://ongaku.de/?lang=en',
      NULL,
      'http',
      array('ongaku','de'),
      array(),
      array('lang'=>'en'),
    );

    $urls[8] = array(
      'http://stechuhr.ps-webforge.com/index.php/path/to/heaven?lang=nix',
      NULL,
      'http',
      array('stechuhr','ps-webforge','com'),
      array('index.php','path','to','heaven'),
      array('lang'=>'nix')
    );

    $urls[9] = array(
      'http://stechuhr.ps-webforge.com/index.php/path/to/heaven/?lang=nix',
      NULL,
      'http',
      array('stechuhr','ps-webforge','com'),
      array('index.php','path','to','heaven'),
      array('lang'=>'nix')
    );

    return $urls;
  }
  
  /**
   * @dataProvider provideAbsRelativeUrls
   */
  public function testAddingRelativeUrlsToAbsoluteOnesWithDirectory($absUrl, $relativeUrl, $resultUrl) {
    $url = new Url($absUrl);
    
    $combinedUrl = $url->addRelativeUrl($relativeUrl);
    
    $this->assertSame($combinedUrl, $url);
    $this->assertEquals(
      $resultUrl,
      (string) $combinedUrl
    );
  }
  
  public static function provideAbsRelativeUrls() {
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
  
  /**
   * @expectedException RuntimeException
   */
  public function testNoPartsException() {
    new Url('myproject.dev1.domain');
  }

  public function testAddSubdomain() {
    $url = new Url('http://tvstt.laptop.ps-webforge.net/');
    $url->addSubDomain('test');

    $this->assertEquals(
      'http://test.tvstt.laptop.ps-webforge.net/',
      (string) $url
    );
  }

  public function testPathTrailingSlashCanBeSet() {
    $url = new Url($s = 'https://www.google.com/analytics');
    $url->setPathTrailingSlash(TRUE);

    $this->assertEquals((string) $url, $s.'/');

    $url->setPathTrailingSlash(FALSE);

    $this->assertEquals((string) $url, $s);
  }
}
