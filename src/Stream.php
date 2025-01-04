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

use Psr\Http\Message\StreamInterface;

/**
 * This class has been taken from nyholm/psr7, it has been slightly modified
 * to provide a stand-alone stream writer for our use case.
 *
 * @author Michael Dowling and contributors to guzzlehttp/psr7
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Martijn van der Ven <martijn@vanderven.se>
 */
class Stream implements StreamInterface
{
    /**
     * @var array Hash of readable and writable stream types
     */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true,
            'w+' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'rb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'rt' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a+' => true,
        ],
        'write' => [
            'w' => true,
            'w+' => true,
            'rw' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'wb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a' => true,
            'a+' => true,
        ],
    ];

    /**
     * @var resource|null A resource reference
     */
    private $stream;

    /**
     * @var bool
     */
    private $seekable;

    /**
     * @var bool
     */
    private $readable;

    /**
     * @var bool
     */
    private $writable;

    /**
     * @var array|mixed|void|bool|null
     */
    private $uri;

    /**
     * @var int|null
     */
    private $size;

    /**
     * @param resource $body
     */
    public function __construct($body)
    {
        if (! \is_resource($body)) {
            throw new \InvalidArgumentException('First argument to Stream::__construct() must be resource');
        }

        $this->stream = $body;
        $meta = \stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'] && \fseek($this->stream, 0, \SEEK_CUR) === 0;
        $this->readable = isset(self::READ_WRITE_HASH['read'][$meta['mode']]);
        $this->writable = isset(self::READ_WRITE_HASH['write'][$meta['mode']]);
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Throwable $e) {
            if (\is_array($errorHandler = \set_error_handler('var_dump'))) {
                $errorHandler = $errorHandler[0] ?? null;
            }
            \restore_error_handler();

            if ($e instanceof \Error) {
                return \trigger_error((string) $e, \E_USER_ERROR);
            }

            return '';
        }
    }

    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface $body
     *
     * @throws \InvalidArgumentException
     */
    public static function create($body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (\is_string($body)) {
            if (\strlen($body) >= 200000) {
                $body = self::openZvalStream($body);
            } else {
                $resource = \fopen('php://memory', 'r+');
                \fwrite($resource, $body);
                \fseek($resource, 0);
                $body = $resource;
            }
        }

        if (! \is_resource($body)) {
            throw new \InvalidArgumentException('First argument to Stream::create() must be a string, resource or StreamInterface');
        }

        return new self($body);
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                \fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (! isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (! isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($uri = $this->getUri()) {
            \clearstatcache(true, $uri);
        }

        $stats = \fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (false === $result = @\ftell($this->stream)) {
            throw new \RuntimeException('Unable to determine stream position: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    public function eof(): bool
    {
        return ! isset($this->stream) || \feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (! $this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (\fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position "' . $offset . '" with whence ' . \var_export($whence, true));
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write($string): int
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (! $this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;

        if (false === $result = @\fwrite($this->stream, $string)) {
            throw new \RuntimeException('Unable to write to stream: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (! $this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        if (false === $result = @\fread($this->stream, $length)) {
            throw new \RuntimeException('Unable to read from stream: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    public function getContents(): string
    {
        if (! isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $exception = null;

        \set_error_handler(static function ($type, $message) use (&$exception) {
            throw $exception = new \RuntimeException('Unable to read stream contents: ' . $message);
        });

        try {
            return \stream_get_contents($this->stream);
        } catch (\Throwable $e) {
            throw $e === $exception ? $e : new \RuntimeException('Unable to read stream contents: ' . $e->getMessage(), 0, $e);
        } finally {
            \restore_error_handler();
        }
    }

    /**
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        if ($key !== null && ! \is_string($key)) {
            throw new \InvalidArgumentException('Metadata key must be a string');
        }

        if (! isset($this->stream)) {
            return $key ? null : [];
        }

        $meta = \stream_get_meta_data($this->stream);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    private function getUri()
    {
        if ($this->uri !== false) {
            $this->uri = $this->getMetadata('uri') ?? false;
        }

        return $this->uri;
    }

    private static function openZvalStream(string $body)
    {
        static $wrapper;

        $wrapper ?? \stream_wrapper_register('Chevere-Writer-Zval', $wrapper = \get_class(new class() {
            public $context;

            private $data;

            private $position = 0;

            public function stream_open(): bool
            {
                $this->data = \stream_context_get_options($this->context)['Chevere-Writer-Zval']['data'];
                \stream_context_set_option($this->context, 'Chevere-Writer-Zval', 'data', null);

                return true;
            }

            public function stream_read(int $count): string
            {
                $result = \substr($this->data, $this->position, $count);
                $this->position += \strlen($result);

                return $result;
            }

            public function stream_write(string $data): int
            {
                $this->data = \substr_replace($this->data, $data, $this->position, \strlen($data));
                $this->position += \strlen($data);

                return \strlen($data);
            }

            public function stream_tell(): int
            {
                return $this->position;
            }

            public function stream_eof(): bool
            {
                return \strlen($this->data) <= $this->position;
            }

            public function stream_stat(): array
            {
                return [
                    'mode' => 33206, // POSIX_S_IFREG | 0666
                    'nlink' => 1,
                    'rdev' => -1,
                    'size' => \strlen($this->data),
                    'blksize' => -1,
                    'blocks' => -1,
                ];
            }

            public function stream_seek(int $offset, int $whence): bool
            {
                if ($whence === \SEEK_SET && ($offset >= 0 && \strlen($this->data) >= $offset)) {
                    $this->position = $offset;
                } elseif ($whence === \SEEK_CUR && $offset >= 0) {
                    $this->position += $offset;
                } elseif ($whence === \SEEK_END && ($offset < 0 && 0 <= $offset = \strlen($this->data) + $offset)) {
                    $this->position = $offset;
                } else {
                    return false;
                }

                return true;
            }

            public function stream_set_option(): bool
            {
                return true;
            }

            public function stream_truncate(int $new_size): bool
            {
                if ($new_size) {
                    $this->data = \substr($this->data, 0, $new_size);
                    $this->position = \min($this->position, $new_size);
                } else {
                    $this->data = '';
                    $this->position = 0;
                }

                return true;
            }
        }));

        $context = \stream_context_create([
            'Chevere-Writer-Zval' => [
                'data' => $body,
            ],
        ]);

        if (! $stream = @\fopen('Chevere-Writer-Zval://', 'r+', false, $context)) {
            \stream_wrapper_register('Chevere-Writer-Zval', $wrapper);
            $stream = \fopen('Chevere-Writer-Zval://', 'r+', false, $context);
        }

        return $stream;
    }
}
