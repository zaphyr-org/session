<?php

declare(strict_types=1);

namespace Zaphyr\Session\Contracts;

use DateTimeInterface;
use Zaphyr\Session\Exceptions\CookieException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface CookieInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @return int
     */
    public function getExpire(): int;

    /**
     * @param DateTimeInterface|int|float|string $expire
     *
     * @throws CookieException on invalid expire value
     * @return $this
     */
    public function setExpire(DateTimeInterface|int|float|string $expire): static;

    /**
     * @return int
     */
    public function getMaxAge(): int;

    /**
     * @return bool
     */
    public function isCleared(): bool;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): static;

    /**
     * @return string|null
     */
    public function getDomain(): string|null;

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain(string $domain): static;

    /**
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * @param bool $secure
     *
     * @return $this
     */
    public function setSecure(bool $secure): static;

    /**
     * @return bool
     */
    public function isHttpOnly(): bool;

    /**
     * @param bool $httpOnly
     *
     * @return $this
     */
    public function setHttpOnly(bool $httpOnly): static;

    /**
     * @return string|null
     */
    public function getSameSite(): string|null;

    /**
     * @param string $sameSite
     *
     * @throws CookieException on invalid same site value
     * @return $this
     */
    public function setSameSite(string $sameSite): static;

    /**
     * @return string
     */
    public function __toString(): string;
}
