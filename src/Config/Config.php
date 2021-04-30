<?php

namespace FriendsOfTwig\Twigcs\Config;

use FriendsOfTwig\Twigcs\Ruleset\Official;

/**
 * Special thanks to https://github.com/c33s/twigcs/ which this feature was inspired from.
 */
class Config implements ConfigInterface
{
    private $name;
    private $finders;
    private $severity = 'warning';
    private $reporter = 'console';
    private $ruleset = Official::class;
    private $specificRulesets = [];
    private $display = ConfigInterface::DISPLAY_ALL;

    public function __construct($name = 'default')
    {
        $this->name = $name;
        $this->finders = [];
    }

    /**
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinders(): array
    {
        return $this->finders;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * {@inheritdoc}
     */
    public function setSeverity(string $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReporter(): string
    {
        return $this->reporter;
    }

    /**
     * {@inheritdoc}
     */
    public function setReporter(string $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleset(): string
    {
        return $this->ruleset;
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleset(string $ruleSet): self
    {
        $this->ruleset = $ruleSet;

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
