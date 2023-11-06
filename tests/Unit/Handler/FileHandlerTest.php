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
    protected string $tempDir;

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

    protected function setUp(): void
    {
        $this->tempFileId = '1';
        $this->tempDir = __DIR__ . '/session/';
        $this->tempFile = $this->tempDir . $this->tempFileId;

        File::createDirectory($this->tempDir);
        $file = fopen($this->tempFile, 'w');

        if ($file !== false) {
            fwrite($file, 'foo');
            fclose($file);
        }

        $this->fileHandler = new FileHandler($this->tempDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempDir);
        unset($this->fileHandler);
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

        unlink($this->tempDir . $file);
    }

    /* ------------------------------------------
     * DESTROY
     * ------------------------------------------
     */

    public function testDestroy(): void
    {
        $this->fileHandler->write($tempFileId = '2', 'baz');

        self::assertFileExists($tempFile = $this->tempDir . $tempFileId);
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
