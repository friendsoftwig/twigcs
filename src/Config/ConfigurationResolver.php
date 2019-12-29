<?php


namespace FriendsOfTwig\Twigcs\Config;

use Pimple\Container;
use Symfony\Component\Filesystem\Filesystem;
use FriendsOfTwig\Twigcs\Finder;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;
use function fnmatch;

final class ConfigurationResolver
{
    const PATH_MODE_OVERRIDE = 'override';
    const PATH_MODE_INTERSECTION = 'intersection';

    /**
     * @var null|ConfigInterface
     */
    private $config;

    /**
     * @var null|string
     */
    private $configFile;

    /**
     * @var ConfigInterface
     */
    private $defaultConfig;

    private $path;

    /**
     * Options which can be set via Cli
     *
     * @var array
     */
    private $options = [
        'path' => [],
        'path-mode' => self::PATH_MODE_OVERRIDE,
        'severity' => null,
        'reporter-service-name' => null,
        'ruleset-class-name' => null,
        'exclude' => [],
        'config' => null,
    ];

    private $finder;

    /**
     * @var Container
     */
    private $container;
    /**
     * @var bool
     */
    private $configFinderIsOverridden;
    private $cwd;
    private $specificRulesets;

    /**
     * ConfigurationResolver constructor.
     * @param Container $container
     * @param $cwd
     * @param array $options
     */
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
        if ($this->options['ruleset-class-name']) {
            $reporterServiceName = "reporter.{$this->options['reporter-service-name']}";
        }

        return $this->container[$reporterServiceName];
    }

    /**
     * Set option that will be resolved.
     *
     * @param string $name
     * @param mixed  $value
     */
    private function setOption($name, $value)
    {
        if (!\array_key_exists($name, $this->options)) {
            throw new InvalidConfigurationException(sprintf('Unknown option name: "%s".', $name));
        }

        $this->options[$name] = $value;
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

        return new $rulesetClassName();
    }

    /**
     * File/Glob specific Ruleset definition. cannot be set via cli
     *
     * @param string $file
     * @return mixed|null
     */
    private function getSpecificRuleset(string $file)
    {
        if (null === $this->specificRulesets) {
            $this->specificRulesets = $this->getConfig()->getSpecificRulesets();
        }

        $file = $this->normalizePath($file);
        foreach ($this->specificRulesets as $pattern => $rulesetClassName) {
            // TODO: fnmatch allow setting flags https://www.php.net/manual/en/function.fnmatch.php
            $pattern = $this->normalizePath($pattern);
            if ($file === $pattern || fnmatch($pattern, $file)) {

                return $rulesetClassName;
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\','/',$path);
    }

    private function getSeverity()
    {
        if (null !== $this->options['severity']) {
            return $this->options['severity'];
        }

        return $this->getConfig()->getSeverity();
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
                throw new \InvalidArgumentException('Invalid severity limit provided.');
        }
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
     * @return null|string
     */
    public function getConfigFile()
    {
        if (null === $this->configFile) {
            $this->getConfig();
        }

        return $this->configFile;
    }


    public function getFinder()
    {
        if (null === $this->finder) {
            $this->finder = $this->resolveFinder();
        }

        return $this->finder;
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
                            throw new InvalidConfigurationException(sprintf(
                                'The path "%s" is not readable.',
                                $path
                            ));
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
    private function resolveFinder()
    {
        $this->configFinderIsOverridden = false;

        $modes = [self::PATH_MODE_OVERRIDE, self::PATH_MODE_INTERSECTION];

        if (!\in_array(
            $this->options['path-mode'],
            $modes,
            true
        )) {
            throw new InvalidConfigurationException(sprintf(
                'The path-mode "%s" is not defined, supported are "%s".',
                $this->options['path-mode'],
                implode('", "', $modes)
            ));
        }

        $isIntersectionPathMode = self::PATH_MODE_INTERSECTION === $this->options['path-mode'];

        $paths = array_filter(array_map(
            static function ($path) {
                return realpath($path);
            },
            $this->getPath()
        ));

        if (!\count($paths)) {
            if ($isIntersectionPathMode) {
                return new \ArrayIterator([]);
            }

            return $this->iterableToTraversable($this->getConfig()->getFinder());
        }

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

        $nestedFinder = null;
        $currentFinder = $this->iterableToTraversable($this->getConfig()->getFinder());

        try {
            $nestedFinder = $currentFinder instanceof \IteratorAggregate ? $currentFinder->getIterator() : $currentFinder;
        } catch (\Exception $e) {
        }

        if ($isIntersectionPathMode) {
            if (null === $nestedFinder) {
                throw new InvalidConfigurationException(
                    'Cannot create intersection with not-fully defined Finder in configuration file.'
                );
            }

            return new \CallbackFilterIterator(
                $nestedFinder,
                static function (\SplFileInfo $current) use ($pathsByType) {
                    $currentRealPath = $current->getRealPath();

                    if (\in_array($currentRealPath, $pathsByType['file'], true)) {
                        return true;
                    }

                    foreach ($pathsByType['dir'] as $path) {
                        if (0 === strpos($currentRealPath, $path)) {
                            return true;
                        }
                    }

                    return false;
                }
            );
        }

        if (null !== $this->getConfigFile() && null !== $nestedFinder) {
            $this->configFinderIsOverridden = true;
        }

        if ($currentFinder instanceof SymfonyFinder && null === $nestedFinder) {
            // finder from configuration Symfony finder and it is not fully defined, we may fulfill it
            return $currentFinder->in($pathsByType['dir'])->append($pathsByType['file'])->exclude($this->options['exclude']);
        }

        return Finder::create()->in($pathsByType['dir'])->append($pathsByType['file'])->exclude('vendor');
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
            $configDir.\DIRECTORY_SEPARATOR.'.twig_cs',
            $configDir.\DIRECTORY_SEPARATOR.'.twig_cs.dist',
        ];
    }

    private static function separatedContextLessInclude($path)
    {
        return include $path;
    }
}
