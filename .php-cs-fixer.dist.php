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
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PHP83Migration' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.cache/.php_cs.cache')
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());
