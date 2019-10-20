<?php

namespace FriendsOfTwig\Twigcs\RegEngine;

class RulesetConfigurator
{
    const MACRO_SPACING_PATTERN = '#^macro( +)name( *)\(( *)expr\)$#';
    const TAG_SPACING_PATTERN = '#^{%( *)expr( *)%}$#';
    const PRINT_STATEMENT_SPACING_PATTERN = '#^{{( *)expr( *)}}$#';
    const FUNC_SPACING_PATTERN = '#^func( *)\(expr\)$#';
    const ARRAY_SPACING_PATTERN = '#^\[( *)expr( *)\]$#';
    const EMPTY_ARRAY_SPACING_PATTERN = '#^\[( *)\]$#';
    const HASH_SPACING_PATTERN = '#^{( *)key( *):( *)expr( *),( *)key( *):( *)expr( *)}$#';
    const EMPTY_HASH_SPACING_PATTERN = '#^{( *)}$#';
    const EMPTY_PARENTHESES_SPACING_PATTERN = '#^\(( *)\)$#';
    const PARENTHESES_SPACING_PATTERN = '#^\(( *)expr( *)\)$#';
    const IF_SPACING_PATTERN = '#^if( *)expr$#';
    const ELSEIF_SPACING_PATTERN = '#^elseif( *)expr$#';
    const FOR_SPACING_PATTERN = '#^for( *)<key( *),( *)>item( *)in( *)expr<( *)if( *)expr>$#';
    const SET_SPACING_PATTERN = '#^set( *)expr( *)=( *)expr$#';
    const BINARY_OP_SPACING_PATTERN = '#^expr( *)op( *)expr$#';
    const UNARY_OP_SPACING_PATTERN = '#^op( *)expr$#';
    const SLICE_SPACING_PATTERN = '#^\[( *)expr( *):( *)expr( *)\]$#';
    const LIST_SPACING_PATTERN = '#^expr( *),( *)expr$#';
    const NAMED_LIST_SPACING_PATTERN = '#^key( *):( *)expr( *),( *)key( *):( *)expr$#';
    const IMPORT_SPACING_PATTERN = '#^import( *)expr( *)as( *)list( *),( *)expr( *)as( *)list$#';
    const FROM_SPACING_PATTERN = '#^from( *)expr( *)import( *)expr<( *)as( *)list>$#';
    const EMBED_SPACING_PATTERN = '#^embed( *)expr<( *)ignore missing><( *)with( *)list><( *)only>$#';
    const INCLUDE_SPACING_PATTERN = '#^include( *)expr<( *)ignore missing><( *)with( *)list><( *)only>$#';
    const TERNARY_SPACING_PATTERN = '#^expr( *)\?( *)expr( *)\:( *)expr\|\|expr( *)\?\:( *)expr$#';
    const PROPERTY_SPACING_PATTERN = '#^expr( *)\.( *)expr( *)\|( *)filter$#';
    const ARROW_FUNCTION_SPACING_PATTERN = '#^args( *)=>( *)expr$#';

    private $macroSpacingPattern = 'macro name(expr)';
    private $tagSpacingPattern = '{% expr %}';
    private $printStatementSpacingPattern = '{{ expr }}';
    private $funcSpacingPattern = 'func(expr)';
    private $arraySpacingPattern = '[expr]';
    private $emptyArraySpacingPattern = '[]';
    private $hashSpacingPattern = '{key: expr, key: expr}';
    private $emptyHashSpacingPattern = '{}';
    private $emptyParenthesesSpacingPattern = '()';
    private $parenthesesSpacingPattern = '(expr)';
    private $ifSpacingPattern = 'if expr';
    private $elseifSpacingPattern = 'elseif expr';
    private $forSpacingPattern = 'for <key, >item in expr< if expr>';
    private $setSpacingPattern = 'set expr = expr';
    private $binaryOpSpacingPattern = 'expr op expr';
    private $unaryOpSpacingPattern = 'op expr';
    private $sliceSpacingPattern = '[expr:expr]';
    private $listSpacingPattern = 'expr, expr';
    private $importSpacingPattern = 'import expr as list, expr as list';
    private $fromSpacingPattern = 'from expr import expr< as list>';
    private $embedSpacingPattern = 'embed expr< ignore missing>< with list>< only>';
    private $includeSpacingPattern = 'include expr< ignore missing>< with list>< only>';
    private $ternarySpacingPattern = 'expr ? expr : expr||expr ?: expr';
    private $propertySpacingPattern = 'expr.expr|filter';
    private $tagDefaultArgSpacing = 1; // Default space used between tag arguments : {% foo arg1 arg2 %}
    private $emptyListWhitespaces = 0;
    private $arrowFunctionSpacingPattern = 'args => expr';

