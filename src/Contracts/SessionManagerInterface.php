<?php

declare(strict_types=1);

namespace Zaphyr\Session\Contracts;

use Closure;
use Zaphyr\Session\Exceptions\SessionException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface SessionManagerInterface
{
    /**
     * @param string|null $handler
     *
     * @throws SessionException if the session handler does not exist or is misconfigured
     * @return SessionInterface
     *
     */
    public function session(string $handler = null): SessionInterface;

    /**
     * @param string  $name
     * @param Closure $callback
     *
     * @throws SessionException if the session handler already exists
     * @return $this
     */
    public function addHandler(string $name, Closure $callback): static;

    /**
     * @return int
     */
    public function getSessionExpireMinutes(): int;
}
