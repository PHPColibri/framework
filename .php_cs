<?php

return PhpCsFixer\Config::create()
    ->setFinder(
        PhpCsFixer\Finder::create()->in(__DIR__)
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                              => true,
        'array_syntax'                          => ['syntax' => 'short'],
        'binary_operator_spaces'                => [
            'align_double_arrow' => true,
            'align_equals'       => true,
        ],
        'cast_spaces'                           => false,
        'concat_space'                          => ['spacing' => 'one'],
        'general_phpdoc_annotation_remove'      => ['author'],
        'heredoc_to_nowdoc'                     => true,
        'is_null'                               => ['use_yoda_style' => false],
        'no_blank_lines_before_namespace'       => true,
        'no_php4_constructor'                   => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_return'                     => true,
        'non_printable_character'               => true,
        'not_operator_with_space'               => true,
        'ordered_imports'                       => true,
        'php_unit_construct'                    => true,
        'phpdoc_add_missing_param_annotation'   => true,
        'phpdoc_no_alias_tag'                   => [
            'replacements' => [
                'property-write' => 'property',
                'type'           => 'var',
                'link'           => 'see',
            ],
        ],
        'phpdoc_no_empty_return'                => false,
        'phpdoc_types'                          => false,
        'pow_to_exponentiation'                 => true,
        'pre_increment'                         => false,
        'psr4'                                  => true,
        'single_blank_line_before_namespace'    => false,


        // Risky под вопросом:
        'ereg_to_preg'                          => true,
        'function_to_constant'                  => true,
        'no_alias_functions'                    => true,
        'random_api_migration'                  => true,
        //'modernize_types_casting' => true

    ])
    ;
