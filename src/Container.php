<?php

namespace FriendsOfTwig\Twigcs;

use FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter;
use FriendsOfTwig\Twigcs\Reporter\ConsoleReporter;
use FriendsOfTwig\Twigcs\Reporter\EmacsReporter;
use FriendsOfTwig\Twigcs\Reporter\JUnitReporter;
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

        $this['lexer'] = function () {
            return new Lexer();
        };

        $this['validator'] = function () {
            return new Validator();
        };
    }

    public function get(string $key)
    {
        return call_user_func($this[$key]);
    }
}
