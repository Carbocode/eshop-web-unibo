<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__,
    ])
    ->name('*.php');

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR12' => true,
        '@PER-CS' => true,
        '@PHP83Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
        'trim_array_spaces' => true,
        'single_quote' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'blank_line_after_namespace' => true,
        'indentation_type' => true,
        'no_unused_imports' => true,
        'array_indentation' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'modernize_strpos' => true,
        'array_push' => true,
        'long_to_shorthand_operator' => true,
        'modernize_types_casting' => true,
        'no_useless_sprintf' => true,
        'strict_param' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
        'no_unneeded_braces' => ['namespaces' => true],
        //'declare_strict_types' => true, //TODO: da valutare
        //'date_time_immutable' => true, //TODO: da valutare
        'method_chaining_indentation' => true,
        'type_declaration_spaces' => ['elements' => ['function', 'property']],
        'types_spaces' => ['space' => 'single'],
        'no_empty_comment' => true,
        'single_line_comment_style' => true,
        'blank_lines_before_namespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'compact_nullable_type_declaration' => true,
        'class_attributes_separation' => true,
        'declare_strict_types' => true,
        'blank_line_before_statement' => ['statements' => ['break', 'case', 'continue', 'declare', 'default', 'exit', 'goto', 'include', 'include_once', 'phpdoc', 'require', 'require_once', 'return', 'switch', 'throw', 'try', 'yield', 'yield_from']],
    ])
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder($finder);
