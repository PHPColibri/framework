<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2'        => true,
        'not_operator_with_space' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
    ;
