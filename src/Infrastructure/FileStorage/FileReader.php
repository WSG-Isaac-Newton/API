<?php

namespace App\Infrastructure\FileStorage;

use App\Domain\Shared\File;

class FileReader
{
    /**
     * Read a file from the given path and return a File object containing the file's stream, MIME type, and size.
     * @param string $path The path to the file to be read.
     * @throws FileNotReadableException if the file cannot be read.
     * @throws UnknownMimeTypeException if the MIME type of the file cannot be determined.
     * @return File The File object containing the file's stream, MIME type, and size.
     */
    public function read(string $path): File
    {
        if (!is_readable($path)) {
            throw new FileNotReadableException($path);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($path);
        if ($mimeType === false) {
            throw new UnknownMimeTypeException($path);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new FileNotReadableException($path);
        }

        return new File(
            filename: basename($path),
            mimeType: $mimeType,
            size: filesize($path),
            contents: $contents,
        );
    }
}
