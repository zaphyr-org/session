<?php

declare(strict_types=1);

namespace Zaphyr\Session;

use DateTimeInterface;
use Zaphyr\Session\Contracts\CookieInterface;
use Zaphyr\Session\Exceptions\CookieException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Cookie implements CookieInterface
{
    /**
     * @const string
     */
    public const RESTRICTION_LAX = 'lax';

    /**
     * @const string
     */
    public const RESTRICTION_STRICT = 'strict';

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $value;

    /**
     * @var int
     */
    protected int $expire;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string|null
     */
    protected string|null $domain;

    /**
     * @var string|null
     */
    protected string|null $sameSite;

    /**
     * @var bool
     */
    protected bool $secure;

    /**
     * @var bool
     */
    protected bool $httpOnly;

    /**
     * @param string                             $name
     * @param string                             $value
     * @param DateTimeInterface|int|float|string $expire
     * @param string                             $path
     * @param string|null                        $domain
     * @param bool                               $secure
     * @param bool                               $httpOnly
     * @param string|null                        $sameSite
     *
     * @throws CookieException
     */
    public function __construct(
        string $name,
        string $value,
        DateTimeInterface|int|float|string $expire = 0,
        string $path = '/',
        string|null $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        string|null $sameSite = null
    ) {
        $this->setName($name);
        $this->setExpire($expire);
        $this->setPath($path);

        $sameSite !== null ? $this->setSameSite($sameSite) : $this->sameSite = $sameSite;

        $this->value = $value;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @throws CookieException
     * @return void
     */
    protected function setName(string $name): void
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new CookieException('Cookie name "' . $name . '" contains invalid characters.');
        }

        if (empty($name)) {
            throw new CookieException('Cookie name cannot be empty.');
        }

        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpire(DateTimeInterface|int|float|string $expire): static
    {
        $this->expire = Utils::prepareExpire($expire);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAge(): int
    {
        $maxMage = $this->expire - time();

        return max($maxMage, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function isCleared(): bool
    {
        return $this->expire !== 0 && $this->expire < time();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): static
    {
        $this->path = empty($path) ? '/' : $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain(): string|null
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * {@inheritdoc}
     */
    public function setSecure(bool $secure): static
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpOnly(bool $httpOnly): static
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSameSite(): string|null
    {
        return $this->sameSite;
    }

    /**
     * {@inheritdoc}
     */
    public function setSameSite(string $sameSite): static
    {
        $this->sameSite = Utils::validateSameSiteRestrictions($sameSite);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $str = urlencode($this->name) . '=';

        if ($this->value === '') {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001) . '; Max-Age=0';
        } else {
            $str .= rawurlencode($this->value);

            if ($this->expire !== 0) {
                $str .= '; expires=' . gmdate('D, d-M-Y H:i:s T', $this->expire) . '; Max-Age=' . $this->getMaxAge();
            }
        }

        if ($this->path) {
            $str .= '; path=' . $this->path;
        }

        if ($this->domain) {
            $str .= '; domain=' . $this->domain;
        }

        if ($this->secure) {
            $str .= '; secure';
        }

        if ($this->httpOnly) {
            $str .= '; httponly';
        }

        if ($this->sameSite) {
            $str .= '; samesite=' . $this->sameSite;
        }

        return $str;
    }
}
