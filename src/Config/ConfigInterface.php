<?php

namespace FriendsOfTwig\Twigcs\Config;

use Symfony\Component\Finder\Finder;

interface ConfigInterface
{
    /**
     * Returns the name of the configuration.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string The name of the configuration
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name);

    /**
     * Returns files to scan.
     *
     * @return iterable|\Traversable
     */
    public function getFinder(): Finder;

    /**
     * @param iterable|string[]|\Traversable $finder
     *
     * @return self
     */
    public function setFinder($finder);

    /**
     * @return string
     */
    public function getSeverity(): string;

    /**
     * @param string $severity
     * @return self
     */
    public function setSeverity(string $severity);

    /**
     * @return string
     */
    public function getReporter(): string;

    /**
     * @param string $reporter
     * @return self
     */
    public function setReporter(string $reporter);

    /**
     * @return string
     */
    public function getRuleSet(): string;

    /**
     * @param string $ruleSet
     * @return self
     */
    public function setRuleSet(string $ruleSet);

    /**
     * @return array
     */
    public function getSpecificRuleSets(): array;

    /**
     * @param array $ruleSet
     * @return self
     */
    public function setSpecificRuleSets(array $ruleSet);
}
