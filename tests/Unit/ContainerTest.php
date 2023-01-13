<?php

declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Tests\Unit;

use FriendsOfTwig\Twigcs;
use PHPUnit\Framework;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Container
 */
final class ContainerTest extends Framework\TestCase
{
    public function testGetThrowsRuntimeExceptionWhenServiceHasNotBeenRegisteredForKey(): void
    {
        $key = 'foo';

        $container = new Twigcs\Container();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'A service with the identifier "%s" has not been registered.',
            $key
        ));

        $container->get($key);
    }
}
