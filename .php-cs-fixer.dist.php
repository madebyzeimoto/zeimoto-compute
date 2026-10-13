<?php
// ==========================================================
// Tooling :: PHP-CS-Fixer
// ==========================================================
//
// Reuses the host kit's formatting rules for this plugin.
//
// ==========================================================

declare(strict_types=1);

use PhpCsFixer\Finder;

$config = require dirname(__DIR__, 3) . "/.php-cs-fixer.dist.php";

return $config->setFinder(
  Finder::create()
    ->exclude(".git")
    ->exclude("node_modules")
    ->exclude("vendor")
    ->notName("kirby.stubs.php")
    ->in(__DIR__),
);
