<?php

declare(strict_types=1);

namespace Windy\Guardian\Utils;

use Windy\Guardian\Exceptions\IOException;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_readable;
use function is_writable;

/**
 * Safe helper for reading an writing files.
 */
class IO
{
    /**
     * Read data from a file.
     *
     * @param string $path The path where to read.
     *
     * @return string The read data.
     */
    public function read(string $path): string
    {
        if (!file_exists($path)) {
            throw new IOException("File $path does not exist");
        }

        if (!is_readable($path)) {
            throw new IOException(
                "File $path exists but is not readable with the current user privileges"
            );
        }

        return file_get_contents($path);
    }

    /**
     * Write data to a file.
     *
     * @param string          $path The path where to write.
     * @param string|resource $data The data to write.
     *
     * @return false|int
     */
    public function write(string $path, $data)
    {
        $dir = dirname($path);

        if (!file_exists($dir)) {
            throw new IOException("Directory $dir does not exist and thus $path cannot be written");
        }

        if (file_exists($path) && is_dir($path)) {
            throw new IOException("$path is a directory and thus $path cannot be written");
        }

        if (file_exists($path) && !is_writable($path)) {
            throw new IOException(
                "File $path exists but is not writable with the current user privileges"
            );
        }

        if (!is_writable($dir)) {
            throw new IOException(
                "Directory $dir exists but is not writable with the current user privileges"
            );
        }

        return file_put_contents($path, $data);
    }
}
