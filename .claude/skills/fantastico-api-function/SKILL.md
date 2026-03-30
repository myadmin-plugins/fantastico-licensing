---
name: fantastico-api-function
description: Adds a new procedural function to `src/fantastico.inc.php` following the established pattern: instantiate `Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD)`, call API methods, use `get_module_db('licenses')` + `get_module_settings('licenses')` for DB queries, log with `myadmin_log()`. Use when user says 'add function', 'new API call', 'fetch licenses', or modifies `fantastico.inc.php`. Do NOT use for Plugin class methods or admin page functions in `src/reusable_fantastico.php`/`src/fantastico_list.php`.
---
# fantastico-api-function

## Critical

- **Never use PDO.** Always use `get_module_db('licenses')` for database access.
- **Always escape user/external input** with `$db->real_escape()` before interpolating into queries.
- **Never build INSERT strings manually.** Use `make_insert_query($settings['TABLE'], $data)`.
- All `myadmin_log()` calls **must** include `__LINE__, __FILE__` as the last positional args.
- This skill is only for procedural functions in `src/fantastico.inc.php`. Do NOT apply it to `src/Plugin.php` static methods.
- After adding a new function, register it in `Plugin::getRequirements()` with `$loader->add_requirement('your_function_name', 'src/fantastico.inc.php');`.

## Instructions

1. **Read `src/fantastico.inc.php` first.** Understand existing functions before adding.
   - Verify the file starts with `use Detain\Fantastico\Fantastico;` — do not add a duplicate import.

2. **Write the PHPDoc block** above your function:
   ```php
   /**
    * your_function_name()
    * One-sentence description of what it does.
    *
    * @param mixed $param Description
    * @return array|bool  Return type description
    */
   ```

3. **Instantiate `Fantastico` using the constants** (not variables, not hardcoded strings):
   ```php
   $fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
   ```
   If your function needs DB access, also initialize:
   ```php
   $db = get_module_db('licenses');
   $settings = \get_module_settings('licenses');
   ```
   Verify `$settings['TABLE']` and `$settings['PREFIX']` are used for all table/column references — never hardcode `licenses` or `license_` prefixes in queries.

4. **Call the Fantastico API method.** Available methods on `$fantastico`:
   - `getIpListDetailed(Fantastico::ALL_TYPES)` — returns associative array keyed by IP with `ipAddress`, `addedOn`, `isVPS`, `status`
   - `getIpList(Fantastico::ALL_TYPES)` — returns flat array of IP strings
   - `getIpDetails($ip)` — returns detail array for one IP, includes `isVPS`
   - `addIp($ip, $type)` — activates a new license; returns array with `id` or `faultcode`/`fault` on error
   - `editIp($oldIp, $newIp)` — changes IP; returns array with `faultcode`/`fault` on error

   If calling a list method, log with `request_log()`:
   ```php
   $result = $fantastico->getIpListDetailed(Fantastico::ALL_TYPES);
   request_log('licenses', false, __FUNCTION__, 'fantastico', 'getIpListDetailed', 'Fantastico::ALL_TYPES', $result);
   ```

5. **Run DB queries** using the module DB pattern:
   ```php
   $db->query("SELECT {$settings['PREFIX']}_ip FROM {$settings['TABLE']}
    LEFT JOIN services ON {$settings['PREFIX']}_type = services_id
    WHERE services_module = 'licenses'
    AND services_category = ".get_service_define('FANTASTICO')." AND {$settings['PREFIX']}_status = 'active'", __LINE__, __FILE__);
   while ($db->next_record(MYSQL_ASSOC)) {
       $row = $db->Record;
       // use $row[$settings['PREFIX'].'_ip'] etc.
   }
   ```
   For single-row checks: use `$db->num_rows() == 0` before calling `$db->next_record()`.

6. **Log significant actions** with `myadmin_log()`:
   ```php
   // Info:
   myadmin_log('licenses', 'info', "Message about $variable", __LINE__, __FILE__);
   // Error:
   myadmin_log('licenses', 'error', 'Fantastico methodName() returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__);
   ```

