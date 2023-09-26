<?php

declare(strict_types=1);

namespace Zaphyr\Session;

use SessionHandlerInterface;
use Zaphyr\Session\Contracts\Flash\FlashInterface;
use Zaphyr\Session\Contracts\Flash\InputInterface;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Flash\Flash;
use Zaphyr\Session\Flash\Input;
use Zaphyr\Utils\Arr;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Session implements SessionInterface
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var FlashInterface
     */
    protected FlashInterface $flash;

    /**
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * @var bool
     */
    protected bool $started = false;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var string
     */
    protected string $tokenKey = '_token';

    /**
     * @param string                  $name
     * @param SessionHandlerInterface $handler
     * @param string|null             $id
     * @param FlashInterface|null     $flash
     * @param InputInterface|null     $input
     */
    public function __construct(
        protected string $name,
        protected SessionHandlerInterface $handler,
        string|null $id = null,
        FlashInterface|null $flash = null,
        InputInterface|null $input = null
    ) {
        $this->setId($id);

        $this->flash = $flash ?? new Flash($this);
        $this->input = $input ?? new Input($this, $this->flash);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        $this->attributes = array_merge($this->attributes, $this->readFromHandler());

        if (!$this->has($this->tokenKey)) {
            $this->regenerateToken();
        }

        return $this->started = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @return array<string[]>
     */
    protected function readFromHandler(): array
    {
        if ($data = $this->handler->read($this->id)) {
            $data = @unserialize($this->prepareForUnserialize($data));

            if ($data !== false && is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function prepareForUnserialize(string $data): string
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken(): string|null
    {
        return $this->get($this->tokenKey);
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): void
    {
        $token = $this->isValidId($token) ? $token : $this->generateId();

        $this->set($this->tokenKey, $token);
    }

    /**
     * @return void
     */
    protected function regenerateToken(): void
    {
        $this->setToken($this->generateId());
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(string|null $id): void
    {
        $this->id = $this->isValidId($id) ? (string)$id : $this->generateId();
    }

    /**
     * @param mixed $id
     *
     * @return bool
     */
    protected function isValidId(mixed $id): bool
    {
        return is_string($id) && ctype_alnum($id) && strlen($id) === 40;
    }

    /**
     * @return string
     */
    protected function generateId(): string
    {
        $length = 40;
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value = null): void
    {
        Arr::set($this->attributes, $key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $key, mixed $value): void
    {
        $array = $this->get($key, []);
        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return Arr::has($this->attributes, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            Arr::forget($this->attributes, $key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeMultiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->remove($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->attributes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(bool $destroy = false): bool
    {
        if ($destroy) {
            $this->handler->destroy($this->id);
        }

        $this->setId($this->generateId());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate(bool $destroy = false): bool
    {
        $migrated = $this->migrate($destroy);
        $this->regenerateToken();

        return $migrated;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(): bool
    {
        $this->flush();

        return $this->migrate(true);
    }

    /**
     * {@inheritdoc}
     */
    public function flash(string $key, mixed $value = true): void
    {
        $this->flash->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function now(string $key, mixed $value): void
    {
        $this->flash->now($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reflash(): void
    {
        $this->flash->reflash();
    }

    /**
     * {@inheritdoc}
     */
    public function keep(array $keys = []): void
    {
        $this->flash->keep($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function clearFlashData(): void
    {
        $this->flash->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function flashInput(array $values): void
    {
        $this->input->set($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getOldInput(?string $key = null, mixed $default = null): mixed
    {
        return $this->input->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function hasOldInput(string $key = null): bool
    {
        return $this->input->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        $this->flash->clear();
        $this->handler->write($this->id, $this->prepareForStorage(serialize($this->attributes)));

        $this->started = false;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function prepareForStorage(string $data): string
    {
        return $data;
    }
}
