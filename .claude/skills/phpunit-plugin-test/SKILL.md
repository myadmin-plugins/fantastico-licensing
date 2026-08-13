---
name: phpunit-plugin-test
description: Writes PHPUnit 9 tests in `tests/` for the Fantastico licensing plugin following the `PluginTest.php` and `SourceFileAnalysisTest.php` patterns. Use when user says 'add test', 'write tests', 'test the plugin', 'write a test for', or when modifying `Plugin.php` or `src/*.php` files. Covers reflection-based structural tests and static file analysis via `file_get_contents`. Do NOT use for integration tests requiring a live DB, SOAP API calls, or MyAdmin environment bootstrapping. For a plugin's contract or behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
---
<!-- myadmin-contract-harness-notice -->
> ### ⚠️ Read this before the rest of the file
>
> This package is on the **shared plugin contract harness**. Parts of the guidance below
> predate it and are now wrong in one specific way:
>
> **Any instruction here that a plugin's `getHooks()` / `getSettings()` / `getActivate()` /
> `getDeactivate()` / `getQueue()` must not be *called* — that only its existence, visibility
> or parameter count may be checked through `ReflectionClass` — no longer applies.** That rule
> existed because those methods reference bare constants (`PRORATE_BILLING` and friends) that
> only a live MyAdmin request defines, so calling them from a test used to fatal. The harness
> defines them first. It then executes the handlers for real, in a process of its own.
>
> A reflection-only assertion passes whether or not the thing works: `getActivate()` can exist,
> be public, be static, take one argument, and still fatal the moment it runs. Three real
> production bugs in this fleet were sitting behind assertions of exactly that shape.
>
> **Use the `plugin-contract-tests` skill** for anything touching `tests/ContractTest.php`,
> the contract inspectors, or `composer myadmin:scaffold-tests`.
>
> **Everything else in this file is still accurate and still applies** — this package's own
> classes, its API wrappers, its fixtures, its bootstrap, and the reasons certain classes must
> not be constructed. Nothing below has been removed.

# PHPUnit Plugin Test

## Critical

- Tests run with `phpunit` from the package root (config: `phpunit.xml.dist`, bootstrap: `tests/bootstrap.php`).
- All test classes MUST be in namespace `Detain\MyAdminFantastico\Tests` and extend `PHPUnit\Framework\TestCase`.
- Never instantiate live DB connections or SOAP clients. No `myadmin_log`, `get_module_db`, or `Fantastico` object creation in tests — use `ReflectionClass` or `file_get_contents` instead.
- `phpunit.xml.dist` has `failOnWarning="true"` and `beStrictAboutOutputDuringTests="true"`. Tests that echo output or produce PHP warnings will fail.
- Test file must be placed in `tests/` and named `*Test.php` to be auto-discovered.

## Instructions

### Step 1 — Decide test type

Two patterns exist:
- **Reflection tests** (`tests/PluginTest.php`): For `src/Plugin.php` structural contracts — static properties, method signatures, `getHooks()` map shape.
- **Source analysis tests** (`tests/SourceFileAnalysisTest.php`): For `src/*.php` procedural files — function existence, `use` statements, access guards, via `file_get_contents` + `assertMatchesRegularExpression` / `assertStringContainsString`.

Verify which `src/` file is being tested before proceeding.

### Step 2 — Create the test class

File: e.g. `tests/PluginTest.php`

```php
<?php

namespace Detain\MyAdminFantastico\Tests;

use Detain\MyAdminFantastico\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\GenericEvent;

class PluginTest extends TestCase
{
    /** @var ReflectionClass */
    private $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }
}
```

For source analysis tests, omit the `ReflectionClass` import and use a static `$srcDir` instead:

```php
private static $srcDir;

public static function setUpBeforeClass(): void
{
    self::$srcDir = dirname(__DIR__) . '/src';
}
```

Verify `tests/bootstrap.php` exists before adding the file — it resolves `vendor/autoload.php` automatically.

### Step 3 — Write reflection tests for src/Plugin.php

Each test must be a single focused `public function testXxx(): void` with a docblock.

**Static property pattern** — verify existence, visibility, and value:
```php
public function testModuleProperty(): void
{
    $this->assertTrue($this->reflection->hasProperty('module'));
    $prop = $this->reflection->getProperty('module');
    $this->assertTrue($prop->isStatic());
    $this->assertTrue($prop->isPublic());
    $this->assertSame('licenses', Plugin::$module);
}
```
Properties to cover: `$name`, `$description`, `$help`, `$module`, `$type`.

