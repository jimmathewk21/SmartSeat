<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'binary_operator_spaces' => ['default' => 'align_single_space_minimal'],
    ])
    ->setFinder($finder);
