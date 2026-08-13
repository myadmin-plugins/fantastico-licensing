# Fantastico Licensing Plugin

MyAdmin plugin (`detain/myadmin-fantastico-licensing`) for selling, activating, and managing Fantastico licenses for dedicated servers and VPS instances via SOAP API.

## Commands

```bash
composer install                          # install deps
vendor/bin/phpunit                        # run all tests
vendor/bin/phpunit tests/PluginTest.php   # run plugin tests only
php bin/fantastico_licenses.php           # list all licenses (requires MyAdmin env)
php bin/activate_fantastico.php           # test activation (requires MyAdmin env)
```

## Architecture

**Namespace:** `Detain\MyAdminFantastico\` → `src/` · **Tests:** `Detain\MyAdminFantastico\Tests\` → `tests/`

**Entry point:** `src/Plugin.php` — static class with `getHooks()` returning Symfony EventDispatcher hook map

**CI/CD:** `.github/` contains automated test workflows (`workflows/tests.yml`) · `.idea/` stores IDE configuration including `inspectionProfiles/`, `deployment.xml`, and `encodings.xml`

**Hook map** (from `Plugin::getHooks()`):
- `function.requirements` → `getRequirements()` — registers page/function loader paths
- `licenses.settings` → `getSettings()` — adds admin settings for `FANTASTICO_USERNAME` / `FANTASTICO_PASSWORD`
- `licenses.activate` + `licenses.reactivate` → `getActivate()` — calls `activate_fantastico($ip, $type)`
- `licenses.change_ip` → `getChangeIp()` — calls `$fantastico->editIp($old, $new)`
- `ui.menu` → `getMenu()` — adds admin links for `reusable_fantastico`, `fantastico_list`, `fantastico_licenses_list`

**Procedural functions** (`src/fantastico.inc.php`):
- `get_fantastico_licenses()` — calls `Fantastico::getIpListDetailed(Fantastico::ALL_TYPES)`
- `get_fantastico_list()` — joins Fantastico API data with `licenses` and `vps` DB tables
- `get_available_fantastico($type)` — finds reusable IPs from canceled/expired licenses
- `activate_fantastico($ipAddress, $type)` — reuses or creates license via `addIp()`/`editIp()`
- `get_reusable_fantastico()` — returns list of reusable license IPs

**Admin pages** (`src/`):
- `src/fantastico_licenses_list.php` — `fantastico_licenses_list()` renders TFTable of all API licenses
- `src/fantastico_list.php` — `fantastico_list()` renders `render_form('fantastico_list')`
- `src/reusable_fantastico.php` — `reusable_fantastico()` handles add/display of reusable IPs

**Bin scripts** (`bin/`):
- `bin/activate_fantastico.php` — manual activation test, requires `include/functions.inc.php`
- `bin/fantastico_licenses.php` — dumps `get_fantastico_licenses()` output

## Key Patterns

**Hook handler signature** (all handlers in `src/Plugin.php`):
```php
public static function getActivate(GenericEvent $event) {
    $serviceClass = $event->getSubject();
    if ($event['category'] == get_service_define('FANTASTICO')) {
        myadmin_log(self::$module, 'info', 'message', __LINE__, __FILE__, self::$module, $serviceClass->getId());
        // ... do work ...
        $event->stopPropagation();
    }
}
```

**DB pattern** (used in `src/fantastico.inc.php` and `src/reusable_fantastico.php`):
```php
$db = get_module_db('licenses');
$settings = get_module_settings('licenses'); // provides PREFIX, TABLE, TBLNAME
$db->query("SELECT * FROM {$settings['TABLE']} WHERE ...", __LINE__, __FILE__);
while ($db->next_record(MYSQL_ASSOC)) { $row = $db->Record; }
$db->query(make_insert_query($settings['TABLE'], $data), __LINE__, __FILE__);
```

**Fantastico API instantiation:**
```php
$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
$ips = $fantastico->getIpList(Fantastico::ALL_TYPES);
$result = $fantastico->editIp($oldIp, $newIp);
$result = $fantastico->addIp($ipAddress, $type); // type 1=dedicated, 2=VPS
if (isset($result['faultcode'])) { /* handle error */ }
```

**Admin page function structure** (`src/reusable_fantastico.php` pattern):
```php
function my_page_function() {
    page_title('Page Title');
    if ($GLOBALS['tf']->ima == 'admin') {
        $table = new \TFTable();
        $table->set_title('...');
        $table->add_field('...');
        $table->add_row();
        add_output($table->get_table());
    }
}
```

**Loader registration** (`getRequirements()` in `src/Plugin.php`):
```php
$loader->add_requirement('func_name', 'src/fantastico.inc.php');
$loader->add_page_requirement('page_name', 'src/page_file.php');
```

## Conventions

- All hook handlers: `public static function`, accept `GenericEvent $event`, call `$event->stopPropagation()` at end
- Logging: `myadmin_log(self::$module, 'info'|'error', $msg, __LINE__, __FILE__)` — always include line/file
- Service category check: always guard with `$event['category'] == get_service_define('FANTASTICO')`
- Admin-only pages: always check `$GLOBALS['tf']->ima == 'admin'` before rendering
- Commit messages: lowercase descriptive (`fix activation`, `update ip change handler`)
- Never use PDO — always `get_module_db()` pattern
- User input escaping: `$db->real_escape($input)` before interpolating into queries

## Tests

- `tests/PluginTest.php` — uses `ReflectionClass` to verify `Plugin` static properties, method signatures, hook map
- `tests/SourceFileAnalysisTest.php` — static analysis of source files
- `tests/bootstrap.php` — tries `vendor/autoload.php` then `../../../autoload.php`
- PHPUnit config: `phpunit.xml.dist`

## Settings Added by This Plugin

| Setting key | Type | Constant |
|---|---|---|
| `fantastico_username` | text | `FANTASTICO_USERNAME` |
| `fantastico_password` | password | `FANTASTICO_PASSWORD` |
| `outofstock_licenses_fantastico` | dropdown | `OUTOFSTOCK_LICENSES_FANTASTICO` |

<!-- caliber:managed:pre-commit -->
## Plugin contract harness

This package is on the shared contract harness from `detain/myadmin-plugin-installer`.
`tests/ContractTest.php` is **generated** — run `composer myadmin:scaffold-tests` (add
`--force --write` to re-emit it), never hand-edit it.

The harness **executes** the plugin: it defines the bare constants the class body references
and then calls `getHooks()`, `getSettings()`, `getMenu()`, `apiRegister()` and — for
`type=service` packages — the activate/deactivate/change-ip/queue handlers, for real.

**So do not write reflection-only tests for the plugin class.** Asserting a handler exists,
is public, is static and takes one parameter passes whether or not the handler works; three
production bugs in this fleet were sitting behind assertions of exactly that shape. Older
guidance in this repo that says those methods must not be called predates the harness.

The harness is **additive**: it runs alongside this package's existing tests, and nothing is
deleted to make room for it. Run the whole suite, never `--filter ContractTest` alone — the
contract class primes constants and calls `register_module()`, neither of which can be undone.

See the `plugin-contract-tests` skill for the full workflow, and `docs/testing-harness.md` in
the installer.

## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md .agents/ .opencode/ 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->

<!-- caliber:managed:sync -->
## Context Sync

This project uses [Caliber](https://github.com/caliber-ai-org/ai-setup) to keep AI agent configs in sync across Claude Code, Cursor, Copilot, and Codex.
Configs update automatically before each commit via `caliber refresh`.
If the pre-commit hook is not set up, run `/setup-caliber` to configure everything automatically.
<!-- /caliber:managed:sync -->
