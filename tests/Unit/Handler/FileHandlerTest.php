<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Zaphyr\Session\Handler\FileHandler;
use Zaphyr\Utils\File;

class FileHandlerTest extends TestCase
{
    /**
     * @var string
     */
    protected static string $tempDir = __DIR__ . '/session/';

    /**
     * @var string
     */
    protected string $tempFile;

    /**
     * @var string
     */
    protected string $tempFileId;

    /**
     * @var FileHandler
     */
    protected FileHandler $fileHandler;

    public static function tearDownAfterClass(): void
    {
        File::deleteDirectory(static::$tempDir);
    }

    protected function setUp(): void
    {
        $this->tempFileId = '1';
        $this->tempFile = static::$tempDir . $this->tempFileId;
        $this->fileHandler = new FileHandler(static::$tempDir);
    }

    protected function tearDown(): void
    {
        unset($this->tempFileId, $this->tempFile, $this->fileHandler);
    }

    /* ------------------------------------------
     * OPEN
     * ------------------------------------------
     */

    public function testOpenReturnsTrue(): void
    {
        self::assertTrue($this->fileHandler->open(__DIR__, 'foo'));
    }

    /* ------------------------------------------
     * CLOSE
     * ------------------------------------------
     */

    public function testCloseReturnsTrue(): void
    {
        self::assertTrue($this->fileHandler->close());
    }

    /* ------------------------------------------
     * READ
     * ------------------------------------------
     */

    public function testReadReturnsFileContent(): void
    {
        $this->fileHandler->write($this->tempFileId, 'foo');

        self::assertEquals('foo', $this->fileHandler->read($this->tempFileId));
    }

    public function testReadReturnsEmptyStringWhenFileDoesNotExists(): void
    {
        self::assertEquals('', $this->fileHandler->read('nope'));
    }

    /* ------------------------------------------
     * WRITE
     * ------------------------------------------
     */

    public function testWriteToExistingFile(): void
    {
        self::assertTrue($this->fileHandler->write($this->tempFileId, $expected = 'bar'));
        self::assertEquals($expected, $this->fileHandler->read($this->tempFileId));
    }

    public function testWriteCreatesNewFileWhenNotExists(): void
    {
        self::assertTrue($this->fileHandler->write($file = 'create', $expected = 'bar'));
        self::assertEquals($expected, $this->fileHandler->read($file));

        unlink(static::$tempDir . $file);
    }

    /* ------------------------------------------
     * DESTROY
     * ------------------------------------------
     */

    public function testDestroy(): void
    {
        $this->fileHandler->write($tempFileId = '2', 'baz');

        self::assertFileExists($tempFile = static::$tempDir . $tempFileId);
        self::assertTrue($this->fileHandler->destroy($tempFileId));
        self::assertFileDoesNotExist($tempFile);
    }

    public function testDestroyReturnsFalseWhenFileDoesNotExists(): void
    {
        self::assertFalse($this->fileHandler->destroy('999'));
    }

    /* ------------------------------------------
     * GARBAGE COLLECTOR
     * ------------------------------------------
     */

    public function testGcReturnsFalseWhenNoItemsDeleted(): void
    {
        self::assertFalse($this->fileHandler->gc(60));
    }

    public function testGcReturnsAmountOfDeletedItems(): void
    {
        self::assertEquals(1, $this->fileHandler->gc(0));
    }
}