    public function getProcessedConfiguration()
    {
        $config = [];

        preg_match(self::PARENTHESES_SPACING_PATTERN, $this->parenthesesSpacingPattern, $matches);
        $config['parentheses']['before_value'] = strlen($matches[1]);
        $config['parentheses']['after_value'] = strlen($matches[2]);

        preg_match(self::MACRO_SPACING_PATTERN, $this->macroSpacingPattern, $matches);
        $config['macro']['after_macro'] = strlen($matches[1]);
        $config['macro']['after_name'] = strlen($matches[2]);

        preg_match(self::TAG_SPACING_PATTERN, $this->tagSpacingPattern, $matches);
        $config['tag']['before'] = strlen($matches[1]);
        $config['tag']['after'] = strlen($matches[2]);

        preg_match(self::PRINT_STATEMENT_SPACING_PATTERN, $this->printStatementSpacingPattern, $matches);
        $config['print']['before'] = strlen($matches[1]);
        $config['print']['after'] = strlen($matches[2]);

        preg_match(self::FUNC_SPACING_PATTERN, $this->funcSpacingPattern, $matches);
        $config['func']['before_parentheses'] = strlen($matches[1]);

        preg_match(self::ARRAY_SPACING_PATTERN, $this->arraySpacingPattern, $matches);
        $config['array']['after_opening'] = strlen($matches[1]);
        $config['array']['before_closing'] = strlen($matches[2]);

        preg_match(self::EMPTY_ARRAY_SPACING_PATTERN, $this->emptyArraySpacingPattern, $matches);
        $config['array']['empty'] = strlen($matches[1]);

        preg_match(self::HASH_SPACING_PATTERN, $this->hashSpacingPattern, $matches);
        $config['hash']['after_opening'] = strlen($matches[1]);
        $config['hash']['after_key'] = strlen($matches[2]);
        $config['hash']['before_value'] = strlen($matches[3]);
        $config['hash']['after_value'] = strlen($matches[4]);
        $config['hash']['after_coma'] = strlen($matches[5]);
        $config['hash']['before_closing'] = strlen($matches[8]);

        preg_match(self::LIST_SPACING_PATTERN, $this->listSpacingPattern, $matches);
        $config['list']['after_value'] = strlen($matches[1]);
        $config['list']['after_coma'] = strlen($matches[2]);

        preg_match(self::EMPTY_HASH_SPACING_PATTERN, $this->emptyHashSpacingPattern, $matches);
        $config['hash']['empty'] = strlen($matches[1]);

        preg_match(self::EMPTY_PARENTHESES_SPACING_PATTERN, $this->emptyParenthesesSpacingPattern, $matches);
        $config['parentheses']['empty'] = strlen($matches[1]);

        preg_match(self::IF_SPACING_PATTERN, $this->ifSpacingPattern, $matches);
        $config['if'] = strlen($matches[1]);

        preg_match(self::ELSEIF_SPACING_PATTERN, $this->elseifSpacingPattern, $matches);
        $config['elseif'] = strlen($matches[1]);

        preg_match(self::FOR_SPACING_PATTERN, $this->forSpacingPattern, $matches);
        $config['for']['after_for'] = strlen($matches[1]);
        $config['for']['after_key_var'] = strlen($matches[2]);
        $config['for']['after_coma'] = strlen($matches[3]);
        $config['for']['after_value_var'] = strlen($matches[4]);
        $config['for']['after_in'] = strlen($matches[5]);
        $config['for']['before_if'] = strlen($matches[6]);
        $config['for']['after_if'] = strlen($matches[7]);

        preg_match(self::SET_SPACING_PATTERN, $this->setSpacingPattern, $matches);
        $config['set']['after_set'] = strlen($matches[1]);
        $config['set']['after_var_name'] = strlen($matches[2]);
        $config['set']['after_equal'] = strlen($matches[3]);

        preg_match(self::BINARY_OP_SPACING_PATTERN, $this->binaryOpSpacingPattern, $matches);
        $config['binary']['before_op'] = strlen($matches[1]);
        $config['binary']['after_op'] = strlen($matches[2]);

        preg_match(self::UNARY_OP_SPACING_PATTERN, $this->unaryOpSpacingPattern, $matches);
        $config['unary']['between'] = strlen($matches[1]);

        preg_match(self::SLICE_SPACING_PATTERN, $this->sliceSpacingPattern, $matches);
        $config['slice']['after_['] = strlen($matches[1]);
        $config['slice']['before_:'] = strlen($matches[2]);
        $config['slice']['after_:'] = strlen($matches[3]);
        $config['slice']['before_]'] = strlen($matches[4]);

        preg_match(self::IMPORT_SPACING_PATTERN, $this->importSpacingPattern, $matches);
        $config['import']['before_source'] = strlen($matches[1]);
        $config['import']['after_source'] = strlen($matches[2]);
        $config['import']['after_as'] = strlen($matches[3]);
        $config['import']['before_coma'] = strlen($matches[4]);
        $config['import']['after_coma'] = strlen($matches[5]);

        preg_match(self::FROM_SPACING_PATTERN, $this->fromSpacingPattern, $matches);
        $config['from']['before_source'] = strlen($matches[1]);
        $config['from']['after_source'] = strlen($matches[2]);
        $config['from']['before_names'] = strlen($matches[3]);
        $config['from']['before_as'] = strlen($matches[4]);
        $config['from']['after_as'] = strlen($matches[5]);

        preg_match(self::EMBED_SPACING_PATTERN, $this->embedSpacingPattern, $matches);
        $config['embed']['before_source'] = strlen($matches[1]);
        $config['embed']['before_ignore_missing'] = strlen($matches[2]);
        $config['embed']['before_with'] = strlen($matches[3]);
        $config['embed']['before_with_args'] = strlen($matches[4]);
        $config['embed']['before_only'] = strlen($matches[5]);

        preg_match(self::INCLUDE_SPACING_PATTERN, $this->includeSpacingPattern, $matches);
        $config['include']['before_source'] = strlen($matches[1]);
        $config['include']['before_ignore_missing'] = strlen($matches[2]);
        $config['include']['before_with'] = strlen($matches[3]);
        $config['include']['before_with_args'] = strlen($matches[4]);
        $config['include']['before_only'] = strlen($matches[5]);

        preg_match(self::TERNARY_SPACING_PATTERN, $this->ternarySpacingPattern, $matches);
        $config['ternary']['before_?'] = strlen($matches[1]);
        $config['ternary']['after_?'] = strlen($matches[2]);
        $config['ternary']['before_:'] = strlen($matches[3]);
        $config['ternary']['after_:'] = strlen($matches[4]);
        $config['ternary']['before_?:'] = strlen($matches[5]);
        $config['ternary']['after_?:'] = strlen($matches[6]);

        preg_match(self::PROPERTY_SPACING_PATTERN, $this->propertySpacingPattern, $matches);
        $config['property']['before_.'] = strlen($matches[1]);
        $config['property']['after_.'] = strlen($matches[2]);
        $config['property']['before_|'] = strlen($matches[3]);
        $config['property']['after_|'] = strlen($matches[4]);

        preg_match(self::ARROW_FUNCTION_SPACING_PATTERN, $this->arrowFunctionSpacingPattern, $matches);
        $config['arrow_function']['before_arrow'] = strlen($matches[1]);
        $config['arrow_function']['after_arrow'] = strlen($matches[2]);

        $config['tag_default_arg_spacing'] = $this->tagDefaultArgSpacing;
        $config['empty_list_whitespaces'] = $this->emptyListWhitespaces;

        return $config;
    }

