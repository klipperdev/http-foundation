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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;

class Psr7WrappedResponse implements ResponseInterface
{
    use MessageTrait;

    public function __construct(Response $response)
    {
        $this->message = $response;
    }

    public function getResponse(): Response
    {
        if (null !== $this->stream) {
            $this->message->setContent((string) $this->stream);
        }

        return $this->message;
    }

    public function withProtocolVersion($version): self
    {
        $this->message->setProtocolVersion($version);

        return $this;
    }

    public function withBody(StreamInterface $body): self
    {
        $this->stream = $body;

        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->message->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $this->message->setStatusCode($code, $reasonPhrase);

        return $this;
    }

    public function getReasonPhrase(): string
    {
        return Response::$statusTexts[$this->getStatusCode()] ?? '';
    }
}
