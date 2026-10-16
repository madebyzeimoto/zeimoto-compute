<?php
// ==========================================================
// Tests :: Compute Contracts
// ==========================================================
//
// Exercises the plugin public behavior with disposable Kirby fixtures.
//
// ==========================================================

declare(strict_types=1);

namespace Zeimoto\Compute\Tests;

use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\DataProvider;

final class ContractTest extends PluginTestCase
{
  public static function legalTemplates(): array
  {
    return [["legal"], ["policy"]];
  }

  #[DataProvider("legalTemplates")]
  public function testLegalMetadataIgnoresSavedOverrides(string $template): void
  {
    $this->app(site: ["content" => ["title" => "Site", "metadataDescription" => "Site description", "indexingStatus" => "true"]]);
    $page = new Page(["slug" => "legal", "template" => $template, "content" => [
      "title" => "Page title", "metadataTitle" => "Old override", "metadataDescription" => "Old description",
      "metadataCanonicalUrl" => "https://old.example", "schemaType" => "Article", "indexingStatus" => "true",
    ]]);
    $compute = compute($page);
    self::assertSame("Page title", $compute->title()->value());
    self::assertSame("Site description", $compute->description()->value());
    self::assertSame($page->url(), $compute->canonicalUrl());
    self::assertSame("WebPage", $compute->schemaType());
    self::assertSame("website", $compute->opengraphType());
    self::assertFalse($page->isIndexable());
  }

  public function testOrdinaryFallbacksRemainRawAndHomeIsFixed(): void
  {
    $app = $this->app(site: ["content" => ["title" => "Site", "metadataDescription" => "Site <description>"], "children" => [["slug" => "home", "template" => "home"]]]);
    $page = new Page(["slug" => "ordinary", "content" => ["title" => "A & B", "schemaType" => "Article"]]);
    self::assertSame("A & B", compute($page)->title()->value());
    self::assertSame("Site <description>", compute($page)->description()->value());
    self::assertSame("article", compute($page)->opengraphType());
    self::assertSame("HomePage", compute($app->site()->homePage())->schemaType());
    self::assertNull(compute($page)->opengraphImage());
    self::assertSame("Hello world", compute($page)->plainText(new \Kirby\Content\Field($page, "text", "<p>Hello</p><p>world</p>")));
  }

  public function testMissingSiteIndexingDisablesOrdinaryPages(): void
  {
    $app = $this->app();
    self::assertFalse((new Page(["slug" => "page"]))->isIndexable());
    self::assertSame("Disabled", $app->site()->computedIndexingStatusLabel());
  }
}
