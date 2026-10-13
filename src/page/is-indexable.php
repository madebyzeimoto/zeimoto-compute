<?php
// ==========================================================
// Plugins :: zeimoto :: Compute :: Page :: isIndexable
// ==========================================================
//
// Resolves whether a page may be indexed from site, page,
// template, and ancestor settings.
//
// ==========================================================

declare(strict_types=1);

return [
  "isIndexable" => function (): bool {
    if (!$this->site()->indexingStatus()->toBool()) {
      return false;
    }
    if ($this->intendedTemplate()->name() === "system") {
      return false;
    }
    if (!$this->indexingStatus()->toBool(true)) {
      return false;
    }
    foreach ($this->parents() as $parent) {
      if (!$parent->indexingChildrenStatus()->toBool(true)) {
        return false;
      }
    }
    return true;
  },
];
