<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Windy\Guardian\Exceptions\IOException;
use Windy\Guardian\Tests\GuardianTestCase;
use Windy\Guardian\Utils\IO;
use function chmod;
use function dirname;
use function file_exists;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function storage_path;
use const DIRECTORY_SEPARATOR;

/**
 * @coversDefaultClass \Windy\Guardian\Utils\IO
 */
class IOTest extends GuardianTestCase
{
    /** @var IO $instance The test instance. */
    private $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->cleanup();

        $this->beforeApplicationDestroyed(function (): void {
            $this->cleanup();
        });

        $this->instance = $this->app->make(IO::class);
    }

    /**
     * Get the test path.
     *
     * @param string $path The child path, if any.
     *
     * @return string The absolute test path.
     */
    private function path(string $path = ''): string
    {
        return storage_path('io-test' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }

    /**
     * Cleanup the test directory.
     */
    private function cleanup(): void
    {
        if (!file_exists($this->path())) {
            mkdir($this->path());

            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($this->path());
    }

    /**
     * @covers ::read
     */
    public function testReadOk(): void
    {
        $path = $this->path('read-ok.txt');
        file_put_contents($path, 'foo');
        $this->assertEquals('foo', $this->instance->read($path));
    }

    /**
     * @covers ::read
     */
    public function testReadNotFound(): void
    {
        $path = $this->path('read-not-found.txt');
        $this->expectException(IOException::class);
        $this->expectErrorMessage("File $path does not exist");
        $this->instance->read($path);
    }

    /**
     * @covers ::read
     */
    public function testReadNotReadable(): void
    {
        $path = $this->path('read-not-readable.txt');
        file_put_contents($path, 'foo');
        chmod($path, 0300);
        $this->expectException(IOException::class);
        $this->expectErrorMessageMatches('/File .* is not readable/');
        $this->instance->read($path);
        chmod($path, 0600);
    }

    /**
     * @covers ::write
     */
    public function testWriteOk(): void
    {
        $path = $this->path('write-ok.txt');
        $this->instance->write($path, 'foo');
        $this->assertFileExists($path);
        $this->assertStringEqualsFile($path, 'foo');
    }

    /**
     * @covers ::write
     */
    public function testWriteNotFound(): void
    {
        $path = $this->path('not-found/write-not-found.txt');

        $this->expectException(IOException::class);
        $this->expectErrorMessageMatches('/Directory .* does not exist/');

        $this->instance->write($path, 'foo');
    }

    /**
     * @covers ::write
     */
    public function testWriteNotDirectory(): void
    {
        $path = $this->path('not-directory');
        mkdir($path);

        $this->expectException(IOException::class);
        $this->expectErrorMessageMatches('/is a directory/');

        $this->instance->write($path, 'foo');
    }

    /**
     * @covers ::write
     */
    public function testWriteFileNotWritable(): void
    {
        $path = $this->path('write-not-writable.txt');
        file_put_contents($path, 'foo');
        chmod($path, 0500);

        $this->expectException(IOException::class);
        $this->expectErrorMessageMatches('/File .* is not writable/');

        $this->instance->write($path, 'foo');
        chmod($path, 0600);
    }

    /**
     * @covers ::write
     */
    public function testWriteDirectoryNotWritable(): void
    {
        $path = $this->path('not-writable/write-not-writable.txt');
        mkdir(dirname($path));
        chmod(dirname($path), 0500);

        $this->expectException(IOException::class);
        $this->expectErrorMessageMatches('/Directory .* is not writable/');

        $this->instance->write($path, 'foo');
        chmod(dirname($path), 0600);
    }
}