    public function setMacroSpacingPattern(string $macroSpacingPattern): self
    {
        $this->macroSpacingPattern = $macroSpacingPattern;

        return $this;
    }

    public function setTagSpacingPattern(string $tagSpacingPattern): self
    {
        $this->tagSpacingPattern = $tagSpacingPattern;

        return $this;
    }

    public function setPrintStatementSpacingPattern(string $printStatementSpacingPattern): self
    {
        $this->printStatementSpacingPattern = $printStatementSpacingPattern;

        return $this;
    }

    public function setFuncSpacingPattern(string $funcSpacingPattern): self
    {
        $this->funcSpacingPattern = $funcSpacingPattern;

        return $this;
    }

    public function setArraySpacingPattern(string $arraySpacingPattern): self
    {
        $this->arraySpacingPattern = $arraySpacingPattern;

        return $this;
    }

    public function setEmptyArraySpacingPattern(string $emptyArraySpacingPattern): self
    {
        $this->emptyArraySpacingPattern = $emptyArraySpacingPattern;

        return $this;
    }

    public function setHashSpacingPattern(string $hashSpacingPattern): self
    {
        $this->hashSpacingPattern = $hashSpacingPattern;

        return $this;
    }

    public function setEmptyHashSpacingPattern(string $emptyHashSpacingPattern): self
    {
        $this->emptyHashSpacingPattern = $emptyHashSpacingPattern;

        return $this;
    }

