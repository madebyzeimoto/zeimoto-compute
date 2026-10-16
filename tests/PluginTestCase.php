<?php
// ==========================================================
// Tests :: Compute Fixture
// ==========================================================
//
// Verifies the documented compute fixture contracts in isolation.
//
// ==========================================================

declare(strict_types=1);

namespace Zeimoto\Compute\Tests;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

abstract class PluginTestCase extends TestCase
{
  protected string $directory;
  private array $apps = [];

  protected function setUp(): void
  {
    $this->directory = sys_get_temp_dir() . "/zeimoto-tests-" . bin2hex(random_bytes(8));
    foreach (["config", "content", "accounts", "sessions", "cache", "media", "plugins", "blueprints/files"] as $name) {
      mkdir($this->directory . "/" . $name, 0700, true);
    }
    foreach (["MAIL_TO", "MAIL_FROM"] as $name) {
      unset($_ENV[$name]);
      putenv($name);
    }
    \Kirby\Email\Email::$debug = true;
    \Kirby\Email\Email::$emails = [];
    $plugin = dirname(__DIR__);
    symlink($plugin, $this->directory . "/plugins/" . basename($plugin));
  }

  protected function tearDown(): void
  {
    foreach ($this->apps as $app) {
      $app->session()->destroy();
    }
    $this->apps = [];
    $remove = function (string $path) use (&$remove): void {
      if (is_link($path) || is_file($path)) {
        unlink($path);
        return;
      }
      foreach (glob($path . "/{,.}*", GLOB_BRACE) ?: [] as $child) {
        if (!in_array(basename($child), [".", ".."], true)) {
          $remove($child);
        }
      }
      rmdir($path);
    };
    $remove($this->directory);
  }

  protected function app(array $options = [], array $site = [], array $extra = []): App
  {
    $extraRoots = $extra["roots"] ?? [];
    unset($extra["roots"]);
    $source = $this->directory;
    $app = new App([
      "roots" => [
        "index" => $this->directory,
        "site" => $source,
        "config" => $this->directory . "/config",
        "content" => $this->directory . "/content",
        "accounts" => $this->directory . "/accounts",
        "sessions" => $this->directory . "/sessions",
        "cache" => $this->directory . "/cache",
        "media" => $this->directory . "/media",
        ...$extraRoots,
        "plugins" => $this->directory . "/plugins",
      ],
      "urls" => ["index" => "http://localhost"],
      "request" => ["url" => "http://localhost"],
      "options" => ["languages" => true, "cache" => false, ...$options],
      "languages" => [
        ["code" => "en", "default" => true, "url" => "http://localhost", "locale" => "en_US"],
        ["code" => "de", "url" => "http://localhost/de", "locale" => "de_DE"],
      ],
      "site" => ["url" => "http://localhost", "content" => ["title" => "Test site"], "children" => [], ...$site],
      ...$extra,
    ]);
    $this->apps[] = $app;
    return $app;
  }
}
