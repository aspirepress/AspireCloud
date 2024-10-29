<?php

$finder = (new PhpCsFixer\Finder())
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

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0' => true,
        '@PHP83Migration' => true,
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
        'class_attributes_separation' => [
            'elements' => ['method' => 'one']
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.cache/.php_cs.cache')
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());
