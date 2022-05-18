<?php

declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Tests\Config;

use FriendsOfTwig\Twigcs;
use PHPUnit\Framework;
use Symfony\Component\Filesystem;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Config\ConfigResolver
 */
final class ConfigResolverTest extends Framework\TestCase
{
    protected function setUp(): void
    {
        self::fileSystem()->mkdir(self::temporaryDirectory());
    }

    protected function tearDown(): void
    {
        self::fileSystem()->remove(self::temporaryDirectory());
    }

    public function testGetConfigFileReturnsNullWhenConfigOptionHasNotBeenSpecifiedAndDefaultConfigFilesDoNotExist(): void
    {
        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            []
        );

        self::assertNull($configResolver->getConfigFile());
    }

    /**
     * @dataProvider provideDefaultConfigFileName
     */
    public function testGetConfigFileThrowsInvalidConfigurationExceptionWhenConfigOptionHasNotBeenSpecifiedAndDefaultConfigFileExistsButDoesNotReturnConfig(string $defaultConfigFileName): void
    {
        $defaultConfigFilePath = sprintf(
            '%s/%s',
            self::temporaryDirectory(),
            $defaultConfigFileName
        );

        self::fileSystem()->dumpFile(
            $defaultConfigFilePath,
            ''
        );

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            []
        );

        $this->expectException(Twigcs\Config\InvalidConfigurationException::class);

        $configResolver->getConfigFile();
    }

    /**
     * @dataProvider provideDefaultConfigFileName
     */
    public function testGetConfigFileReturnsConfigFileWhenConfigOptionHasNotBeenSpecifiedAndDefaultConfigFileExistAndReturnsConfig(string $defaultConfigFileName): void
    {
        $defaultConfigFilePath = sprintf(
            '%s/%s',
            self::temporaryDirectory(),
            $defaultConfigFileName
        );

        $defaultConfigFileContent = <<<PHP
<?php

return FriendsOfTwig\Twigcs\Config\Config::create();
PHP;

        self::fileSystem()->dumpFile(
            $defaultConfigFilePath,
            $defaultConfigFileContent
        );

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            []
        );

        self::assertSame($defaultConfigFilePath, $configResolver->getConfigFile());
    }

    /**
     * @dataProvider provideDefaultConfigFileNameAndAvailableDefaultConfigFileNames
     *
     * @param array<int, string> $availableDefaultConfigFileNames
     */
    public function testGetConfigFileReturnsConfigFileWhenConfigOptionHasNotBeenSpecifiedAndMultipleDefaultConfigFilesExistsAndReturnConfigs(
        string $defaultConfigFileName,
        array $availableDefaultConfigFileNames
    ): void {
        $defaultConfigFilePath = sprintf(
            '%s/%s',
            self::temporaryDirectory(),
            $defaultConfigFileName
        );

        $defaultConfigFileContent = <<<PHP
<?php

return FriendsOfTwig\Twigcs\Config\Config::create();
PHP;

        foreach ($availableDefaultConfigFileNames as $availableDefaultConfigFileName) {
            $availableDefaultConfigFilePath = sprintf(
                '%s/%s',
                self::temporaryDirectory(),
                $defaultConfigFileName
            );

            self::fileSystem()->dumpFile(
                $availableDefaultConfigFilePath,
                $defaultConfigFileContent
            );
        }

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            []
        );

        self::assertSame($defaultConfigFilePath, $configResolver->getConfigFile());
    }

    /**
     * @return array<int, array{0: string, 1: array<int, string>}>
     */
    public function provideDefaultConfigFileNameAndAvailableDefaultConfigFileNames(): array
    {
        return [
            [
                '.twig_cs',
                [
                    '.twigcs',
                    '.twigcs.dist',
                ],
            ],
        ];
    }

    public function testGetConfigFileThrowsInvalidConfigurationExceptionWhenConfigOptionHasBeenSpecifiedAndConfigFileDoesNotExist(): void
    {
        $configFilePath = sprintf(
            '%s/foo',
            self::temporaryDirectory(),
        );

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            [
                'config' => $configFilePath,
            ]
        );

        $this->expectException(Twigcs\Config\InvalidConfigurationException::class);

        $configResolver->getConfigFile();
    }

    public function testGetConfigFileThrowsInvalidConfigurationExceptionWhenConfigOptionHasBeenSpecifiedAndConfigFileExistsButDoesNotReturnConfig(): void
    {
        $configFilePath = sprintf(
            '%s/foo',
            self::temporaryDirectory(),
        );

        self::fileSystem()->dumpFile(
            $configFilePath,
            ''
        );

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            [
                'config' => $configFilePath,
            ]
        );

        $this->expectException(Twigcs\Config\InvalidConfigurationException::class);

        $configResolver->getConfigFile();
    }

    public function testGetConfigFileReturnsConfigFileWhenConfigOptionHasBeenSpecifiedAndConfigFileExistsAndReturnsConfig(): void
    {
        $configFilePath = sprintf(
            '%s/foo',
            self::temporaryDirectory(),
        );

        $configFileContent = <<<PHP
<?php

return FriendsOfTwig\Twigcs\Config\Config::create();
PHP;

        self::fileSystem()->dumpFile(
            $configFilePath,
            $configFileContent
        );

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            [
                'config' => $configFilePath,
            ]
        );

        self::assertSame($configFilePath, $configResolver->getConfigFile());
    }

    /**
     * @dataProvider provideDefaultConfigFileName
     */
    public function testGetConfigFileReturnsConfigFileWhenConfigOptionHasBeenSpecifiedAndBothConfigFileAndDefaultConfigFileExistAndReturnConfigs(string $defaultConfigFileName): void
    {
        $configFilePath = sprintf(
            '%s/foo',
            self::temporaryDirectory(),
        );

        $defaultConfigFilePath = sprintf(
            '%s/%s',
            self::temporaryDirectory(),
            $defaultConfigFileName,
        );

        $configFileContent = <<<PHP
<?php

return FriendsOfTwig\Twigcs\Config\Config::create();
PHP;

        self::fileSystem()->dumpFile(
            $configFilePath,
            $configFileContent
        );

        self::fileSystem()->dumpFile(
            $defaultConfigFilePath,
            $configFileContent
        );

        $configResolver = new Twigcs\Config\ConfigResolver(
            $this->createStub(Twigcs\Container::class),
            self::temporaryDirectory(),
            [
                'config' => $configFilePath,
            ]
        );

        self::assertSame($configFilePath, $configResolver->getConfigFile());
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public function provideDefaultConfigFileName(): \Generator
    {
        $defaultConfigFileNames = [
            '.twig_cs',
            '.twig_cs.dist',
        ];

        foreach ($defaultConfigFileNames as $defaultConfigFileName) {
            yield $defaultConfigFileName => [
                $defaultConfigFileName,
            ];
        }
    }

    private static function fileSystem(): Filesystem\Filesystem
    {
        return new Filesystem\Filesystem();
    }

    private static function temporaryDirectory(): string
    {
        return __DIR__.'/../../.build/test';
    }
}
