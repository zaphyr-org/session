<?php

declare(strict_types=1);

namespace Zaphyr\Session;

use Closure;
use Doctrine\DBAL\DriverManager;
use SessionHandlerInterface;
use Throwable;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Session\Contracts\SessionManagerInterface;
use Zaphyr\Session\Exceptions\SessionException;
use Zaphyr\Session\Handler\DatabaseHandler;
use Zaphyr\Session\Handler\FileHandler;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class SessionManager implements SessionManagerInterface
{
    /**
     * @const string
     */
    public const DATABASE_HANDLER = 'database';

    /**
     * @const string
     */
    public const FILE_HANDLER = 'file';

    /**
     * @var array<string, SessionHandlerInterface>
     */
    protected array $handlers = [];

    /**
     * @var array<string, Closure>
     */
    protected array $customHandlers = [];

    /**
     * @param string                $sessionName
     * @param array<string, mixed>  $handlerConfig
     * @param int                   $sessionExpireMinutes
     * @param string                $defaultHandler
     * @param EncryptInterface|null $encryptor
     */
    public function __construct(
        protected string $sessionName,
        protected array $handlerConfig,
        protected int $sessionExpireMinutes = 60,
        protected string $defaultHandler = self::FILE_HANDLER,
        protected EncryptInterface|null $encryptor = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function session(string $handler = null): SessionInterface
    {
        $handler = $handler ?: $this->defaultHandler;

        if (!isset($this->handlers[$handler])) {
            $this->createHandler($handler);
        }

        return $this->encryptor
            ? new EncryptedSession($this->sessionName, $this->handlers[$handler], $this->encryptor)
            : new Session($this->sessionName, $this->handlers[$handler]);
    }

    /**
     * {@inheritdoc}
     */
    public function addHandler(string $name, Closure $callback): static
    {
        if (in_array($name, [self::DATABASE_HANDLER, self::FILE_HANDLER], true)) {
            throw new SessionException('Session handler with name "' . $name . '" already exists');
        }

        $this->customHandlers[$name] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionExpireMinutes(): int
    {
        return $this->sessionExpireMinutes;
    }

    /**
     * @param string $handler
     *
     * @throws SessionException if the handler is not found or misconfigured
     * @return void
     */
    protected function createHandler(string $handler): void
    {
        match ($handler) {
            self::DATABASE_HANDLER => $this->createDatabaseHandler(),
            self::FILE_HANDLER => $this->createFileHandler(),
            default => $this->createCustomHandler($handler),
        };
    }

    /**
     * @throws SessionException if the database connection is not set or invalid
     * @return void
     */
    protected function createDatabaseHandler(): void
    {
        try {
            $connection = $this->getConfig(self::DATABASE_HANDLER, 'connection');
            $driverConnection = DriverManager::getConnection($connection);
        } catch (Throwable $exception) {
            throw new SessionException('Could not connect to database', 0, $exception);
        }

        $options = $this->handlerConfig[self::DATABASE_HANDLER]['options'] ?? [];

        $this->handlers[self::DATABASE_HANDLER] = new DatabaseHandler(
            $driverConnection,
            $options,
            $this->sessionExpireMinutes
        );
    }

    /**
     * @throws SessionException if the file path is not set
     * @return void
     */
    protected function createFileHandler(): void
    {
        $path = $this->getConfig(self::FILE_HANDLER, 'path');

        $this->handlers[self::FILE_HANDLER] = new FileHandler($path, $this->sessionExpireMinutes);
    }

    /**
     * @param string $name
     *
     * @throws SessionException if the custom handler does not exist
     * @return void
     */
    protected function createCustomHandler(string $name): void
    {
        if (!isset($this->customHandlers[$name])) {
            throw new SessionException('Session handler with name "' . $name . '" does not exist');
        }

        $this->handlers[$name] = $this->customHandlers[$name]();
    }

    /**
     * @param string $handler
     * @param string $key
     *
     * @throws SessionException if the config key is not set
     * @return mixed
     */
    protected function getConfig(string $handler, string $key): mixed
    {
        if (!isset($this->handlerConfig[$handler][$key])) {
            throw new SessionException(
                'Config key "' . $key . '" for session handler "' . $handler . '" is not set'
            );
        }

        return $this->handlerConfig[$handler][$key];
    }
}