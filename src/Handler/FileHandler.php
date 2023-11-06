<?php

declare(strict_types=1);

namespace Zaphyr\Session\Handler;

use SessionHandlerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class FileHandler implements SessionHandlerInterface
{
    /**
     * @var string
     */
    protected string $storage;

    /**
     * @param string $path
     * @param int    $minutes
     */
    public function __construct(string $path, protected int $minutes = 60)
    {
        $this->storage = rtrim($path, '\/') . '/';
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
        $path = $this->storage . $id;

        if (is_file($path) && filemtime($path) >= strtotime('-' . $this->minutes . ' minutes')) {
            return file_get_contents($this->storage . $id) ?: false;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        return file_put_contents($this->storage . $id, $data, LOCK_EX) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        $path = $this->storage . $id;

        return is_file($path) && unlink($path);
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        $files = glob($this->storage . '*');
        $expiry = time() - $max_lifetime;
        $deletedFiles = 0;

        if (is_array($files)) {
            foreach ($files as $file) {
                if (filemtime($file) <= $expiry) {
                    unlink($file);
                    $deletedFiles++;
                }
            }
        }

        return $deletedFiles > 0 ? $deletedFiles : false;
    }
}
