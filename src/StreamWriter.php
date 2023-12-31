<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Writer;

use Chevere\Writer\Interfaces\WriterInterface;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\StreamInterface;
use Throwable;

/**
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class StreamWriter implements WriterInterface
{
    public function __construct(
        private StreamInterface $stream
    ) {
        if (! $this->stream->isWritable()) {
            throw new InvalidArgumentException(
                'Stream provided is not writable'
            );
        }
    }

    public function __toString(): string
    {
        return $this->stream->__toString();
    }

    public function write(string $string): void
    {
        try {
            $this->stream->write($string);
        } catch (Throwable $e) {
            throw new LogicException(
                previous: $e,
                message: 'Unable to write provided string'
            );
        }
    }
}
