<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'storage',
        'bootstrap',
    ])
    ->notPath([
        '_ide_helper.php',
        '.phpstorm.meta.php',
    ]);

return (new Config())
    ->setRules([
        '@PER-CS2.0' => true,
        '@PHP83Migration' => true,
        // Most rules below come from @Symfony and @PhpCsFixer, but there are a few differences in configuration,
        // as well as some rules we just don't take at all because we have no official opinion on the style.
        'backtick_to_shell_exec' => true,
        // 'binary_operator_spaces' => true, // not ideal.  this takes a _lot_ of options.  TODO.
        'braces_position' => [
            // these are all defaults, but it's good to spell them out here
            'allow_single_line_anonymous_functions' => true,
            'allow_single_line_empty_anonymous_classes' => true,
            'anonymous_classes_opening_brace' => 'same_line',
            'anonymous_functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'class_definition' => ['single_line' => true],
        'class_reference_name_casing' => true,
        'declare_parentheses' => true,
        'empty_loop_body' => ['style' => 'braces'],
        'empty_loop_condition' => ['style' => 'while'],
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'general_phpdoc_tag_rename' => ['replacements' => ['inheritDocs' => 'inheritDoc']],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => false],
        'include' => true,
        'lambda_not_used_import' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        'no_alias_language_construct_call' => true,
        'no_alternative_syntax' => true,
        'no_binary_string' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute', 'break', 'case', 'continue', 'curly_brace_block', 'default',
                'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use',
            ],
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.cache/.php_cs.cache')
    ->setParallelConfig(ParallelConfigFactory::detect());
