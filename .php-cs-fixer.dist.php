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
        '@PHP84Migration' => true,
        // Most rules below come from @Symfony and @PhpCsFixer, but there are a few differences in configuration,
        // as well as some rules we just don't take at all because we have no official opinion on the style.
        'backtick_to_shell_exec' => true,
        'binary_operator_spaces' => ['default' => 'at_least_single_space'], // leaves existing alignments alone
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
        'explicit_indirect_variable' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'general_phpdoc_tag_rename' => ['replacements' => ['inheritDocs' => 'inheritDoc']],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => false],
        'include' => true,
        'lambda_not_used_import' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
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
                'attribute', 'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra',
                'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use',
            ],
        ],
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_null_property_initialization' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
        'no_trailing_comma_in_singleline' => true,
        'no_unneeded_braces' => ['namespaces' => true],
        'no_unneeded_control_parentheses' => [
            'statements' => [
                'break', 'clone', 'continue', 'echo_print', 'others', 'return', 'switch_case', 'yield', 'yield_from',
            ],
        ],
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'no_useless_concat_operator' => ['juggle_simple_strings' => true],
        'no_useless_nullsafe_operator' => true,
        'no_useless_return' => true,
        'nullable_type_declaration' => ['syntax' => 'question_mark'],
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => ['only_booleans' => true],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'ordered_types' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => false, // align phpdoc yourself or don't, but we don't enforce it
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => ['order' => ['param', 'return', 'throws']],
        'phpdoc_order_by_value' => ['annotations' => ['covers']],
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        'protected_to_private' => true,
        'self_static_accessor' => true,
        'single_class_element_per_statement' => true,
        'single_import_per_statement' => true,
        'single_quote' => false, // we have no opinion on single quote use
        'single_space_around_construct' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],
        'standardize_not_equals' => true,
        'string_implicit_backslashes' => true,
        'switch_continue_to_break' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => true,
            'elements' => ['array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        'trim_array_spaces' => true,
        'type_declaration_spaces' => true,
        'types_spaces' => ['space' => 'none'],
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
        'yoda_style' => false,  // official opinion on yoda style we do not have
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.cache/.php_cs.cache')
    ->setParallelConfig(ParallelConfigFactory::detect());
