<?php

declare(strict_types=1);

namespace Zaphyr\Session\Handler;

use SessionHandlerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class ArrayHandler implements SessionHandlerInterface
{
    /**
     * @var array<string, array{expiry: int, data: mixed}>
     */
    protected array $storage = [];

    /**
     * @param int $minutes
     */
    public function __construct(protected int $minutes = 60)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $id): string|false
    {
        if (!isset($this->storage[$id])) {
            return false;
        }

        if ($this->storage[$id]['expiry'] >= time()) {
            return $this->storage[$id]['data'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        $this->storage[$id] = [
            'expiry' => time() + ($this->minutes * 60),
            'data' => $data,
        ];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        if (isset($this->storage[$id])) {
            unset($this->storage[$id]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        $deletedItems = 0;
        $expiry = time() - $max_lifetime;

        foreach ($this->storage as $id => $item) {
            if ($item['expiry'] <= $expiry) {
                unset($this->storage[$id]);
                $deletedItems++;
            }
        }
        return $deletedItems > 0 ? $deletedItems : false;
    }
}
