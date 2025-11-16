<?php

use PhpCsFixer\Config;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return (new Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    //->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        'psr_autoloading' => true,
        'void_return' => true,
        'no_empty_statement' => true,
        'no_unused_imports' => true,
        'declare_strict_types' => false,
        'no_extra_blank_lines' => true,
        'no_whitespace_in_blank_line' => true,
        'no_blank_lines_after_class_opening' => true,
        'semicolon_after_instruction' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'no_empty_phpdoc' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'ternary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'function_declaration' => ['closure_function_spacing' => 'one', 'closure_fn_spacing' => 'one', 'trailing_comma_single_line' => false],
        'method_argument_space' => ['keep_multiple_spaces_after_comma' => false, 'after_heredoc' => true, 'on_multiline' => 'ignore'],
        'single_space_around_construct' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'no_closing_tag' => true,
        'single_blank_line_at_eof' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
    ])
    ->setFinder($finder)
    ->setUnsupportedPhpVersionAllowed(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

