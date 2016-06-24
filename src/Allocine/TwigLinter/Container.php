<?php

namespace Allocine\TwigLinter;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Reporter\CheckstyleReporter;
use Allocine\TwigLinter\Reporter\ConsoleReporter;
use Allocine\TwigLinter\Validator\Validator;
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

        $this['twig'] = function ($container) {
            $twig = new \Twig_Environment();

            $twig->setLexer(new Lexer($twig));

            return $twig;
        };

        $this['validator'] = function () {
            return new Validator();
        };
    }
}
