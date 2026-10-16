# zeimoto Compute

`zeimoto/compute` owns derived values for Kirby pages and the site: metadata
fallbacks, identity, social profiles, language formatting, and PWA settings.
It returns raw values; consumers escape or encode them for their output context.

## Installation

Requires PHP 8.5 and Kirby 5.6.1 or later within Kirby 5.
The Composer package is `zeimoto/compute` and installs into
`site/plugins/zeimoto-compute`. Release versions come from Git tags.

Once published on Packagist:

```sh
composer require zeimoto/compute
```

For manual installation, copy this plugin into `site/plugins/zeimoto-compute`.
See the integration requirements below before using its public surfaces.
The plugin is distributed under the MIT license; see [LICENSE](LICENSE).

## Page rendering

The `page.render:before` hook injects a `Zeimoto\Compute\Compute` instance bound
to the rendered page as `$compute`. Templates and snippets in that render context
can use it directly:

```php
<title><?= $compute->title()->html() ?></title>
<link rel="canonical" href="<?= html($compute->canonicalUrl()) ?>">
```

Injection happens during page rendering. Standalone snippet calls and routes
must supply their own instance when no page-render context exists.

## Explicit model binding

Use `compute($model)` with a Kirby `Page` or `Site` outside page rendering, or
when resolving a different model:

```php
$siteCompute = compute($site);
$description = $siteCompute->description()->value();

foreach ($page->children() as $child) {
  $childCompute = compute($child);
  $title = $childCompute->title()->value();
}
```

Page-bound metadata resolves the page's values with site fallbacks. Site-bound
metadata resolves the site's values. Shared website and entity schema descriptions
therefore use `compute($site)->description()`, while page schema uses the
page-bound instance. Identity and PWA methods always resolve the owning site.

## Public methods

| Methods                                | Return and behavior                                                                                                                                                               |
| -------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `title()`, `description()`             | `Field`; metadata title falls back through model title, site metadata title, and site title; metadata description falls back to the site's metadata description                   |
| `plainText(Field $field)`              | `string`; strips HTML, preserves block and line-break word boundaries, decodes entities, and normalizes whitespace                                                                |
| `canonicalUrl()`                       | `string`; metadata canonical URL, falling back to the model URL                                                                                                                   |
| `schemaType()`, `opengraphType()`      | `string`; home always resolves to `HomePage`; other schema types default to `WebPage`; Open Graph maps `Article` to `article`, `ProfilePage` to `profile`, and other types to `website`                                     |
| `opengraphImage()`                     | `Obj` or `null`; model image falls back to site image and requests a 1200×630 crop; exposes `file`, `url`, actual `width` and `height`, and optional `alt`                        |
| `lang()`, `locale()`                   | `string`; lowercase language and formatted `language_REGION`, defaulting to `en` and `en_US`; bare English maps to US and other bare codes use their uppercase language as region |
| `entityType()`                         | `string`; site entity type, defaulting to `person`                                                                                                                                |
| `schemaEntityType()`                   | `string`; Schema.org type of the entity node: `Person`, `Organization`, or `Brand`                                                                                                |
| `entityName()`, `entityShortName()`    | `Field`; entity name falls back to site title; short name falls back to resolved entity name                                                                                      |
| `entityId()`, `entityUrl()`            | `string`; site URL plus the entity-type fragment; selected `entityPage` URL falls back to site URL                                                                                |
| `schemaAgentId()`, `schemaAgentType()` | `string`; a brand's agent uses the site's organization ID; agent type is `Person` for a person, otherwise `Organization`                                                          |
| `sameAs()`                             | `array`; profile URLs from the host's `socials` collection                                                                                                                        |
| `twitterHandle()`                      | `string` or `null`; first nonempty X username with one leading `@`                                                                                                                |
| `faviconUrl(int $size)`                | `string` or `null`; square PNG thumbnail URL, absent when no site favicon is selected                                                                                             |
| `manifestDescription()`                | `Field`; site metadata description falls back to site title                                                                                                                       |
| `pwaDisplay()`, `pwaOrientation()`     | `Field`; site PWA settings default to `standalone` and `portrait`                                                                                                                 |
| `themeLightMode()`                     | `Field`; site light theme color defaults to `#ffffff`                                                                                                                             |

