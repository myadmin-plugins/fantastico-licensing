---
name: admin-page-function
description: Creates a new admin page function in `src/` following `reusable_fantastico.php` / `fantastico_licenses_list.php` patterns: PHP file with named function, `page_title()`, admin check via `$GLOBALS['tf']->ima == 'admin'`, `TFTable` for tabular output, `add_output()` for rendering, and `render_form()` for form-based pages. Registers the page in `Plugin::getRequirements()` via `$loader->add_page_requirement()` and optionally adds a menu link in `Plugin::getMenu()`. Use when user says 'add admin page', 'new page', 'list view', or adds files to `src/`. Do NOT use for non-admin pages, API endpoints, or hook handlers.
---
# Admin Page Function

## Critical

- **Always** gate the entire page body with `if ($GLOBALS['tf']->ima == 'admin')` — never render output for non-admins.
- **Never** echo directly — always use `add_output()` to buffer content.
- **Never** build INSERT strings manually — use `make_insert_query($table, $assoc)` for all inserts.
- **Always** escape user input with `$db->real_escape()` before using it in a query.
- The function name **must exactly match** the filename (e.g. `src/reusable_fantastico.php` → `function reusable_fantastico()`).
- `$loader->add_page_requirement()` path uses the `src/` directory prefix — **not** an absolute path.

## Instructions

### Step 1 — Create the page file in `src/`

Create the page file in `src/` with this boilerplate:

```php
<?php
/**
 * Fantastico Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2025
 * @package MyAdmin
 * @category Licenses
 */

use Detain\Fantastico\Fantastico;

function page_function_name()
{
    page_title('<Human Readable Title>');
    if ($GLOBALS['tf']->ima == 'admin') {
        $module = 'licenses';
        $db = get_module_db($module);
        $settings = \get_module_settings($module); // provides PREFIX, TABLE, TBLNAME

        // ... page logic ...

        add_output($table->get_table());
    }
}
```

Verify: function name matches filename exactly before proceeding.

### Step 2 — Choose the rendering pattern

**Pattern A — TFTable (data list, like `src/fantastico_licenses_list.php`):**

```php
$table = new \TFTable();
$table->set_title('My Table Title');
$header = false;
foreach ($rows as $data) {
    if (!$header) {
        foreach (array_keys($data) as $field) {
            $table->add_field(ucwords(str_replace('_', ' ', $field)));
        }
        $table->add_row();
        $header = true;
    }
    foreach (array_values($data) as $field) {
        $table->add_field($field);
    }
    $table->add_row();
}
add_output($table->get_table());
```

**Pattern B — TFTable with form input (like `src/reusable_fantastico.php`):**

```php
add_output('<h3>Section Heading</h3>');
$table = new \TFTable();
$table->add_hidden('add', 1);
$table->set_title('Form Title');
$table->add_field('Label');
$table->add_field($table->make_input('field_name', '', 20));
$table->add_field($table->make_submit('Submit'));
$table->add_row();
add_output($table->get_table());
add_output(render_form('page_function_name'));
```

**Pattern C — Pure Smarty form (like `src/fantastico_list.php`):**

```php
add_output(render_form('page_function_name'));
```

Verify: you know whether the page needs a data table, input form, or Smarty template before choosing.

### Step 3 — Handle form submission (Pattern B only)

Add this block **before** the output section, inside the `if ($GLOBALS['tf']->ima == 'admin')` block:

```php
if (isset($GLOBALS['tf']->variables->request['add']) && $GLOBALS['tf']->variables->request['add'] == 1) {
    $field = $db->real_escape($GLOBALS['tf']->variables->request['field_name']);
    // validate, then insert:
    $db->query(make_insert_query($settings['TABLE'], [
        $settings['PREFIX'].'_id'         => null,
        $settings['PREFIX'].'_custid'     => 8,
        $settings['PREFIX'].'_order_date' => mysql_now(),
        $settings['PREFIX'].'_status'     => 'active',
        // ... other columns ...
    ]), __LINE__, __FILE__);
    // on validation error:
    // dialog('Error', 'Descriptive message here');
}
```