    public function setEmptyParenthesesSpacingPattern(string $emptyParenthesesSpacingPattern): self
    {
        $this->emptyParenthesesSpacingPattern = $emptyParenthesesSpacingPattern;

        return $this;
    }

    public function setParenthesesSpacingPattern(string $parenthesesSpacingPattern): self
    {
        $this->parenthesesSpacingPattern = $parenthesesSpacingPattern;

        return $this;
    }

    public function setIfSpacingPattern(string $ifSpacingPattern): self
    {
        $this->ifSpacingPattern = $ifSpacingPattern;

        return $this;
    }

    public function setElseifSpacingPattern(string $elseifSpacingPattern): self
    {
        $this->elseifSpacingPattern = $elseifSpacingPattern;

        return $this;
    }

    public function setForSpacingPattern(string $forSpacingPattern): self
    {
        $this->forSpacingPattern = $forSpacingPattern;

        return $this;
    }

    public function setSetSpacingPattern(string $setSpacingPattern): self
    {
        $this->setSpacingPattern = $setSpacingPattern;

        return $this;
    }

    public function setBinaryOpSpacingPattern(string $binaryOpSpacingPattern): self
    {
        $this->binaryOpSpacingPattern = $binaryOpSpacingPattern;

        return $this;
    }

    public function setArrowFunctionSpacingPattern(string $arrowFunctionSpacingPattern): self
    {
        $this->arrowFunctionSpacingPattern = $arrowFunctionSpacingPattern;

        return $this;
    }

    public function setUnaryOpSpacingPattern(string $unaryOpSpacingPattern): self
    {
        $this->unaryOpSpacingPattern = $unaryOpSpacingPattern;

        return $this;
    }

    public function setSliceSpacingPattern(string $sliceSpacingPattern): self
    {
        $this->sliceSpacingPattern = $sliceSpacingPattern;

        return $this;
    }

    public function setListSpacingPattern(string $listSpacingPattern): self
    {
        $this->listSpacingPattern = $listSpacingPattern;

        return $this;
    }

    public function setImportSpacingPattern(string $importSpacingPattern): self
    {
        $this->importSpacingPattern = $importSpacingPattern;

        return $this;
    }

    public function setFromSpacingPattern(string $fromSpacingPattern): self
    {
        $this->fromSpacingPattern = $fromSpacingPattern;

        return $this;
    }

    public function setEmbedSpacingPattern(string $embedSpacingPattern): self
    {
        $this->embedSpacingPattern = $embedSpacingPattern;

        return $this;
    }

    public function setIncludeSpacingPattern(string $includeSpacingPattern): self
    {
        $this->includeSpacingPattern = $includeSpacingPattern;

        return $this;
    }

    public function setTernarySpacingPattern(string $ternarySpacingPattern): self
    {
        $this->ternarySpacingPattern = $ternarySpacingPattern;

        return $this;
    }

    public function setPropertySpacingPattern(string $propertySpacingPattern): self
    {
        $this->propertySpacingPattern = $propertySpacingPattern;

        return $this;
    }

    public function setTagDefaultArgSpacing(int $tagDefaultArgSpacing): self
    {
        $this->tagDefaultArgSpacing = $tagDefaultArgSpacing;

        return $this;
    }

    public function setEmptyListWhitespaces(int $emptyListWhitespaces): self
    {
        $this->emptyListWhitespaces = $emptyListWhitespaces;

        return $this;
    }
}
