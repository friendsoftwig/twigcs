<?php

namespace Allocine\Twigcs;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Reporter\CheckstyleReporter;
use Allocine\Twigcs\Reporter\ConsoleReporter;
use Allocine\Twigcs\Reporter\JUnitReporter;
use Allocine\Twigcs\Validator\Validator;
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
            $twig = new \Twig_Environment(new \Twig_Loader_Array());

            $twig->setLexer(new Lexer($twig));

            return $twig;
        };

        $this['validator'] = function () {
            return new Validator();
        };
    }
}
