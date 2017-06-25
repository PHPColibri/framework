<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setHideProgress(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2'        => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
    ;
