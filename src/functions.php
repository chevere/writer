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

use Chevere\Writer\Interfaces\WritersInterface;
use ErrorException;
use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

function writers(): WritersInterface
{
    return WritersInstance::get();
}

/**
 * @throws InvalidArgumentException
 */
function streamFor(string $uri, string $mode): StreamInterface
{
    error_clear_last();
    $fopen = @fopen($uri, $mode);
    if ($fopen === false) {
        $error = error_get_last();

        throw new ErrorException(
            message: $error['message'] ?? 'An error occured',
            code: 0,
            severity: $error['type'] ?? 1
        );
    }

    return Stream::create($fopen);
}

/**
 * @throws InvalidArgumentException
 */
function streamTemp(string $content = ''): StreamInterface
{
    return Stream::create($content);
}
