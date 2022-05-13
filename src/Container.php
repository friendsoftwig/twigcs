<?php

namespace FriendsOfTwig\Twigcs;

use FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter;
use FriendsOfTwig\Twigcs\Reporter\ConsoleReporter;
use FriendsOfTwig\Twigcs\Reporter\CsvReporter;
use FriendsOfTwig\Twigcs\Reporter\EmacsReporter;
use FriendsOfTwig\Twigcs\Reporter\GithubActionReporter;
use FriendsOfTwig\Twigcs\Reporter\GitLabReporter;
use FriendsOfTwig\Twigcs\Reporter\JsonReporter;
use FriendsOfTwig\Twigcs\Reporter\JUnitReporter;
use FriendsOfTwig\Twigcs\Reporter\ReporterInterface;
use FriendsOfTwig\Twigcs\Validator\Validator;

class Container extends \ArrayObject
{
    public function __construct()
    {
        $this['reporter.console'] = function () {
            return new ConsoleReporter();
        };

        $this['reporter.checkstyle'] = function () {
            return new CheckstyleReporter();
        };

        $this['reporter.junit'] = function () {
            return new JUnitReporter();
        };

        $this['reporter.emacs'] = function () {
            return new EmacsReporter();
        };

        $this['reporter.json'] = function () {
            return new JsonReporter();
        };

        $this['reporter.csv'] = function () {
            return new CsvReporter();
        };

        $this['reporter.githubAction'] = function () {
            return new GithubActionReporter(new ConsoleReporter());
        };

        $this['reporter.gitlab'] = function (): ReporterInterface {
            return new GitLabReporter();
        };

        $this['lexer'] = function () {
            return new Lexer();
        };

        $this['validator'] = function () {
            return new Validator();
        };
    }

    /**
     * @throws \RuntimeException
     */
    public function get(string $key)
    {
        if (!$this->offsetExists($key)) {
            throw new \RuntimeException(sprintf('A service with the identifier "%s" has not been registered.', $key));
        }

        return call_user_func($this[$key]);
    }
}
