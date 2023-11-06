<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests\Unit;

use PHPUnit\Framework\TestCase;
use Zaphyr\Encrypt\Encrypt;
use Zaphyr\Session\EncryptedSession;
use Zaphyr\Session\Exceptions\SessionException;
use Zaphyr\Session\Handler\ArrayHandler;
use Zaphyr\Session\Handler\DatabaseHandler;
use Zaphyr\Session\Handler\FileHandler;
use Zaphyr\Session\SessionManager;

class SessionManagerTest extends TestCase
{
    /**
     * @var SessionManager
     */
    protected SessionManager $sessionManager;

    /**
     * @var string
     */
    protected string $sessionName = 'zaphyr_session';

    protected function setUp(): void
    {
        $this->sessionManager = new SessionManager(
            $this->sessionName,
            [
                'file' => [
                    'path' => __DIR__,
                ],
                'database' => [
                    'connection' => [
                        'driver' => 'pdo_sqlite',
                        'path' => __DIR__ . '/database.sqlite',
                    ],
                ],
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->sessionManager);
    }

    /* -------------------------------------------------
     * CONSTRUCTOR
     * -------------------------------------------------
     */

    public function testConstructor(): void
    {
        $sessionManager = new SessionManager(
            $this->sessionName,
            [
                'file' => [
                    'path' => __DIR__,
                ],
            ],
            sessionExpireMinutes: $expireMinutes = 120
        );

        self::assertSame($this->sessionName, $sessionManager->session()->getName());
        self::assertSame($expireMinutes, $sessionManager->getSessionExpireMinutes());
    }

    /* -------------------------------------------------
     * SESSION
     * -------------------------------------------------
     */

    public function testSessionReturnsDefaultHandler(): void
    {
        self::assertInstanceOf(FileHandler::class, $this->sessionManager->session()->getHandler());
    }

    public function testSessionReturnsArrayHandler(): void
    {
        self::assertInstanceOf(
            ArrayHandler::class,
            $this->sessionManager->session('array')->getHandler()
        );
    }

    public function testSessionReturnsFileHandler(): void
    {
        self::assertInstanceOf(
            FileHandler::class,
            $this->sessionManager->session('file')->getHandler()
        );
    }

    public function testSessionReturnsDatabaseHandler(): void
    {
        self::assertInstanceOf(
            DatabaseHandler::class,
            $this->sessionManager->session('database')->getHandler()
        );
    }

    public function testSessionWithDifferentDefaultHandler(): void
    {
        $sessionManager = new SessionManager(
            $this->sessionName,
            [
                'database' => [
                    'connection' => [
                        'driver' => 'pdo_sqlite',
                        'path' => __DIR__ . '/database.sqlite',
                    ],
                ],
            ],
            defaultHandler: SessionManager::DATABASE_HANDLER
        );

        self::assertInstanceOf(DatabaseHandler::class, $sessionManager->session()->getHandler());
    }

    public function testSessionIsEncrypted(): void
    {
        $sessionManager = new SessionManager(
            $this->sessionName,
            [
                'file' => [
                    'path' => __DIR__,
                ],
            ],
            encryptor: new Encrypt('OOQPAgC4tA7NanCiVCa1QN5BiRDpdQZR', 'AES-256-CBC')
        );

        self::assertInstanceOf(EncryptedSession::class, $sessionManager->session());
    }

    public function testSessionReturnsSameInstanceWhenAlreadyCreated(): void
    {
        $sessionManager = new SessionManager(
            $this->sessionName,
            [
                'file' => [
                    'path' => __DIR__,
                ],
            ]
        );

        $session = $sessionManager->session();

        self::assertSame($session, $sessionManager->session());
    }

    public function testSessionThrowsExceptionOnMissingFileHandlerConfiguration(): void
    {
        $this->expectException(SessionException::class);

        (new SessionManager($this->sessionName, []))->session('file');
    }

    public function testSessionThrowsExceptionOnMissingDatabaseHandlerConfiguration(): void
    {
        $this->expectException(SessionException::class);

        (new SessionManager($this->sessionName, []))->session('database');
    }

    public function testSessionThrowsExceptionOnMisconfiguredDatabaseConnection(): void
    {
        $this->expectException(SessionException::class);

        (new SessionManager(
            $this->sessionName,
            [
                'database' => [
                    'connection' => [],
                ],
            ],
            defaultHandler: SessionManager::DATABASE_HANDLER
        ))->session('database');
    }

    public function testSessionThrowsExceptionWhenHandlerNotRegistered(): void
    {
        $this->expectException(SessionException::class);

        $this->sessionManager->session('custom_handler');
    }

    /* -------------------------------------------------
     * ADD HANDLER
     * -------------------------------------------------
     */

    public function testAddHandler(): void
    {
        $this->sessionManager->addHandler('custom_handler', function () {
            return new FileHandler(__DIR__);
        });

        self::assertInstanceOf(
            FileHandler::class,
            $this->sessionManager->session('custom_handler')->getHandler()
        );
    }

    public function testAddHandlerWithForce(): void
    {
        $this->sessionManager->addHandler('custom_handler', function () {
            return new ArrayHandler(0);
        });

        $this->sessionManager->addHandler('custom_handler', function () {
            return new FileHandler(__DIR__);
        }, true);

        self::assertInstanceOf(
            FileHandler::class,
            $this->sessionManager->session('file')->getHandler()
        );
    }

    public function testAddHandlerThrowsExceptionIfHandlerNameIsAlreadyRegistered(): void
    {
        $this->expectException(SessionException::class);

        $this->sessionManager->addHandler('file', function () {
            return new FileHandler(__DIR__);
        });
    }

    public function testAddHandlerThrowsExceptionIfCustomHandlerNameIsAlreadyRegistered(): void
    {
        $this->expectException(SessionException::class);

        $this->sessionManager
            ->addHandler('custom_handler', function () {
                return new FileHandler(__DIR__);
            })
            ->addHandler('custom_handler', function () {
                return new FileHandler(__DIR__);
            });
    }

    public function testAddHandlerForceDoesNotWorkOnDefaultHandlers(): void
    {
        $this->expectException(SessionException::class);

        $this->sessionManager->addHandler('file', function () {
            return new ArrayHandler(0);
        }, true);
    }
}
