<?php
// ==========================================================
// Plugins :: zeimoto :: Compute
// ==========================================================
//
// Owns model-bound derived values, the compute() factory, and
// page-render injection. The two registered model exceptions
// and public integration contract are documented in README.md.
//
// ==========================================================

declare(strict_types=1);

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Zeimoto\Compute\Compute;

require_once __DIR__ . "/src/Compute.php";

$loadMethods = function (string $group): array {
  $methods = [];

  foreach (glob(__DIR__ . "/src/{$group}/*.php") as $file) {
    $methods = [...$methods, ...require $file];
  }

  return $methods;
};

if (function_exists("compute") === false) {
  function compute(Page|Site $model): Compute
  {
    return new Compute($model);
  }
}

Kirby::plugin("zeimoto/compute", [
  "siteMethods" => $loadMethods("site"),
  "pageMethods" => $loadMethods("page"),
  "hooks" => [
    "page.render:before" => function (array $data, Page $page): array {
      $data["compute"] = compute($page);

      return $data;
    },
  ],
]);