7. **Check for API faults** on mutating calls (`addIp`, `editIp`):
   ```php
   if (isset($result['faultcode'])) {
       myadmin_log('licenses', 'error', 'Fantastico addIp(...) returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__);
       return false;
   }
   ```

8. **Register the function** in `src/Plugin.php` inside `getRequirements()`:
   ```php
   $loader->add_requirement('your_function_name', 'src/fantastico.inc.php');
   ```
   Verify: grep `src/Plugin.php` for your function name to confirm the line was added.

9. **Run tests** to confirm nothing is broken:
   ```bash
   vendor/bin/phpunit tests/PluginTest.php
   ```

## Examples

**User says:** "Add a function to get all active Fantastico licenses from the DB joined with the API."

**Actions taken:**
1. Read `src/fantastico.inc.php` — confirmed `use Detain\Fantastico\Fantastico;` present.
2. Added function at end of file:

```php
/**
 * get_active_fantastico_licenses()
 * Returns active Fantastico licenses from the DB cross-referenced with the API.
 *
 * @return array
 */
function get_active_fantastico_licenses()
{
    $db = get_module_db('licenses');
    $settings = \get_module_settings('licenses');
    $fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
    $ips = $fantastico->getIpList(Fantastico::ALL_TYPES);
    $db->query("SELECT {$settings['PREFIX']}_ip, {$settings['PREFIX']}_status
 FROM {$settings['TABLE']}
 LEFT JOIN services ON {$settings['PREFIX']}_type = services_id
 WHERE services_module = 'licenses'
 AND services_category = ".get_service_define('FANTASTICO')."  AND {$settings['PREFIX']}_status = 'active'
 AND {$settings['PREFIX']}_ip IN ('".
implode("','", $ips)."')", __LINE__, __FILE__);
    $rows = [];
    while ($db->next_record(MYSQL_ASSOC)) {
        $rows[] = $db->Record;
    }
    myadmin_log('licenses', 'info', 'get_active_fantastico_licenses returned '.count($rows).' rows', __LINE__, __FILE__);
    return $rows;
}
```

3. Added to `Plugin::getRequirements()`:
```php
$loader->add_requirement('get_active_fantastico_licenses', 'src/fantastico.inc.php');
```

4. Ran `vendor/bin/phpunit tests/PluginTest.php` — all passed.

**Result:** New function follows the same DB/API/log pattern as `get_reusable_fantastico()` and is discoverable via the loader.

## Common Issues

- **`FANTASTICO_USERNAME` / `FANTASTICO_PASSWORD` undefined:** These are constants set via `Plugin::getSettings()`. They require a running MyAdmin environment with settings configured. In tests or bin scripts, ensure `include/functions.inc.php` is loaded first.

- **`get_module_settings()` returns null prefix:** Means `'licenses'` module is not registered in `$GLOBALS['modules']`. Wrap DB queries in `if (isset($GLOBALS['modules']['licenses'])) { ... }` when the function may run outside a full MyAdmin bootstrap (see `get_fantastico_list()` for the pattern).

- **`make_insert_query()` not found:** Call `function_requirements('make_insert_query')` before using it, or ensure `include/functions.inc.php` is loaded via the bin script bootstrap.

- **`$db->next_record(MYSQL_ASSOC)` returns false on first call:** You forgot to call `$db->query(...)` first, or the query returned 0 rows. Always check `$db->num_rows() > 0` before iterating if the result is optional.

- **API call returns `faultcode` unexpectedly:** Run `php bin/fantastico_licenses.php` to verify credentials work end-to-end. Check that `FANTASTICO_USERNAME` / `FANTASTICO_PASSWORD` constants match what's stored in the admin settings panel.

- **`implode()` in query with empty `$ips` array:** `getIpList()` can return an empty array. Guard with `if (empty($ips)) { return []; }` before building `IN (...)` queries to avoid a SQL syntax error.