**Hook map tests** — assert key presence and exact `[ClassName, 'methodName']` callback:
```php
public function testGetHooksContainsActivate(): void
{
    $hooks = Plugin::getHooks();
    $this->assertArrayHasKey('licenses.activate', $hooks);
    $this->assertSame([Plugin::class, 'getActivate'], $hooks['licenses.activate']);
}
```
Required hook keys: `function.requirements`, `licenses.settings`, `licenses.activate`, `licenses.reactivate`, `licenses.change_ip`, `ui.menu`. Total count must be `assertCount(6, $hooks)`.

**Method signature test** — all event handlers accept exactly one `GenericEvent` param:
```php
public function testGetActivateMethodSignature(): void
{
    $method = $this->reflection->getMethod('getActivate');
    $this->assertTrue($method->isStatic());
    $this->assertTrue($method->isPublic());
    $this->assertSame(1, $method->getNumberOfParameters());
    $params = $method->getParameters();
    $this->assertSame(GenericEvent::class, $params[0]->getType()->getName());
}
```
Apply to: `getActivate`, `getChangeIp`, `getMenu`, `getRequirements`, `getSettings`.

### Step 4 — Write source analysis tests for src/*.php

Use `file_get_contents` — no `require`. Pattern:
```php
public function testIncFileDefinesActivateFantastico(): void
{
    $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
    $this->assertMatchesRegularExpression(
        '/function\s+activate_fantastico\s*\(\s*\$\w+\s*,\s*\$\w+\s*\)/',
        $content
    );
}
```

Standard checks per file:
- File existence: `$this->assertFileExists(self::$srcDir . '/filename.php')`
- Namespace/use: `assertStringContainsString('use Detain\\Fantastico\\Fantastico;', $content)`
- Admin guard: `assertStringContainsString("$GLOBALS['tf']->ima == 'admin'", $content)`
- Function count: `preg_match_all('/^\s*function\s+\w+\s*\(/m', $content, $matches); $this->assertCount(N, $matches[0]);`

### Step 5 — Run tests

```bash
phpunit
```

Or for a single file:
```bash
phpunit tests/PluginTest.php
```

All tests must be green before committing. Fix any `failOnWarning` failures before marking done.

## Examples

**User says:** "Add tests for the `getChangeIp` hook in src/Plugin.php"

**Actions taken:**
1. Read `src/Plugin.php` to confirm `getChangeIp` signature: `public static function getChangeIp(GenericEvent $event)`
2. Read `tests/PluginTest.php` to find existing hook test pattern
3. Add to `tests/PluginTest.php`:

```php
/**
 * Tests that getHooks() contains the licenses.change_ip hook.
 */
public function testGetHooksContainsChangeIp(): void
{
    $hooks = Plugin::getHooks();
    $this->assertArrayHasKey('licenses.change_ip', $hooks);
    $this->assertSame([Plugin::class, 'getChangeIp'], $hooks['licenses.change_ip']);
}

/**
 * Tests that getChangeIp() accepts exactly one parameter of type GenericEvent.
 */
public function testGetChangeIpMethodSignature(): void
{
    $method = $this->reflection->getMethod('getChangeIp');
    $this->assertTrue($method->isStatic());
    $this->assertTrue($method->isPublic());
    $this->assertSame(1, $method->getNumberOfParameters());
    $params = $method->getParameters();
    $this->assertSame(GenericEvent::class, $params[0]->getType()->getName());
}
```

4. Run `phpunit tests/PluginTest.php` — both new tests pass.

**Result:** Two focused tests covering hook registration and method signature for `getChangeIp`.

## Common Issues

**`Class 'Detain\MyAdminFantastico\Plugin' not found`**
- Run `composer install` first. The autoloader at `vendor/autoload.php` must exist.
- Verify `tests/bootstrap.php` resolves one of `vendor/autoload.php` or `../../../autoload.php`.

**`Error: Output during test` / test marked risky**
- A `var_dump`, `echo`, or `print_r` leaked into test output. Remove all output statements — `phpunit.xml.dist` sets `beStrictAboutOutputDuringTests="true"`.

**`TypeError: Return value of ... must be of type string, null returned`**
- `$params[0]->getType()` returned null. The method parameter has no type hint in `src/Plugin.php`. Add the `GenericEvent` type hint to the handler: `public static function getXxx(GenericEvent $event)`.

**`Failed asserting that 6 matches expected count`** in `testGetHooksCount`
- `getHooks()` was modified. Update both `assertCount(N, $hooks)` and the individual `assertArrayHasKey` tests to match the new hook map.

**`PHPUnit\Framework\Exception: Class ... does not exist`** in `ReflectionClass` constructor
- Namespace mismatch. Confirm top of test file has `use Detain\MyAdminFantastico\Plugin;` and Plugin's namespace is `namespace Detain\MyAdminFantastico;`.

**`assertMatchesRegularExpression` not found**
- Using PHPUnit < 9. This project requires PHPUnit 9 (`vendor/bin/phpunit --version`). Run `composer install` to restore correct version. Do not use the deprecated `assertRegExp`.
