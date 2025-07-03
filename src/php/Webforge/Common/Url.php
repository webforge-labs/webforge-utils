<?php

namespace Webforge\Common;

use RuntimeException;
use Webforge\Common\StringUtil as S;

class Url
{
    public const HTTP = 'http';
    public const HTTPS = 'https';

    /**
     * Der Cache für die URL
     *
     * (muss nicht unbedingt aktuell sein)
     * @var string
     */
    protected mixed $url = null;

    /**
     * @var string
     */
    protected $scheme = self::HTTP;

    /**
     * @var array
     */
    protected $hostParts = [];

    protected mixed $port = null;
    protected mixed $user = null;
    protected mixed $password = null;

    /**
     * @var array
     */
    protected $path = [];

    protected $pathTrailingSlash = false;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var string ohne # davor
     */
    protected mixed $fragment = null;

    public function __construct(mixed $url = null)
    {
        $this->url = $url;

        if (isset($url)) {
            $this->parseFrom($this->url);
        }
    }

    /**
     * @param string $url
     */
    public function parseFrom(mixed $url)
    {
        $url = (string) trim($url);

        $info = (object) parse_url($url);

        /* einfache Eigenschaften kopieren */
        foreach (['port','user','pass','fragment','scheme'] as $simple) {
            if (isset($info->$simple)) {
                $prop = $simple === 'pass' ? 'password' : $simple;
                $value = $info->$simple !== '' ? $info->$simple : null;

                $this->$prop = $value;
            } // nicht überschreiben wenn es nicht im array gesetzt ist
        }

        if (isset($info->host) && $info->host !== '') {
            $this->setHost($info->host);
        } else {
            throw new RuntimeException(sprintf("Kann keine URL aus '%s' parsen.", $url));
        }

        if (isset($info->query) && $info->query !== '') {
            parse_str($info->query, $this->query);
        }

        if (isset($info->path) && $info->path !== '') {
            //@TODO müssen wir hier noch url decodieren?
            $this->pathTrailingSlash = S::endsWith($info->path, '/');
            $this->path = array_filter(array_map('trim', explode('/', trim($info->path, '/'))));
        }
        return $this;
    }

    public function toString()
    {
        $url = $this->getScheme() . '://';

        // user:password@ | user@
        if (isset($this->user)) {
            $url .= $this->user;
            if (isset($this->password)) {
                $url .= $this->password . ':';
            }
            $url .= '@';
        }

        $url .= $this->getHost();

        if (isset($this->port)) {
            $url .= ':' . $this->port;
        }

        $url .= '/'; // schließt immer mit slash nach dem host ab

        if (count($this->path) > 0) {
            $url .= $this->getPathString();
        }

        if (count($this->query) > 0) {
            $url .= '?' . $this->getQueryString();
        }

        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * Gibt den Namen des Hosts zurück
     * Beispiel:
     *
     * tiptoi.philipp.zpintern
     * 127.0.0.1
     *
     * @return string ohne http:// davor und / dahinter
     */
    public function getHost()
    {
        return implode('.', $this->hostParts);
    }

    /**
     * @param string $host
     */
    public function setHost(mixed $host)
    {
        $this->hostParts = explode('.', $host);
        return $this;
    }

    /**
     * @return SimpleURL
     */
    public function getHostURL()
    {
        $url = new self();
        $url->setScheme($this->scheme);
        $url->setHostParts($this->hostParts);
        $url->setPort($this->port);
        $url->setUser($this->user);
        $url->setPassword($this->password);

        // query, fragment und part natürlich nicht

        return $url;
    }

    /**
     * Gibt den Name des Hosts nach . getrennt zurück
     *
     * @return array
     */
    public function getHostParts()
    {
        return $this->hostParts;
    }

    public function setHostParts(array $parts)
    {
        $this->hostParts = $parts;
        return $this;
    }

    /**
     * Adds an host part onto the beginning
     */
    public function addSubDomain(mixed $domain): void
    {
        array_unshift($this->hostParts, $domain);
    }

    /**
     * @return array
     */
    public function getPath()
    : array {
        return $this->path;
    }

    public function setPath(array $parts)
    {
        $this->pathTrailingSlash = false;
        $this->path = $parts;
        return $this;
    }

    public function addPathPart(mixed $string)
    {
        $this->path[] = $string;
        return $this;
    }

    /**
     * Modifies the current URL with adding an relative Url to the pathParts
     */
    public function addRelativeUrl(mixed $relativeUrl)
    {
        $this->pathTrailingSlash = S::endsWith($relativeUrl, '/');

        $parts = array_filter(explode('/', trim($relativeUrl, '/')));

        $this->path = array_merge($this->path, $parts);
        return $this;
    }

    /**
     * Gibt einen bestimten Teil des Paths zurück
     *
     * Gibt NULL zurück wenn der Part nicht gesetzt ist
     * @param int $num 1-basierend
     * @return string|null
     */
    public function getPathPart(mixed $num)
    {
        if ($num < 1) {
            throw new \InvalidArgumentException('Num ist 1-basierend');
        }
        if ($num > count($this->path)) {
            return null;
        }

        return $this->path[$num - 1];
    }

    /**
     * @return string ohne / davor und mit optionalem / dahinter (wenn pathTrailingSlash === TRUE ist)
     */
    public function getPathString()
    {
        if (count($this->path) === 0) {
            return null;
        }

        $s = implode('/', $this->path); // @TODO müssen wir hier noch url encodieren?
        if ($this->pathTrailingSlash) {
            $s .= '/';
        }

        return $s;
    }

    /**
     * @return string ohne ? davor
     */
    public function getQueryString()
    {
        return http_build_query($this->query, '', '&');
    }

    /**
     * @var array
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery(array $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHTTP()
    {
        return $this->scheme === self::HTTP;
    }

    /**
     * @return bool
     */
    public function isHTTPs()
    {
        return $this->scheme === self::HTTPS;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function setScheme(mixed $scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int port
     * @chainable
     */
    public function setPort(mixed $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string user
     * @chainable
     */
    public function setUser(mixed $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string password
     * @chainable
     */
    public function setPassword(mixed $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPathTrailingSlash()
    {
        return $this->pathTrailingSlash;
    }

    /**
     * @param mixed pathTrailingSlash
     * @chainable
     */
    public function setPathTrailingSlash(mixed $pathTrailingSlash)
    {
        $this->pathTrailingSlash = $pathTrailingSlash;
        return $this;
    }

    public function __toString()
    : string {
        try {
            return (string) $this->toString();
        } catch (\Exception $e) {
            return '[Class cannot converted to string because of ERROR: ' . $e->getMessage() . ']';
        }
    }
}
