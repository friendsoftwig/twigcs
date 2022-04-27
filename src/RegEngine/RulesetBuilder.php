<?php

namespace FriendsOfTwig\Twigcs\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\Checker\Handler;

class RulesetBuilder
{
    public const OP_VARS = [
        '➀' => '\s*',
        '➁' => '\s*',
        '➂' => '\s*',
        '➃' => '\s*',
        '➄' => '\s*',
        '➅' => '\s*',
        '➆' => '\s*',
        '➇' => '\s*',
        '➈' => '\s*',
        '➉' => '\s*',
        '➊' => '\s+',
        '➋' => '\s+',
        '➌' => '\s+',
        '➍' => '\s+',
        '➎' => '\s+',
        '➏' => '\s+',
        '➐' => '\s+',
        '➑' => '\s+',
        '➒' => '\s+',
        '➓' => '\s+',
        ' ' => '\s*',
        '…' => '\s+',
        '$' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    public const TAGS_VARS = [
        '➀' => '\s*',
        '➁' => '\s*',
        '➂' => '\s*',
        '➃' => '\s*',
        '➄' => '\s*',
        '➅' => '\s*',
        '➆' => '\s*',
        '➇' => '\s*',
        '➈' => '\s*',
        '➉' => '\s*',
        '➊' => '\s+',
        '➋' => '\s+',
        '➌' => '\s+',
        '➍' => '\s+',
        '➎' => '\s+',
        '➏' => '\s+',
        '➐' => '\s+',
        '➑' => '\s+',
        '➒' => '\s+',
        '➓' => '\s+',
        ' ' => '\s+',
        '_' => '\s*',
        '…' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '&' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
        '<' => '{%[~-]?',
        '>' => '[~-]?%}',
        '{' => '{{[~-]?',
        '}' => '[~-]?}}',
    ];

    public const LIST_VARS = [
        '➀' => '\s*',
        '➁' => '\s*',
        '➂' => '\s*',
        '➃' => '\s*',
        '➄' => '\s*',
        '➅' => '\s*',
        '➆' => '\s*',
        '➇' => '\s*',
        '➈' => '\s*',
        '➉' => '\s*',
        '➊' => '\s+',
        '➋' => '\s+',
        '➌' => '\s+',
        '➍' => '\s+',
        '➎' => '\s+',
        '➏' => '\s+',
        '➐' => '\s+',
        '➑' => '\s+',
        '➒' => '\s+',
        '➓' => '\s+',
        ' ' => '\s*',
        '_' => '\s*',
        '…' => '\s*',
        '•' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '%' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    public const IMPORTS_VARS = [
        '➀' => '\s*',
        '➁' => '\s*',
        '➂' => '\s*',
        '➃' => '\s*',
        '➄' => '\s*',
        '➅' => '\s*',
        '➆' => '\s*',
        '➇' => '\s*',
        '➈' => '\s*',
        '➉' => '\s*',
        '➊' => '\s+',
        '➋' => '\s+',
        '➌' => '\s+',
        '➍' => '\s+',
        '➎' => '\s+',
        '➏' => '\s+',
        '➐' => '\s+',
        '➑' => '\s+',
        '➒' => '\s+',
        '➓' => '\s+',
        ' ' => '\s*',
        '_' => '\s*',
        '…' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '%' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    public const SLICE_VARS = [
        '➀' => '\s*',
        '➁' => '\s*',
        '➂' => '\s*',
        '➃' => '\s*',
        '➄' => '\s*',
        '➅' => '\s*',
        '➆' => '\s*',
        '➇' => '\s*',
        '➈' => '\s*',
        '➉' => '\s*',
        '➊' => '\s+',
        '➋' => '\s+',
        '➌' => '\s+',
        '➍' => '\s+',
        '➎' => '\s+',
        '➏' => '\s+',
        '➐' => '\s+',
        '➑' => '\s+',
        '➒' => '\s+',
        '➓' => '\s+',
        ' ' => '\s*',
        '$' => '(?:[^\?]|\n|\r)+?', // Excludes ternary from slice detection
    ];

    public const FALLBACK_VARS = [
        '➀' => '\s*',
        '➁' => '\s*',
        '➂' => '\s*',
        '➃' => '\s*',
        '➄' => '\s*',
        '➅' => '\s*',
        '➆' => '\s*',
        '➇' => '\s*',
        '➈' => '\s*',
        '➉' => '\s*',
        '➊' => '\s+',
        '➋' => '\s+',
        '➌' => '\s+',
        '➍' => '\s+',
        '➎' => '\s+',
        '➏' => '\s+',
        '➐' => '\s+',
        '➑' => '\s+',
        '➒' => '\s+',
        '➓' => '\s+',
        ' ' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
        '&' => '-?[0-9]+',
    ];

    private $config;

    public function __construct(RulesetConfigurator $configurator)
    {
        $this->config = $configurator->getProcessedConfiguration();
    }

    public function handle(): Handler
    {
        return Handler::create();
    }

    public function using($vars, array $rules): array
    {
        return array_map(function ($rule) use ($vars) {
            array_unshift($rule, $vars);

            return $rule;
        }, array_filter($rules));
    }

    public function unaryOpSpace(string $opName, string $spaceChar = '➀'): Handler
    {
        return $this
            ->handle()
            ->delegate('$', 'expr')
            ->enforceSize($spaceChar, $this->config['unary']['between'], sprintf('There should be %%quantity%% space(s) between the "%s" operator and its value.', $opName))
        ;
    }

    public function binaryOpSpace(string $opName, string $spaceChar = '➀', string $spaceChar2 = '➁'): Handler
    {
        return $this
            ->handle()
            ->delegate('$', 'expr')
            ->enforceSize($spaceChar, $this->config['binary']['before_op'], sprintf('There should be %%quantity%% space(s) between the "%s" operator and its left operand.', $opName))
            ->enforceSize($spaceChar2, $this->config['binary']['after_op'], sprintf('There should be %%quantity%% space(s) between the "%s" operator and its right operand.', $opName))
        ;
    }

    public function ternaryOpSpace(): Handler
    {
        return $this
            ->handle()
            ->delegate('$', 'expr')
            ->enforceSize('➀', $this->config['ternary']['before_?'], 'There should be %quantity% space(s) before the "?".')
            ->enforceSize('➁', $this->config['ternary']['after_?'], 'There should be %quantity% space(s) after the "?".')
            ->enforceSize('➂', $this->config['ternary']['before_:'], 'There should be %quantity% space(s) before the ":".')
            ->enforceSize('➃', $this->config['ternary']['after_:'], 'There should be %quantity% space(s) after the ":".')
            ->enforceSize('➄', $this->config['ternary']['before_?:'], 'There should be %quantity% space(s) before the "?:".')
            ->enforceSize('➅', $this->config['ternary']['after_?:'], 'There should be %quantity% space(s) after the "?:".')
        ;
    }

    public function noArgTag(): Handler
    {
        return $this
            ->handle()
            ->enforceSize('➀', $this->config['tag']['before'], 'A tag statement should start with %quantity% space(s).')
            ->enforceSize('➁', $this->config['tag']['after'], 'A tag statement should end with %quantity% space(s).')
        ;
    }

    public function argTag(): Handler
    {
        return $this->handle()
            ->enforceSize(' ', $this->config['tag_default_arg_spacing'], 'Tag arguments should be separated by %quantity% space(s).')
            ->enforceSize('➀', $this->config['tag']['before'], 'A tag statement should start with %quantity% space(s).')
            ->enforceSize('➁', $this->config['tag']['after'], 'A tag statement should end with %quantity% space(s).')
        ;
    }

    public function slice(): Handler
    {
        return $this->handle()
            ->delegate('$', 'expr')
            ->enforceSize('➀', $this->config['slice']['after_['], 'There should be %quantity% space(s) right after the opening "[" of a slice.')
            ->enforceSize('➁', $this->config['slice']['before_:'], 'There should be %quantity% space(s) before the middle ":" of a slice.')
            ->enforceSize('➂', $this->config['slice']['after_:'], 'There should be %quantity% space(s) after the middle ":" of a slice.')
            ->enforceSize('➃', $this->config['slice']['before_]'], 'There should be %quantity% space(s) right before the closing "]" of a slice.')
        ;
    }

    public function build(): array
    {
        $tags = self::using(self::TAGS_VARS, [
            ['<➀use $ with &➁>', $this->argTag()->delegate('$', 'expr')->delegate('&', 'imports')],
            ['<➀use $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀apply $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀endapply➁>', $this->noArgTag()],
            ['<➀autoescape $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀endautoescape➁>', $this->noArgTag()],
            ['<➀deprecated $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀do $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀flush➁>', $this->noArgTag()],
            ['<➀sandbox➁>', $this->noArgTag()],
            ['<➀endsandbox➁>', $this->noArgTag()],
            ['<➀verbatim➁>', $this->noArgTag()],
            ['<➀endverbatim➁>', $this->noArgTag()],
            ['<➀with➁>', $this->noArgTag()],
            ['<➀with only➁>', $this->noArgTag()],
            ['<➀with $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀with $ only➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀endwith➁>', $this->noArgTag()],
            $this->config['twigMajorVersion'] < 3 ? ['<➀spaceless➁>', $this->noArgTag()] : null,
            $this->config['twigMajorVersion'] < 3 ? ['<➀endspaceless➁>', $this->noArgTag()] : null,
            ['<➀extends $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀embed➊$➋ignore missing➌with➍&➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->delegate('&', 'with')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "ignore missing".')
                ->enforceSize('➌', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "with".')
                ->enforceSize('➍', $this->config['from']['before_source'], 'There should be %quantity% space(s) after the "with".')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀embed➊$➋ignore missing➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "ignore missing".')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀embed➊$➋ignore missing➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "ignore missing".'),
            ],
            ['<➀embed➊$➌with➍&➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')->delegate('&', 'with')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➌', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "with".')
                ->enforceSize('➍', $this->config['from']['before_source'], 'There should be %quantity% space(s) after the "with".')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀embed➊$➌with➍&➁>', $this
                ->argTag()
                ->delegate('$', 'expr')->delegate('&', 'with')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➌', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "with".')
                ->enforceSize('➍', $this->config['from']['before_source'], 'There should be %quantity% space(s) after the "with".'),
            ],
            ['<➀embed➊$➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀embed➊$➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.'),
            ],
            ['<➀elseif $➁>', $this->handle()->enforceSize(' ', $this->config['elseif'], 'There should be %quantity% space(s) between the elseif keyword and its condition.')->delegate('$', 'expr')],
            ['<➀else➁>', $this->noArgTag()],
            ['<➀include➊$➋ignore missing➌with➍&➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->delegate('&', 'with')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "ignore missing".')
                ->enforceSize('➌', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "with".')
                ->enforceSize('➍', $this->config['from']['before_source'], 'There should be %quantity% space(s) after the "with".')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀include➊$➋ignore missing➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "ignore missing".')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀include➊$➋ignore missing➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "ignore missing".'),
            ],
            ['<➀include➊$➌with➍&➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->delegate('&', 'with')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➌', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "with".')
                ->enforceSize('➍', $this->config['from']['before_source'], 'There should be %quantity% space(s) after the "with".')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀include➊$➌with➍&➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->delegate('&', 'with')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➌', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "with".')
                ->enforceSize('➍', $this->config['from']['before_source'], 'There should be %quantity% space(s) after the "with".'),
            ],
            ['<➀include➊$➎only➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➎', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the "only".'),
            ],
            ['<➀include➊$➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.'),
            ],

            ['<➀set @➁>', $this->argTag()],
            ['<➀endset➁>', $this->noArgTag()],
            ['<➀macro @_@➁>', $this->handle()
                ->enforceSize('➀', $this->config['tag']['before'], 'A tag statement should start with %quantity% space(s).')
                ->enforceSize('➁', $this->config['tag']['after'], 'A tag statement should end with %quantity% space(s).')
                ->enforceSize(' ', $this->config['macro']['after_macro'], 'There should be %quantity% space(s) between macro keyword and its name.')
                ->enforceSize('_', $this->config['macro']['after_name'], 'There should be %quantity% space(s) between macro name and args.'),
            ],
            ['<➀endmacro➁>', $this->noArgTag()],
            ['<➀block @➁>', $this->argTag()],
            ['<➀block @ $➁>', $this->argTag()->delegate('$', 'expr')],
            ['<➀endblock➁>', $this->noArgTag()],
            $this->config['twigMajorVersion'] < 3 ? ['<➀filter @➁>', $this->argTag()] : null,
            $this->config['twigMajorVersion'] < 3 ? ['<➀endfilter➁>', $this->noArgTag()] : null,
            ['<➀import➊$➋as➌&➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->delegate('&', 'list')
                ->enforceSize('➊', $this->config['import']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['import']['after_source'], 'There should be %quantity% space(s) after the source.')
                ->enforceSize('➌', $this->config['import']['after_as'], 'There should be %quantity% space(s) after the "as".'),
            ],
            ['<➀from➊$➋import➌@➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['after_source'], 'There should be %quantity% space(s) after the source.')
                ->enforceSize('➌', $this->config['from']['before_names'], 'There should be %quantity% space(s) before the imported names.'),
            ],
            ['<➀from➊$➋import➌@➍as➎&➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->delegate('&', 'list')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['after_source'], 'There should be %quantity% space(s) after the source.')
                ->enforceSize('➌', $this->config['from']['before_names'], 'There should be %quantity% space(s) before the imported names.')
                ->enforceSize('➍', $this->config['from']['before_as'], 'There should be %quantity% space(s) before the "as".')
                ->enforceSize('➎', $this->config['from']['after_as'], 'There should be %quantity% space(s) after the "as".'),
            ],
            ['<➀from➊$➋import➌&➁>', $this
                ->argTag()
                ->delegate('$', 'expr')->delegate('&', 'list')
                ->enforceSize('➊', $this->config['from']['before_source'], 'There should be %quantity% space(s) before the source.')
                ->enforceSize('➋', $this->config['from']['after_source'], 'There should be %quantity% space(s) after the source.')
                ->enforceSize('➌', $this->config['from']['before_names'], 'There should be %quantity% space(s) before the imported names.'),
            ],
            ['{➀$➁}', $this->handle()->delegate('$', 'expr')->enforceSize('➀', $this->config['print']['before'], 'A print statement should start with %quantity% space(s).')->enforceSize('➁', $this->config['print']['after'], 'A print statement should end with %quantity% space(s).')],
            ['<➀if $➁>', $this->handle()->delegate('$', 'expr')->enforceSize(' ', $this->config['if'], 'There should be %quantity% space(s) between the if keyword and its condition.')],
            ['<➀endif➁>', $this->noArgTag()],
            ['<➀endfor➁>', $this->noArgTag()],

            $this->config['twigMajorVersion'] < 3 ? ['<➀for➊@➋,➌@➍in➎$➏if➐$➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['for']['after_for'], 'There should be %quantity% space(s) between for and the local variables.')
                ->enforceSize('➋', $this->config['for']['after_key_var'], 'There should be %quantity% space(s) after the local key variable.')
                ->enforceSize('➌', $this->config['for']['after_coma'], 'There should be %quantity% space(s) after the coma.')
                ->enforceSize('➍', $this->config['for']['after_value_var'], 'There should be %quantity% space(s) between the local variable and the in operator.')
                ->enforceSize('➎', $this->config['for']['after_in'], 'There should be %quantity% space(s) after the in operator.')
                ->enforceSize('➏', $this->config['for']['before_if'], 'There should be %quantity% space(s) before the if part of the loop.')
                ->enforceSize('➐', $this->config['for']['after_if'], 'There should be %quantity% space(s) between the if and its expression.'),
            ] : null,
            ['<➀for➊@➋,➌@➍in➎$➁>', $this
                ->argTag()
                ->enforceSize('➊', $this->config['for']['after_for'], 'There should be %quantity% space(s) between for and the local variables.')
                ->enforceSize('➋', $this->config['for']['after_key_var'], 'There should be %quantity% space(s) after the local key variable.')
                ->enforceSize('➌', $this->config['for']['after_coma'], 'There should be %quantity% space(s) after the coma.')
                ->enforceSize('➍', $this->config['for']['after_value_var'], 'There should be %quantity% space(s) between the local variable and the in operator.')
                ->enforceSize('➎', $this->config['for']['after_in'], 'There should be %quantity% space(s) after the in operator.'),
            ],
            $this->config['twigMajorVersion'] < 3 ? ['<➀for➊@➋in➎$➏if➐$➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['for']['after_for'], 'There should be %quantity% space(s) between for and the local variables.')
                ->enforceSize('➋', $this->config['for']['after_value_var'], 'There should be %quantity% space(s) after the local variable.')
                ->enforceSize('➎', $this->config['for']['after_in'], 'There should be %quantity% space(s) after the in operator.')
                ->enforceSize('➏', $this->config['for']['before_if'], 'There should be %quantity% space(s) before the if part of the loop.')
                ->enforceSize('➐', $this->config['for']['after_if'], 'There should be %quantity% space(s) between the if and its expression.'),
            ] : null,
            ['<➀for➊@➋in➎$➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['for']['after_for'], 'There should be %quantity% space(s) between for and the local variables.')
                ->enforceSize('➋', $this->config['for']['after_value_var'], 'There should be %quantity% space(s) after the local variable.')
                ->enforceSize('➎', $this->config['for']['after_in'], 'There should be %quantity% space(s) after the in operator.'),
            ],

            ['<➀set➊@➋=➌$➁>', $this
                ->argTag()
                ->delegate('$', 'expr')
                ->enforceSize('➊', $this->config['set']['after_set'], 'There should be %quantity% space(s) after the "set".')
                ->enforceSize('➋', $this->config['set']['after_var_name'], 'There should be %quantity% space(s) before the "=".')
                ->enforceSize('➌', $this->config['set']['after_equal'], 'There should be %quantity% space(s) after the "=".'),
            ],
            ['<➀@➁>', $this->noArgTag()],
            ['<➀@ &➁>', $this->argTag()->delegate('&', 'list')],
        ]);

        $ops = $this->using(self::OP_VARS, [
            ['not➊$', $this->unaryOpSpace('not', '➊')],
            ['@ __PARENTHESES__', $this->handle()->enforceSize(' ', $this->config['func']['before_parentheses'], 'There should be %quantity% space(s) between a function name and its opening parentheses.')],
            ['\( \)', $this->handle()->enforceSize(' ', $this->config['parentheses']['empty'], 'There should be %quantity% space(s) inside empty parentheses.')],
            ["\(\n $\n \)", $this->handle()->delegate('$', 'argsList')], // Multiline function call
            ['\(➀$➁\)', $this
                ->handle()
                ->delegate('$', 'argsList')
                ->enforceSize('➀', $this->config['parentheses']['before_value'], 'There should be %quantity% space(s) between the opening parenthese and its content.')
                ->enforceSize('➁', $this->config['parentheses']['after_value'], 'There should be %quantity% space(s) between the closing parenthese and its content.'),
            ],
            ['\[ \]', $this->handle()->enforceSize(' ', $this->config['array']['empty'], 'There should be %quantity% space(s) inside empty arrays.')],
            ['\[➀$➁\]', $this->handle()
                ->delegate('$', 'list')
                ->enforceSize('➀', $this->config['array']['after_opening'], 'There should be %quantity% space(s) before the array values.')
                ->enforceSize('➁', $this->config['array']['before_closing'], 'There should be %quantity% space(s) after the array values.'),
            ],
            ['\{ \}', $this->handle()->enforceSize(' ', $this->config['hash']['empty'], '%quantity% space(s) should be used for empty hashes.')],
            ["\{\n $ \n\}", $this->handle()->delegate('$', 'hash')],
            ['\{➀$➁\}', $this->handle()
                ->delegate('$', 'hash')
                ->enforceSpaceOrLineBreak('➀', $this->config['hash']['after_opening'], 'There should be %quantity% space(s) before the hash values.')
                ->enforceSpaceOrLineBreak('➁', $this->config['hash']['before_closing'], 'There should be %quantity% space(s) after the hash values.'),
            ],
            ['$➀<=>➁$', $this->binaryOpSpace('<=>')],
            ['$➀=>➁$', $this
                ->handle()
                ->delegate('$', 'expr')
                ->enforceSize('➀', $this->config['arrow_function']['before_arrow'], 'There should be %quantity% space(s) between the arrow and its arguments.')
                ->enforceSize('➁', $this->config['arrow_function']['after_arrow'], 'There should be %quantity% space(s) between the arrow and its body.'),
            ],
            ['$➀\?\?➁$', $this->binaryOpSpace('??')],
            ['$➀<=➁$', $this->binaryOpSpace('<=')],
            ['$➀>=➁$', $this->binaryOpSpace('>=')],
            ['$➀<➁$', $this->binaryOpSpace('<')],
            ['$➀>➁$', $this->binaryOpSpace('>')],
            ['$➀!=➁$', $this->binaryOpSpace('!=')],
            ['$➀==➁$', $this->binaryOpSpace('==')],
            ['$➀=➁$', $this->binaryOpSpace('=')],
            ['$➊b-and➋$', $this->binaryOpSpace('b-and', '➊', '➋')],
            ['$➊b-xor➋$', $this->binaryOpSpace('b-xor', '➊', '➋')],
            ['$➊b-or➋$', $this->binaryOpSpace('b-or', '➊', '➋')],
            ['$➊and➋$', $this->binaryOpSpace('and', '➊', '➋')],
            ['$➊or➋$', $this->binaryOpSpace('or', '➊', '➋')],
            ['$➀__TERNARY__➃$', $this->ternaryOpSpace()],
            ['$➄\?:➅$', $this->ternaryOpSpace()],
            ['$➀\?➁$', $this->ternaryOpSpace()],
            ['\?➁$➂\:', $this->ternaryOpSpace()],
            ['$➀\+➁$', $this->binaryOpSpace('+')],
            ['$➀-➁$', $this->binaryOpSpace('-')],
            ['$➀~➁$', $this->binaryOpSpace('~')],
            ['$➀//➁$', $this->binaryOpSpace('//')],
            ['$➀/➁$', $this->binaryOpSpace('/')],
            ['$➀%➁$', $this->binaryOpSpace('%')],
            ['$➀\*\*➁$', $this->binaryOpSpace('**')],
            ['$➀\*➁$', $this->binaryOpSpace('*')],
            ['$➊is not➋$', $this->binaryOpSpace('is', '➊', '➋')],
            ['$➊is➋$', $this->binaryOpSpace('is', '➊', '➋')],
            ['$➊not in➋$', $this->binaryOpSpace('in', '➊', '➋')],
            ['$➊in➋$', $this->binaryOpSpace('in', '➊', '➋')],
            ['$➊matches➋$', $this->binaryOpSpace('matches', '➊', '➋')],
            ['$➊starts with➋$', $this->binaryOpSpace('starts with', '➊', '➋')],
            ['$➊ends with➋$', $this->binaryOpSpace('ends with', '➊', '➋')],
            ['$➀\.\.➁$', $this
                ->handle()
                ->delegate('$', 'expr')
                ->enforceSize('➀', $this->config['range']['before_op'], 'There should be %quantity% space(s) between the ".." operator and its left operand.')
                ->enforceSize('➁', $this->config['range']['after_op'], 'There should be %quantity% space(s) between the ".." operator and its right operand.'),
            ],
            ['same➀as$', $this->unaryOpSpace('same as', '➀')],
            ['$➀\|➁$', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize('➀', $this->config['property']['before_|'], 'There should be %quantity% space(s) before the "|".')
                ->enforceSize('➁', $this->config['property']['after_|'], 'There should be %quantity% space(s) after the "|".'),
            ],
            ['$➀\.➁$', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize('➀', $this->config['property']['before_.'], 'There should be %quantity% space(s) before the ".".')
                ->enforceSize('➁', $this->config['property']['after_.'], 'There should be %quantity% space(s) after the ".".'),
            ],
        ]);

        $fallback = $this->using(self::FALLBACK_VARS, [
            [' "" ', Handler::create()->noop()],
            [' "$" ', Handler::create()->noop()],
            [' @ ', Handler::create()->noop()],
            [' & ', Handler::create()->noop()],
            [' \[\] ', Handler::create()->noop()],
            [' \?\: ', Handler::create()->noop()],
        ]);

        $argsList = $this->using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', $this->config['empty_list_whitespaces'], 'Empty list should have %quantity% whitespace(s).')],
            ['@➀=(?![>=])➁$➂,➃%', Handler::create()
                ->enforceSize('➀', $this->config['named_args']['before_='], 'There should be %quantity% space(s) before the "=" in the named arguments list.')
                ->enforceSize('➁', $this->config['named_args']['after_='], 'There should be %quantity% space(s) after the "=" in the named arguments list.')
                ->enforceSize('➂', $this->config['named_args']['after_value'], 'There should be %quantity% space(s) after the value in the named arguments list.')
                ->delegate('$', 'expr')
                ->delegate('%', 'argsList'),
            ],
            ['@➀=(?![>=])➁$', Handler::create()
                ->enforceSize('➀', $this->config['named_args']['before_='], 'There should be %quantity% space(s) before the "=" in the named arguments list.')
                ->enforceSize('➁', $this->config['named_args']['after_='], 'There should be %quantity% space(s) after the "=" in the named arguments list.')
                ->delegate('$', 'expr'),
            ],
            ['$_, %', Handler::create()
                ->delegate('$', 'expr')
                ->delegate('%', 'argsList')
                ->enforceSize('_', $this->config['list']['after_value'], 'There should be %quantity% space(s) before the ",".')
                ->enforceSpaceOrLineBreak(' ', $this->config['list']['after_coma'], 'The next value of a list should be separated by %quantity% space(s).'),
            ],
            [' @ ', Handler::create()->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
            [' $, ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
            [' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
        ]);

        $list = $this->using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', $this->config['empty_list_whitespaces'], 'Empty list should have %quantity% whitespace(s).')],
            ['$_, %', Handler::create()
                ->delegate('$', 'expr')
                ->delegate('%', 'list')
                ->enforceSize('_', $this->config['list']['after_value'], 'There should be %quantity% space(s) before the ",".')
                ->enforceSpaceOrLineBreak(' ', $this->config['list']['after_coma'], 'The next value of a list should be separated by %quantity% space(s).'),
            ],
            [' @ ', Handler::create()->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
            [' $, ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
            [' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
        ]);

        $imports = $this->using(self::IMPORTS_VARS, [
            ['@➀as➁@_,…%', Handler::create()
                ->delegate('%', 'imports')
                ->enforceSize('_', $this->config['import']['before_coma'], 'There should be %quantity% space(s) between an aliased import and the next one.')
                ->enforceSize('…', $this->config['import']['after_coma'], 'There should be %quantity% space(s) after the previous import.')
                ->enforceSize('➀', $this->config['import']['after_source'], 'There should be %quantity% space(s) between the as operator and its operands.')
                ->enforceSize('➁', $this->config['import']['after_as'], 'There should be %quantity% space(s) between the as operator and its operands.'),
            ],
            ['@➀as➁@', Handler::create()
                ->enforceSize('➀', $this->config['import']['after_source'], 'There should be %quantity% space(s) between the as operator and its operands.')
                ->enforceSize('➁', $this->config['import']['after_as'], 'There should be %quantity% space(s) between the as operator and its operands.'),
            ],
            [' @ ', Handler::create()->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
            [' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
        ]);

        $hash = $this->using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', $this->config['hash']['empty'], 'Empty hash should have %quantity% whitespace(s).')],
            ['@ :_$•,…%', Handler::create()
                ->delegate('$', 'expr')
                ->delegate('%', 'hash')
                ->enforceSize(' ', $this->config['hash']['after_key'], 'There should be %quantity% space(s) between the key and ":".')
                ->enforceSize('_', $this->config['hash']['before_value'], 'There should be %quantity% space(s) between ":" and the value.')
                ->enforceSize('•', $this->config['hash']['after_value'], 'There should be %quantity% space(s) between the value and the following ",".')
                ->enforceSpaceOrLineBreak('…', $this->config['hash']['after_coma'], 'There should be %quantity% space(s) between the , and the following hash key.'),
            ],
            ['"@" :_$•,…%', Handler::create()
                ->delegate('$', 'expr')
                ->delegate('%', 'hash')
                ->enforceSize(' ', $this->config['hash']['after_key'], 'There should be %quantity% space(s) between the key and ":".')
                ->enforceSize('_', $this->config['hash']['before_value'], 'There should be %quantity% space(s) between ":" and the value.')
                ->enforceSize('•', $this->config['hash']['after_value'], 'There should be %quantity% space(s) between the value and the following ",".')
                ->enforceSpaceOrLineBreak('…', $this->config['hash']['after_coma'], 'There should be %quantity% space(s) between the , and the following hash key.'),
            ],
            ['@ :_$,', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize(' ', $this->config['hash']['after_key'], 'There should be %quantity% space(s) between the key and ":".')
                ->enforceSize('_', $this->config['hash']['before_value'], 'There should be %quantity% space(s) between ":" and the value.'),
            ],
            ['@ :_$', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize(' ', $this->config['hash']['after_key'], 'There should be %quantity% space(s) between the key and ":".')
                ->enforceSize('_', $this->config['hash']['before_value'], 'There should be %quantity% space(s) between ":" and the value.'),
            ],
            ['"@" :_$,', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize(' ', $this->config['hash']['after_key'], 'There should be %quantity% space(s) between the key and ":".')
                ->enforceSize('_', $this->config['hash']['before_value'], 'There should be %quantity% space(s) between ":" and the value.'),
            ],
            ['"@" :_$', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize(' ', $this->config['hash']['after_key'], 'There should be %quantity% space(s) between the key and ":".')
                ->enforceSize('_', $this->config['hash']['before_value'], 'There should be %quantity% space(s) between ":" and the value.'),
            ],
            ['@•,…%', Handler::create()
                ->delegate('%', 'hash')
                ->enforceSize('•', $this->config['hash']['after_value'], 'There should be %quantity% space(s) between the value and the following ",".')
                ->enforceSpaceOrLineBreak('…', $this->config['hash']['after_coma'], 'There should be %quantity% space(s) between the , and the following hash key.'),
            ],
            ['@', Handler::create()->noop()], // Collects the last entry of a short object initialization
        ]);

        $hashFallback = $this->using(self::FALLBACK_VARS, [
            [' __HASH__ ', Handler::create()->noop()],
        ]);

        $slice = $this->using(self::SLICE_VARS, [
            ['\[➀:➂$➃\]', $this->slice()],
            ['\[➀$➁:➃\]', $this->slice()],
            ['\[➀$➁:➂$➃\]', $this->slice()],
            ['\[ \]', Handler::create()->enforceSize(' ', $this->config['array']['empty'], 'There should be %quantity% space(s) inside an empty array.')],
        ]);

        $array = $this->using(self::OP_VARS, [
            ['\[➀$➁\]', $this->handle()
                ->delegate('$', 'list')
                ->enforceSpaceOrLineBreak('➀', $this->config['array']['after_opening'], 'There should be %quantity% space(s) before the array values.')
                ->enforceSpaceOrLineBreak('➁', $this->config['array']['before_closing'], 'There should be %quantity% space(s) after the array values.'),
            ],
        ]);

        $with = $this->using(self::OP_VARS, [
            ['$', $this->handle()->noop()],
        ]);

        return [
            'expr' => array_merge($tags, $ops, $fallback),
            'list' => $list,
            'argsList' => $argsList,
            'hash' => array_merge($hash, $hashFallback),
            'with' => array_merge($hash, $with),
            'imports' => $imports,
            'arrayOrSlice' => array_merge($slice, $array),
        ];
    }
}
