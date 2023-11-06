<?php

declare(strict_types=1);

namespace Handler;

use PHPUnit\Framework\TestCase;
use Zaphyr\Session\Handler\ArrayHandler;
use Zaphyr\SessionTests\TestAssets\Time;

class ArrayHandlerTest extends TestCase
{
    /**
     * @var ArrayHandler
     */
    protected ArrayHandler $arrayHandler;

    /**
     * @var int
     */
    protected int $minutes = 60;

    protected function setUp(): void
    {
        $this->arrayHandler = new ArrayHandler($this->minutes);
    }

    protected function tearDown(): void
    {
        Time::$now = null;
    }

    /* -------------------------------------------------
     * OPEN
     * -------------------------------------------------
     */

    public function testOpenReturnsTrue(): void
    {
        self::assertTrue($this->arrayHandler->open('', ''));
    }

    /* -------------------------------------------------
     * CLOSE
     * -------------------------------------------------
     */

    public function testCloseReturnsTrue(): void
    {
        self::assertTrue($this->arrayHandler->close());
    }

    /* -------------------------------------------------
     * READ
     * -------------------------------------------------
     */

    public function testReadReturnsFalseIfItemDoesNotExists(): void
    {
        self::assertFalse($this->arrayHandler->read('foo'));
    }

    public function testReadReturnsFalseIfItemExistsButIsExpired(): void
    {
        $arrayHandler = new ArrayHandler(0);
        $arrayHandler->write('foo', 'bar');

        Time::$now = 0;

        self::assertFalse($arrayHandler->read('foo'));
    }

    public function testWriteAndReadReturnsItem(): void
    {
        $this->arrayHandler->write('foo', 'bar');

        self::assertEquals('bar', $this->arrayHandler->read('foo'));
    }

    /* -------------------------------------------------
     * WRITE
     * -------------------------------------------------
     */

    public function testWrite(): void
    {
        $this->arrayHandler->write('foo', 'bar');

        self::assertEquals('bar', $this->arrayHandler->read('foo'));

        $this->arrayHandler->write('foo', 'baz');

        self::assertEquals('baz', $this->arrayHandler->read('foo'));
    }

    /* -------------------------------------------------
     * DESTROY
     * -------------------------------------------------
     */

    public function testDestroyReturnsTrueIfStorageItemExists(): void
    {
        $this->arrayHandler->write('foo', 'bar');

        self::assertTrue($this->arrayHandler->destroy('foo'));
    }

    public function testDestroyReturnsFalseIfStorageItemDoesNotExists(): void
    {
        self::assertFalse($this->arrayHandler->destroy('foo'));
    }

    /* -------------------------------------------------
     * GARBAGE COLLECTOR
     * -------------------------------------------------
     */

    public function testGcReturnsFalseWhenNoItemsDeleted(): void
    {
        self::assertFalse($this->arrayHandler->gc(0));
    }

    public function testGcReturnsAmountOfDeletedItems(): void
    {
        $arrayHandler = new ArrayHandler(0);

        Time::$now = 0;

        $arrayHandler->write('foo', 'bar');
        $arrayHandler->write('baz', 'qux');

        self::assertEquals(2, $arrayHandler->gc(0));
    }
}
