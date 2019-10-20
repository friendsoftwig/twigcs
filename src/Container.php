<?php

namespace FriendsOfTwig\Twigcs;

use FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter;
use FriendsOfTwig\Twigcs\Reporter\ConsoleReporter;
use FriendsOfTwig\Twigcs\Reporter\JUnitReporter;
use FriendsOfTwig\Twigcs\Validator\Validator;
use Pimple\Container as BaseContainer;

class Container extends BaseContainer
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

        $this['twig'] = function ($container) {
            $twig = new \Twig\Environment(new \Twig\Loader\ArrayLoader());

            $twig->setLexer(new Lexer($twig));

            return $twig;
        };

        $this['validator'] = function () {
            return new Validator();
        };
    }
}
