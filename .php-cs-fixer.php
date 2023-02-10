<?php

$finder = PhpCsFixer\Finder::create()
//    ->exclude('vendor/')
//    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in('.');

return (new PhpCsFixer\Config('Docker', 'Docker style guide'))
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'case',
                'default',
            ],
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'echo_tag_syntax' => [
            'format' => 'short'
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'multiline_whitespace_before_semicolons' => false,
        'no_empty_comment' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_useless_else' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_summary' => false,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'php_unit_method_casing' => false,
        'psr_autoloading' => false,
        'return_type_declaration' => [
            'space_before' => 'one',
        ],
        'single_line_comment_spacing' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);