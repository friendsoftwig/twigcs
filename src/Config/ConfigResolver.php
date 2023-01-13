<?php

namespace FriendsOfTwig\Twigcs\Config;

use FriendsOfTwig\Twigcs\Container;
use FriendsOfTwig\Twigcs\Finder\TemplateFinder;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\Ruleset\TemplateResolverAwareInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Special thanks to https://github.com/c33s/twigcs/ which this feature was inspired from.
 */
final class ConfigResolver
{
    private ?ConfigInterface $config = null;

    private ?string $configFile = null;

    /**
     * @var ConfigInterface
     */
    private $defaultConfig;

    private $path;

    /**
     * Options which can be set via Cli.
     */
    private array $options = [
        'path' => [],
        'severity' => null,
        'reporter-service-name' => 'console',
        'ruleset-class-name' => null,
        'exclude' => [],
        'config' => null,
        'twig-version' => null,
        'display' => null,
    ];

    private $finders;

    private Container $container;

    private $cwd;

    private $specificRulesets;

    public function __construct(Container $container, $cwd, array $options)
    {
        $this->container = $container;
        $this->cwd = $cwd;
        $this->defaultConfig = new Config();

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getReporter()
    {
        $reporterServiceName = "reporter.{$this->config->getReporter()}";

        if ($this->options['reporter-service-name']) {
            $reporterServiceName = "reporter.{$this->options['reporter-service-name']}";
        }

        return $this->container->get($reporterServiceName);
    }

    public function getRuleset(string $file)
    {
        $rulesetClassName = $this->getSpecificRuleset($file);

        if (null === $rulesetClassName) {
            $rulesetClassName = $this->config->getRuleset();

            if ($this->options['ruleset-class-name']) {
                $rulesetClassName = $this->options['ruleset-class-name'];
            }
        }

        if (!class_exists($rulesetClassName)) {
            throw new \InvalidArgumentException(sprintf('Ruleset class %s does not exist', $rulesetClassName));
        }

        if (!is_subclass_of($rulesetClassName, RulesetInterface::class)) {
            throw new \InvalidArgumentException('Ruleset class must implement '.RulesetInterface::class);
        }

        $instance = new $rulesetClassName($this->options['twig-version']);

        if ($instance instanceof TemplateResolverAwareInterface) {
            $instance->setTemplateResolver($this->config->getTemplateResolver());
        }

        return $instance;
    }

    public function getSeverityLimit()
    {
        switch ($this->getSeverity()) {
            case 'ignore':
                return Violation::SEVERITY_IGNORE - 1;

            case 'info':
                return Violation::SEVERITY_INFO - 1;

            case 'warning':
                return Violation::SEVERITY_WARNING - 1;

            case 'error':
                return Violation::SEVERITY_ERROR - 1;

            default:
                throw new \InvalidArgumentException('Invalid severity limit provided. Valid values are: ignore, info, warning, or error');
        }
    }

    public function getDisplay(): string
    {
        if (null !== $this->options['display']) {
            return $this->options['display'];
        }

        if (!method_exists($this->getConfig(), 'getDisplay')) {
            return ConfigInterface::DISPLAY_ALL;
        }

        return $this->getConfig()->getDisplay();
    }

    public function getConfig(): ConfigInterface
    {
        if (null === $this->config) {
            foreach ($this->computeConfigFiles() as $configFile) {
                if (!file_exists($configFile)) {
                    continue;
                }
                $config = self::separatedContextLessInclude($configFile);

                // verify that the config has an instance of Config
                if (!$config instanceof ConfigInterface) {
                    throw new InvalidConfigurationException(sprintf('The config file: "%s" does not return a "PhpCsFixer\ConfigInterface" instance. Got: "%s".', $configFile, \is_object($config) ? \get_class($config) : \gettype($config)));
                }

                $this->config = $config;
                $this->configFile = $configFile;

                break;
            }

            if (null === $this->config) {
                $this->config = $this->defaultConfig;
            }
        }

        return $this->config;
    }

    /**
     * @return string|null
     */
    public function getConfigFile()
    {
        if (null === $this->configFile) {
            $this->getConfig();
        }

        return $this->configFile;
    }

    public function getFinders()
    {
        if (null === $this->finders) {
            $this->finders = $this->resolveFinders();
        }

        return $this->finders;
    }

    /**
     * Set option that will be resolved.
     *
     * @param string $name
     */
    private function setOption($name, $value)
    {
        if (!\array_key_exists($name, $this->options)) {
            throw new InvalidConfigurationException(sprintf('Unknown option name: "%s".', $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * File/Glob specific Ruleset definition. cannot be set via cli.
     *
     * @return mixed|null
     */
    private function getSpecificRuleset(string $file)
    {
        if (null === $this->specificRulesets) {
            $this->specificRulesets = $this->getConfig()->getSpecificRulesets();
        }

        $file = $this->normalizePath($file);

        foreach ($this->specificRulesets as $pattern => $rulesetClassName) {
            $pattern = $this->normalizePath($pattern);

            if ($file === $pattern || \fnmatch($pattern, $file)) {
                return $rulesetClassName;
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    private function getSeverity()
    {
        if (null !== $this->options['severity']) {
            return $this->options['severity'];
        }

        return $this->getConfig()->getSeverity();
    }

    private function getPath()
    {
        if (null === $this->path) {
            $filesystem = new Filesystem();
            $cwd = $this->cwd;

            if (1 === \count($this->options['path']) && '-' === $this->options['path'][0]) {
                $this->path = $this->options['path'];
            } else {
                $this->path = array_map(
                    static function ($path) use ($cwd, $filesystem) {
                        $absolutePath = $filesystem->isAbsolutePath($path)
                            ? $path
                            : $cwd.\DIRECTORY_SEPARATOR.$path;

                        if (!file_exists($absolutePath)) {
                            throw new InvalidConfigurationException(sprintf('The path "%s" is not readable.', $path));
                        }

                        return $absolutePath;
                    },
                    $this->options['path']
                );
            }
        }

        return $this->path;
    }

    /**
     * Apply path on config instance.
     */
    private function resolveFinders(): array
    {
        $finders = $this->getConfig()->getFinders();

        $paths = array_filter(array_map(
            static function ($path) {
                return realpath($path);
            },
            $this->getPath()
        ));

        if (\count($paths)) {
            $pathsByType = [
                'file' => [],
                'dir' => [],
            ];

            foreach ($paths as $path) {
                if (is_file($path)) {
                    $pathsByType['file'][] = $path;
                } else {
                    $pathsByType['dir'][] = $path.\DIRECTORY_SEPARATOR;
                }
            }

            $finders[] = TemplateFinder::create()->in($pathsByType['dir'])->append($pathsByType['file'])->notPath($this->options['exclude']);
        }

        if (0 === count($finders)) {
            $finders[] = TemplateFinder::create()->in($this->cwd)->notPath($this->options['exclude']);
        }

        return $finders;
    }

    /**
     * @param iterable $iterable
     *
     * @return \Traversable
     */
    private function iterableToTraversable($iterable)
    {
        return \is_array($iterable) ? new \ArrayIterator($iterable) : $iterable;
    }

    /**
     * Compute file candidates for config file.
     *
     * @return string[]
     */
    private function computeConfigFiles()
    {
        $configFile = $this->options['config'];

        if (null !== $configFile) {
            if (false === file_exists($configFile) || false === is_readable($configFile)) {
                throw new InvalidConfigurationException(sprintf('Cannot read config file "%s".', $configFile));
            }

            return [$configFile];
        }
        $configDir = $this->cwd;

        return [
            $configDir.\DIRECTORY_SEPARATOR.'.twig_cs.php',
            $configDir.\DIRECTORY_SEPARATOR.'.twig_cs',
            $configDir.\DIRECTORY_SEPARATOR.'.twig_cs.dist.php',
            $configDir.\DIRECTORY_SEPARATOR.'.twig_cs.dist',
        ];
    }

    private static function separatedContextLessInclude($path)
    {
        return include $path;
    }
}
