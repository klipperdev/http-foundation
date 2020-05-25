<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\HttpFoundation\Psr7;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Psr7WrappedUploadedFile implements UploadedFileInterface
{
    private UploadedFile $uploadedFile;

    public function __construct(UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }

    public function getStream(): StreamInterface
    {
        $resource = fopen($this->uploadedFile->getPathname(), 'r');

        return Stream::create($resource);
    }

    public function moveTo($targetPath): void
    {
        $this->uploadedFile->move($targetPath);
    }

    public function getSize(): ?int
    {
        return $this->uploadedFile->getSize();
    }

    public function getError(): int
    {
        return (int) $this->uploadedFile->getError();
    }

    public function getClientFilename(): ?string
    {
        return $this->uploadedFile->getClientOriginalName();
    }

    public function getClientMediaType(): ?string
    {
        return (string) $this->uploadedFile->getClientMimeType();
    }
}
