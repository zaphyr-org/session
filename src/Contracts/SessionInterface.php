<?php

declare(strict_types=1);

namespace Zaphyr\Session\Contracts;

use SessionHandlerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface SessionInterface
{
    /**
     * @return SessionHandlerInterface
     */
    public function getHandler(): SessionHandlerInterface;

    /**
     * @return bool
     */
    public function start(): bool;

    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * @return string|null
     */
    public function getToken(): string|null;

    /**
     * @param string $token
     *
     * @return void
     */
    public function setToken(string $token): void;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string|null $id
     *
     * @return void
     */
    public function setId(string|null $id): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, mixed $value = null): void;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function add(string $key, mixed $value): void;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void;

    /**
     * @param string[] $keys
     *
     * @return void
     */
    public function removeMultiple(array $keys): void;

    /**
     * @return void
     */
    public function flush(): void;

    /**
     * @param bool $destroy
     *
     * @return bool
     */
    public function migrate(bool $destroy = false): bool;

    /**
     * @param bool $destroy
     *
     * @return bool
     */
    public function regenerate(bool $destroy = false): bool;

    /**
     * @return bool
     */
    public function invalidate(): bool;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function flash(string $key, mixed $value = true): void;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function now(string $key, mixed $value): void;

    /**
     * @retun void
     */
    public function reflash(): void;

    /**
     * @param string[] $keys
     *
     * @return void
     */
    public function keep(array $keys = []): void;

    /**
     * @return void
     */
    public function clearFlashData(): void;

    /**
     * @param array<string, mixed> $values
     *
     * @return void
     */
    public function flashInput(array $values): void;

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function getOldInput(string|null $key = null, mixed $default = null): mixed;

    /**
     * @param string|null $key
     *
     * @return bool
     */
    public function hasOldInput(string|null $key = null): bool;

    /**
     * @return void
     */
    public function save(): void;
}
