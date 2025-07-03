<?php

declare(strict_types=1);

namespace Webforge\Common;

use RuntimeException;
use Webforge\Common\StringUtil as S;

class Url implements \Stringable
{
    public const HTTP = 'http';
    public const HTTPS = 'https';

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

    public function __construct(/**
     * Der Cache für die URL
     *
     * (muss nicht unbedingt aktuell sein)
     */
    protected mixed $url = null
    )
    {
        if (isset($this->url)) {
            $this->parseFrom($this->url);
        }
    }

    /**
     * @param string $url
     */
    public function parseFrom(mixed $url): static
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

    public function toString(): string
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
    public function getHost(): string
    {
        return implode('.', $this->hostParts);
    }

    /**
     * @param string $host
     */
    public function setHost(mixed $host): static
    {
        $this->hostParts = explode('.', $host);
        return $this;
    }

    /**
     * @return SimpleURL
     */
    public function getHostURL(): self
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
     */
    public function getHostParts(): array
    {
        return $this->hostParts;
    }

    public function setHostParts(array $parts): static
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

    public function getPath(): array {
        return $this->path;
    }

    public function setPath(array $parts): static
    {
        $this->pathTrailingSlash = false;
        $this->path = $parts;
        return $this;
    }

    public function addPathPart(mixed $string): static
    {
        $this->path[] = $string;
        return $this;
    }

    /**
     * Modifies the current URL with adding an relative Url to the pathParts
     */
    public function addRelativeUrl(mixed $relativeUrl): static
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
     */
    public function getPathPart(mixed $num): ?string
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
    public function getPathString(): ?string
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
    public function getQueryString(): string
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

    public function setQuery(array $query): static
    {
        $this->query = $query;
        return $this;
    }

    public function isHTTP(): bool
    {
        return $this->scheme === self::HTTP;
    }

    public function isHTTPs(): bool
    {
        return $this->scheme === self::HTTPS;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function setScheme(mixed $scheme): static
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int port
     */
    public function setPort(mixed $port): static
    {
        $this->port = $port;
        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string user
     */
    public function setUser(mixed $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string password
     */
    public function setPassword(mixed $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPathTrailingSlash(): mixed
    {
        return $this->pathTrailingSlash;
    }

    /**
     * @param mixed pathTrailingSlash
     */
    public function setPathTrailingSlash(mixed $pathTrailingSlash): static
    {
        $this->pathTrailingSlash = $pathTrailingSlash;
        return $this;
    }

    public function __toString(): string
    {
        try {
            return (string) $this->toString();
        } catch (\Exception $e) {
            return '[Class cannot converted to string because of ERROR: ' . $e->getMessage() . ']';
        }
    }
}
