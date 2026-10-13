<?php
// ==========================================================
// Plugins :: zeimoto :: Compute :: Site :: computedIndexingStatusLabel
// ==========================================================
//
// Exposes the site's indexing state as a Panel-ready label for
// blueprint queries.
//
// ==========================================================

declare(strict_types=1);

return [
  "computedIndexingStatusLabel" => function (): string {
    return $this->indexingStatus()->toBool() ? "Enabled" : "Disabled";
  },
];
