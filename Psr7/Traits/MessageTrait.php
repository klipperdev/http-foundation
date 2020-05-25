<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\HttpFoundation\Psr7\Traits;

use Klipper\Component\HttpFoundation\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait MessageTrait
{
    /**
     * @var Request|Response
     */
    private $message;

    private ?StreamInterface $stream = null;

    public function __clone()
    {
        $this->message = clone $this->message;
        $this->stream = null !== $this->stream ? clone $this->stream : null;
    }

    public function getProtocolVersion(): string
    {
        return $this->message->getProtocolVersion();
    }

    public function getHeaders(): array
    {
        return $this->message->headers->all();
    }

    public function hasHeader($name): bool
    {
        return $this->message->headers->has($name);
    }

    public function getHeader($name): array
    {
        $val = $this->message->headers->get($name);

        return null === $val ? [] : (array) $val;
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): self
    {
        $this->message->headers->set($name, $value);

        return $this;
    }

    public function withAddedHeader($name, $value): self
    {
        if (!empty($value)) {
            $this->message->headers->set($name, $value);
        }

        return $this;
    }

    public function withoutHeader($name): self
    {
        if (!$this->message->headers->has($name)) {
            return $this;
        }

        $this->message->headers->remove($name);

        return $this;
    }

    public function getBody(): StreamInterface
    {
        if (null === $this->stream) {
            $body = $this->message->getContent();
            $this->stream = Stream::create(false === $body ? '' : $body);
        }

        return $this->stream;
    }
}
