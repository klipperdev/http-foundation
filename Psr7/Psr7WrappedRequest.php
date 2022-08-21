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

use Klipper\Component\HttpFoundation\Psr7\Traits\MessageTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class Psr7WrappedRequest implements ServerRequestInterface
{
    use MessageTrait;

    public function __construct(Request $request)
    {
        $this->message = $request;
    }

    public function getRequest(): Request
    {
        return $this->message;
    }

    public function withProtocolVersion($version): self
    {
        throw new \InvalidArgumentException('Protocol version cannot be modified');
    }

    public function withBody(StreamInterface $body): self
    {
        throw new \InvalidArgumentException('Body cannot be modified');
    }

    public function getRequestTarget(): string
    {
        return $this->message->getRequestUri();
    }

    public function withRequestTarget($requestTarget): self
    {
        throw new \InvalidArgumentException('Request target cannot be modified');
    }

    public function getMethod(): string
    {
        return $this->message->getMethod();
    }

    public function withMethod($method): self
    {
        $this->message->setMethod($method);

        return $this;
    }

    public function getUri(): string
    {
        return $this->message->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): void
    {
        throw new \InvalidArgumentException('Uri cannot be modified');
    }

    public function getServerParams(): array
    {
        return $this->message->server->all();
    }

    public function getCookieParams(): array
    {
        return $this->message->cookies->all();
    }

    public function withCookieParams(array $cookies): self
    {
        foreach ($cookies as $key => $value) {
            $this->message->cookies->set($key, $value);
        }

        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->message->query->all();
    }

    public function withQueryParams(array $query): self
    {
        foreach ($query as $key => $value) {
            $this->message->query->set($key, $value);
        }

        return $this;
    }

    public function getUploadedFiles(): array
    {
        $files = [];

        /** @var UploadedFile $file */
        foreach ($this->message->files->all() as $file) {
            $files[] = new Psr7WrappedUploadedFile($file);
        }

        return $files;
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        throw new \InvalidArgumentException('Uploaded files cannot be modified');
    }

    public function getParsedBody(): array
    {
        return $this->message->request->all();
    }

    public function withParsedBody($data): self
    {
        throw new \InvalidArgumentException('Parsed body cannot be modified');
    }

    public function getAttributes(): array
    {
        return $this->message->attributes->all();
    }

    /**
     * @param mixed      $name
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->message->attributes->get($name, $default);
    }

    public function withAttribute($name, $value): self
    {
        $this->message->attributes->set($name, $value);

        return $this;
    }

    public function withoutAttribute($name): self
    {
        if (!$this->message->attributes->has($name)) {
            return $this;
        }

        $this->message->attributes->remove($name);

        return $this;
    }
}
