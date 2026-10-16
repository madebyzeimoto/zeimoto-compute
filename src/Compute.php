<?php
// ==========================================================
// Plugins :: zeimoto :: Compute :: Compute
// ==========================================================
//
// Resolves raw derived values for a bound page or site.
// Public return contracts and consumers are documented in README.md.
//
// ==========================================================

declare(strict_types=1);

namespace Zeimoto\Compute;

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\Structure;
use Kirby\Content\Field;
use Kirby\Toolkit\Obj;

final class Compute
{
  // Region for a bare language code: uppercasing the language is right for
  // most (de→DE, fr→FR) but not all, so the known exceptions live here.
  private const REGION_BY_LANGUAGE = ["en" => "US"];

  public function __construct(private readonly Page|Site $model)
  {
  }

  private function site(): Site
  {
    return $this->model instanceof Page ? $this->model->site() : $this->model;
  }

  private function isLegalPage(): bool
  {
    return $this->model instanceof Page && in_array($this->model->intendedTemplate()->name(), ["legal", "policy"], true);
  }

  public function title(): Field
  {
    if ($this->isLegalPage()) {
      return $this->model->title();
    }

    return $this->model
      ->metadataTitle()
      ->or($this->model->title())
      ->or($this->site()->metadataTitle())
      ->or($this->site()->title());
  }

  public function description(): Field
  {
    if ($this->isLegalPage()) {
      return $this->site()->metadataDescription();
    }

    return $this->model->metadataDescription()->or($this->site()->metadataDescription());
  }

  public function plainText(Field $field): string
  {
    $text = preg_replace(
      '~</?(?:address|article|aside|blockquote|br|dd|div|dl|dt|fieldset|figcaption|figure|footer|form|h[1-6]|header|hr|li|main|nav|ol|p|pre|section|table|tbody|td|tfoot|th|thead|tr|ul)\b[^>]*>~i',
      " ",
      $field->value() ?? "",
    );
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, "UTF-8");

    return trim(preg_replace('/[\s\p{Z}]+/u', " ", $text));
  }

  public function canonicalUrl(): string
  {
    if ($this->isLegalPage()) {
      return $this->model->url();
    }

    return $this->model->metadataCanonicalUrl()->or($this->model->url())->value();
  }

  public function schemaType(): string
  {
    if ($this->isLegalPage()) {
      return "WebPage";
    }

    if ($this->model instanceof Page && $this->model->isHomePage()) {
      return "HomePage";
    }

    return $this->model->schemaType()->or("WebPage")->value();
  }

  public function opengraphType(): string
  {
    return match ($this->schemaType()) {
      "Article" => "article",
      "ProfilePage" => "profile",
      default => "website",
    };
  }

  public function opengraphImage(): Obj|null
  {
    $file = $this->isLegalPage()
      ? $this->site()->opengraphImage()->toFile()
      : $this->model->opengraphImage()->toFile() ?? $this->site()->opengraphImage()->toFile();

    if ($file === null) {
      return null;
    }

    $thumb = $file->thumb(["width" => 1200, "height" => 630, "crop" => true]);
    $alt = $file->alt();

    return new Obj([
      "file" => $file,
      "url" => $thumb->url(),
      "width" => $thumb->width(),
      "height" => $thumb->height(),
      "alt" => $alt->isNotEmpty() ? $alt->value() : null,
    ]);
  }

  private function languageCode(string $fallback): string
  {
    return $this->site()->kirby()->language()?->code() ?: $fallback;
  }

  private function languageParts(string $fallback): array
  {
    return explode("-", str_replace("_", "-", $this->languageCode($fallback)));
  }

  public function lang(): string
  {
    return strtolower($this->languageParts("en")[0]);
  }

  public function locale(): string
  {
    $parts = $this->languageParts("en_US");
    $language = strtolower($parts[0]);
    $region = $parts[1] ?? self::REGION_BY_LANGUAGE[$language] ?? $language;

    return $language . "_" . strtoupper($region);
  }

  public function entityType(): string
  {
    return $this->site()->entityType()->or("person")->value();
  }

  public function entityName(): Field
  {
    return $this->site()
      ->entityName()
      ->or($this->site()->title());
  }

  public function entityId(): string
  {
    return $this->site()->url() . "#" . $this->entityType();
  }

  public function entityUrl(): string
  {
    return $this->site()->entityPage()->toPage()?->url() ?? $this->site()->url();
  }

  public function schemaEntityType(): string
  {
    return match ($this->entityType()) {
      "organization" => "Organization",
      "brand" => "Brand",
      default => "Person",
    };
  }

  public function schemaAgentId(): string
  {
    return $this->entityType() === "brand" ? $this->site()->url() . "#organization" : $this->entityId();
  }

  public function schemaAgentType(): string
  {
    return $this->entityType() === "person" ? "Person" : "Organization";
  }

  private function socials(): Structure
  {
    return $this->site()->kirby()->collection("socials");
  }

  public function sameAs(): array
  {
    return $this->socials()->pluck("url");
  }

  public function faviconUrl(int $size): string|null
  {
    return $this->site()
      ->faviconImage()
      ->toFile()
      ?->thumb(["width" => $size, "height" => $size, "crop" => true, "format" => "png"])
      ->url();
  }

  public function manifestDescription(): Field
  {
    return $this->site()
      ->metadataDescription()
      ->or($this->site()->title());
  }

  public function entityShortName(): Field
  {
    return $this->site()->entityShortName()->or($this->entityName());
  }

  public function pwaDisplay(): Field
  {
    return $this->site()->pwaDisplay()->or("standalone");
  }

  public function pwaOrientation(): Field
  {
    return $this->site()->pwaOrientation()->or("portrait");
  }

  public function themeLightMode(): Field
  {
    return $this->site()->themeLightMode()->or("#ffffff");
  }

  public function twitterHandle(): string|null
  {
    foreach ($this->socials() as $social) {
      if ($social->platform() === "x" && $social->username() !== "") {
        return "@" . ltrim($social->username(), "@");
      }
    }

    return null;
  }
}
