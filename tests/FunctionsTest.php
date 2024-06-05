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

namespace Chevere\Tests;

use Chevere\Writer\Writers;
use Chevere\Writer\WritersInstance;
use LogicException;
use PHPUnit\Framework\TestCase;
use function Chevere\Writer\streamFor;
use function Chevere\Writer\writers;

final class FunctionsTest extends TestCase
{
    public function testWritersNoInstance(): void
    {
        $this->expectException(LogicException::class);
        writers();
    }

    public function testWritersInstance(): void
    {
        $writers = new Writers();
        $instance = new WritersInstance($writers);
        $this->assertSame($writers, writers());
        $this->assertSame($instance->get(), writers());
    }

    public function testStreamFor(): void
    {
        $this->expectNotToPerformAssertions();
        $uri = 'php://output';
        $mode = 'r+';
        streamFor($uri, $mode);
    }

    public function testStreamForError(): void
    {
        $uri = '404';
        $mode = 'r+';
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            fopen(404): Failed to open stream: No such file or directory
            PLAIN
        );
        streamFor($uri, $mode);
    }
}
