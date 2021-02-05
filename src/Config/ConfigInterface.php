<?php

namespace FriendsOfTwig\Twigcs\Config;

use FriendsOfTwig\Twigcs\TemplateResolver\TemplateResolverInterface;

/**
 * Special thanks to https://github.com/c33s/twigcs/ which this feature was inspired from.
 *
 * @method string getDisplay()
 */
interface ConfigInterface
{
    public const DISPLAY_BLOCKING = 'blocking';
    public const DISPLAY_ALL = 'all';

    /**
     * Returns the name of the configuration.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string The name of the configuration
     */
    public function getName(): string;

    /**
     * @return self
     */
    public function setName(string $name);

    public function getFinders(): array;

    /**
     * @param iterable|string[]|\Traversable $finder
     *
     * @return self
     */
    public function setFinder($finder);

    public function getSeverity(): string;

    /**
     * @return self
     */
    public function setSeverity(string $severity);

    public function getReporter(): string;

    /**
     * @return self
     */
    public function setReporter(string $reporter);

    public function getRuleSet(): string;

    /**
     * @return self
     */
    public function setRuleSet(string $ruleSet);

    public function getSpecificRuleSets(): array;

    /**
     * @return self
     */
    public function setSpecificRuleSets(array $ruleSet);

    public function getTemplateResolver(): TemplateResolverInterface;

    /**
     * @return self
     */
    public function setTemplateResolver(TemplateResolverInterface $loader);
}
