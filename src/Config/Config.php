<?php

namespace FriendsOfTwig\Twigcs\Config;

use FriendsOfTwig\Twigcs\Ruleset\Official;
use Symfony\Component\Finder\Finder;

class Config implements ConfigInterface
{
    private $name;
    private $finder;
    private $severity = 'warning';
    private $reporter = 'reporter.console';
    private $ruleset = Official::class;
    private $specificRulesets = [];

    public function __construct($name = 'default')
    {
        $this->name = $name;
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
    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * {@inheritdoc}
     */
    public function setFinder($finder): self
    {
        if (false === \is_array($finder) && false === $finder instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf(
                'Argument must be an array or a Traversable, got "%s".',
                \is_object($finder) ? \get_class($finder) : \gettype($finder)
            ));
        }

        $this->finder = $finder;

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

    /**
     * @return array
     */
    public function getSpecificRulesets(): array
    {
        return $this->specificRulesets;
    }

    /**
     * @param array $ruleSet
     * @return self
     */
    public function setSpecificRulesets(array $ruleSet): self
    {
        $this->specificRulesets = $ruleSet;

        return $this;
    }
}
