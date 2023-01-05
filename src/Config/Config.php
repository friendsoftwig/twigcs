<?php

namespace FriendsOfTwig\Twigcs\Config;

use FriendsOfTwig\Twigcs\Ruleset\Official;
use FriendsOfTwig\Twigcs\TemplateResolver\NullResolver;
use FriendsOfTwig\Twigcs\TemplateResolver\TemplateResolverInterface;

/**
 * Special thanks to https://github.com/c33s/twigcs/ which this feature was inspired from.
 */
class Config implements ConfigInterface
{
    private string $name;
    private array $finders;
    private ?TemplateResolverInterface $loader;
    private string $severity = 'warning';
    private string $reporter = 'console';
    private string $ruleset = Official::class;
    private array $specificRulesets = [];
    private string $display = ConfigInterface::DISPLAY_ALL;

    public function __construct(string $name = 'default')
    {
        $this->name = $name;
        $this->finders = [];
        $this->loader = new NullResolver();
    }

    /**
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFinders(): array
    {
        return $this->finders;
    }

    public function setFinder($finder): self
    {
        if (false === \is_array($finder) && false === $finder instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('Argument must be an array or a Traversable, got "%s".', \is_object($finder) ? \get_class($finder) : \gettype($finder)));
        }

        $this->finders = [$finder];

        return $this;
    }

    public function addFinder($finder): self
    {
        if (false === \is_array($finder) && false === $finder instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('Argument must be an array or a Traversable, got "%s".', \is_object($finder) ? \get_class($finder) : \gettype($finder)));
        }

        $this->finders[] = $finder;

        return $this;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function setReporter(string $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getRuleset(): string
    {
        return $this->ruleset;
    }

    public function setRuleset(string $ruleSet): self
    {
        $this->ruleset = $ruleSet;

        return $this;
    }

    public function getTemplateResolver(): TemplateResolverInterface
    {
        return $this->loader;
    }

    public function setTemplateResolver(TemplateResolverInterface $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    public function getSpecificRulesets(): array
    {
        return $this->specificRulesets;
    }

    public function setSpecificRulesets(array $ruleSet): self
    {
        $this->specificRulesets = $ruleSet;

        return $this;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }
}
