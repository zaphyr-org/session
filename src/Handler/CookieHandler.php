<?php

declare(strict_types=1);

namespace Zaphyr\Session\Handler;

use JsonException;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CookieHandler implements SessionHandlerInterface
{
    /**
     * @param CookieManagerInterface $cookieManager
     * @param ServerRequestInterface $request
     * @param int                    $minutes
     * @param bool                   $expireOnClose
     */
    public function __construct(
        protected CookieManagerInterface $cookieManager,
        protected ServerRequestInterface $request,
        protected int $minutes = 60,
        protected bool $expireOnClose = false
    ) {
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
        $value = $this->request->getCookieParams()[$id] ?? '';

        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (is_array($data) && isset($data['expire']) && time() <= $data['expire']) {
            return $data['data'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        $expire = time() + ($this->minutes * 60);

        try {
            $value = json_encode(['data' => $data, 'expire' => $expire], JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        $this->cookieManager->addToQueue($this->cookieManager->create($id, $value, $this->expireOnClose ? 0 : $expire));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        $this->cookieManager->addToQueue($this->cookieManager->forget($id));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        return false;
    }
}