Verify: every `$_REQUEST` value is passed through `$db->real_escape()` before use.

### Step 4 — Register the page in `Plugin::getRequirements()`

Open `src/Plugin.php` and add inside `getRequirements()`, alongside the existing `add_page_requirement` calls:

```php
$loader->add_page_requirement('page_function_name', 'src/page_function_name.php');
```

Verify: the path starts with `src/` and the filename matches the function name exactly.

### Step 5 — Add a menu link in `Plugin::getMenu()` (optional but typical)

Inside `getMenu()`, within the `if ($GLOBALS['tf']->ima == 'admin')` block:

```php
$menu->add_link(self::$module, 'choice=none.page_function_name', '/images/myadmin/list.png', _('Menu Label'));
```

Verify: `choice=none.page_function_name` uses the exact function/file name registered in Step 4.

## Examples

**User says:** "Add an admin page that lists all canceled Fantastico licenses."

**Actions taken:**

1. Create `src/fantastico_canceled_list.php`:
```php
<?php
/**
 * Fantastico Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2025
 * @package MyAdmin
 * @category Licenses
 */

use Detain\Fantastico\Fantastico;

function fantastico_canceled_list()
{
    page_title('Canceled Fantastico Licenses');
    if ($GLOBALS['tf']->ima == 'admin') {
        $module = 'licenses';
        $db = get_module_db($module);
        $settings = \get_module_settings($module);
        $db->query("SELECT * FROM {$settings['TABLE']} WHERE {$settings['PREFIX']}_status = 'canceled'", __LINE__, __FILE__);
        $table = new \TFTable();
        $table->set_title('Canceled Fantastico Licenses');
        $header = false;
        while ($db->next_record(MYSQL_ASSOC)) {
            $data = $db->Record;
            if (!$header) {
                foreach (array_keys($data) as $field) {
                    $table->add_field(ucwords(str_replace('_', ' ', $field)));
                }
                $table->add_row();
                $header = true;
            }
            foreach (array_values($data) as $field) {
                $table->add_field($field);
            }
            $table->add_row();
        }
        add_output($table->get_table());
    }
}
```

2. In `src/Plugin.php` → `getRequirements()`:
```php
$loader->add_page_requirement('fantastico_canceled_list', 'src/fantastico_canceled_list.php');
```

3. In `src/Plugin.php` → `getMenu()`:
```php
$menu->add_link(self::$module, 'choice=none.fantastico_canceled_list', '/images/myadmin/list.png', _('Canceled Fantastico Licenses'));
```

**Result:** Page is accessible at `?choice=none.fantastico_canceled_list`, visible only to admins, listed in the licenses module menu.

## Common Issues

**Page renders blank / no output for admin users:**
- The function name does not match the filename. For example, `src/reusable_fantastico.php` must contain `function reusable_fantastico()` — check both.
- `add_page_requirement` key does not match the function name — `choice=none.X` dispatches to function `X()`.

**"Undefined function" error on page load:**
- The `add_page_requirement()` path is wrong. Verify it starts with `src/` and the filename matches exactly (case-sensitive on Linux).

**Form submission silently does nothing:**
- Check `isset($GLOBALS['tf']->variables->request['add'])` — POST/GET values come from `$GLOBALS['tf']->variables->request`, not `$_POST` or `$_GET`.

**SQL error on insert:**
- Never interpolate raw request values. Always call `$db->real_escape()` first, or use `make_insert_query()` which handles escaping.
- `make_insert_query()` requires the full column name including prefix (e.g. `license_ip`, not `ip`).

**Menu link not appearing:**
- The `getMenu()` handler checks `$GLOBALS['tf']->ima == 'admin'` — confirm you are logged in as admin.
- `$menu->add_link()` first argument must match `self::$module` (`'licenses'`), not a custom string.

**`page_title()` not called for form-only pages (Pattern C):**
- `src/fantastico_list.php` omits `page_title()` — this is valid for pages that delegate entirely to `render_form()`. Only call `page_title()` when the page has its own heading.
