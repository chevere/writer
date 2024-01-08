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
use LogicException;

final class WritersInstance
{
    private static ?WritersInterface $instance;

    public function __construct(WritersInterface $writers)
    {
        self::$instance = $writers;
    }

    public function __destruct()
    {
        self::$instance = null;
    }

    public static function get(): WritersInterface
    {
        if (! isset(self::$instance)) {
            throw new LogicException(
                'No writers instance present'
            );
        }

        return self::$instance;
    }
}
