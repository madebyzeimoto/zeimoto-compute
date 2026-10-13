<?php
// ==========================================================
// Tooling :: Kirby IDE Stubs
// ==========================================================
//
// Provides global class aliases for editor analysis only.
//
// ==========================================================

declare(strict_types=1);

/**
 * Kirby global class-alias stubs (IDE only — never loaded at runtime).
 *
 * Kirby registers short global class names (Kirby, Page, Site, Str, A, F, ...)
 * at runtime via class_alias() in kirby/config/setup.php. Intelephense cannot
 * follow that runtime aliasing, so those globals show as "undefined type".
 * These top-level class_alias() calls let Intelephense resolve them to their
 * real classes. This file is NOT in any autoload path and is never required by
 * index.php, so it has no runtime effect. Regenerate from kirby/config/aliases.php.
 */
class_alias(\Kirby\Cms\Collection::class, "Collection");
class_alias(\Kirby\Cms\File::class, "File");
class_alias(\Kirby\Cms\Files::class, "Files");
class_alias(\Kirby\Cms\Find::class, "Find");
class_alias(\Kirby\Cms\Helpers::class, "Helpers");
class_alias(\Kirby\Cms\Html::class, "Html");
class_alias(\Kirby\Cms\App::class, "Kirby");
class_alias(\Kirby\Cms\Page::class, "Page");
class_alias(\Kirby\Cms\Pages::class, "Pages");
class_alias(\Kirby\Cms\Pagination::class, "Pagination");
class_alias(\Kirby\Cms\R::class, "R");
class_alias(\Kirby\Cms\Response::class, "Response");
class_alias(\Kirby\Cms\S::class, "S");
class_alias(\Kirby\Sane\Sane::class, "Sane");
class_alias(\Kirby\Cms\Site::class, "Site");
class_alias(\Kirby\Cms\Structure::class, "Structure");
class_alias(\Kirby\Cms\Url::class, "Url");
class_alias(\Kirby\Cms\User::class, "User");
class_alias(\Kirby\Cms\Users::class, "Users");
class_alias(\Kirby\Cms\Visitor::class, "Visitor");
class_alias(\Kirby\Content\Field::class, "Field");
class_alias(\Kirby\Data\Data::class, "Data");
class_alias(\Kirby\Data\Json::class, "Json");
class_alias(\Kirby\Data\Yaml::class, "Yaml");
class_alias(\Kirby\Filesystem\Asset::class, "Asset");
class_alias(\Kirby\Filesystem\Dir::class, "Dir");
class_alias(\Kirby\Filesystem\F::class, "F");
class_alias(\Kirby\Filesystem\Mime::class, "Mime");
class_alias(\Kirby\Database\Database::class, "Database");
class_alias(\Kirby\Database\Db::class, "Db");
class_alias(\Kirby\Exception\ErrorPageException::class, "ErrorPageException");
class_alias(\Kirby\Http\Cookie::class, "Cookie");
class_alias(\Kirby\Http\Header::class, "Header");
class_alias(\Kirby\Http\Remote::class, "Remote");
class_alias(\Kirby\Image\Dimensions::class, "Dimensions");
class_alias(\Kirby\Panel\Panel::class, "Panel");
class_alias(\Kirby\Template\Snippet::class, "Snippet");
class_alias(\Kirby\Template\Slot::class, "Slot");
class_alias(\Kirby\Toolkit\A::class, "A");
class_alias(\Kirby\Toolkit\Config::class, "Config");
class_alias(\Kirby\Toolkit\Escape::class, "Escape");
class_alias(\Kirby\Toolkit\I18n::class, "I18n");
class_alias(\Kirby\Toolkit\Obj::class, "Obj");
class_alias(\Kirby\Toolkit\Str::class, "Str");
class_alias(\Kirby\Toolkit\Tpl::class, "Tpl");
class_alias(\Kirby\Toolkit\V::class, "V");
class_alias(\Kirby\Toolkit\Xml::class, "Xml");