Fields may be empty where no fallback is defined. Check nullable image and icon
results before emitting markup. Returned fields and strings are unescaped:
use `->html()` or `html()` for HTML, XML encoding for XML, and JSON encoding for
JSON. Embedded JSON-LD must use flags that prevent a literal `</script>` from
terminating its script element. Plain-text conversion is not output escaping.

Legal and Policy pages always resolve their page title, site metadata description and sharing image,
their own URL, and the `WebPage` schema type (`website` for Open Graph). Saved
page metadata overrides are ignored. Legal pages, Policies, and their descendants are never indexable.

## Registered model methods

Two methods intentionally remain on Kirby models:

- `$page->isIndexable(): bool` returns false when site indexing is disabled or unset,
  the page uses the `system`, `legal`, or `policy` template, the page disables indexing,
  an ancestor uses the `legal` or `policy` template, or an ancestor disables child indexing.
- `$site->computedIndexingStatusLabel(): string` returns `Enabled` or `Disabled`
  for the Panel blueprint query `{{ site.computedIndexingStatusLabel }}`; an
  unset site toggle reads as `Disabled`.

Other derived values belong on `Compute`. Direct field reads and one-off Kirby
conversions stay with their consumers when no derived domain value is involved.

## Integration requirements

The plugin uses Kirby's page/site models, content fields, current language, and
image processing. It declares no plugin options and needs no frontend build.
The host owns the content blueprints, routes, and rendering snippets; this plugin
does not register those surfaces.

`sameAs()` and `twitterHandle()` require the host's named `socials` collection.
The kit supplies it in `site/collections/socials.php`. Manual integrations must
register an iterable collection whose entries provide `url()`, `platform()`, and
`username()`; X entries use platform `x`. An empty collection represents no
configured profiles. The plugin does not provide a fallback for a missing collection.

## Entity Page migration

The Identity field is `entityPage` (`fields/entity/page.yml`, label **Entity Page**). `entityUrl()` remains the computed URL derived from that selection, with the site URL as fallback.

For host copies that previously stored `entityUrl`, stop Panel editing and migrate before saving through the renamed blueprint:

```sh
php tools/migrate-entity-page.php
php tools/migrate-entity-page.php --apply --backup-dir=/absolute/path/to/backups
```

The command inspects root `site.txt` and language-specific `site.*.txt` files. Use `--content-root=/absolute/path/to/content` for another installation. Dry runs do not write files. Applying requires an external backup directory; each run creates a separate backup folder containing the original files. Different non-empty old/new selections block the entire run with exit code 2. Missing old keys are skipped, matching duplicates are cleaned up, and reruns are harmless. Other failures return exit code 1.

Migration preserves selected page IDs/UUIDs and other decoded field values; Kirby’s TXT encoder normalizes file formatting. Restore affected files from the reported backup folder if rollback is needed, together with the previous blueprint and Compute implementation. No runtime alias reads the retired field.

## Development environment

Open `kirby.code-workspace` while this plugin is installed under the zeimoto Kirby
Kit's `site/plugins/` directory. The workspace uses the kit's Composer and npm
dependencies, shared formatting rules, and Kirby classes for autocomplete. IDE
stubs are included; local editor settings, caches, private notes, and `.env` are
ignored. The formatter launcher currently requires Herd's PHP 8.5 on macOS.

Install Composer and npm dependencies at the kit root. From that root, check this
plugin with:

```sh
node tools/check-style.mjs --plugin site/plugins/zeimoto-compute
```

## Behavior tests

Run `composer install` and `composer test` from this repository. Tests boot Kirby
with disposable runtime directories and never load project secrets or send real
mail. The host kit can also run this suite with `composer test:plugins`.
