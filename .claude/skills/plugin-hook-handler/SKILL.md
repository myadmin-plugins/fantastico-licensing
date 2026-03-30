---
name: plugin-hook-handler
description: Adds a new Symfony EventDispatcher hook handler to `src/Plugin.php`. Generates the static method with `GenericEvent $event` signature, category guard, `myadmin_log()` call, and `stopPropagation()`. Also updates `getHooks()` map and `getRequirements()` loader. Use when user says 'add hook', 'handle event', 'new listener', or modifies `Plugin.php`. Do NOT use for non-hook logic like procedural functions in `src/fantastico.inc.php` or admin page renderers.
---
# Plugin Hook Handler

## Critical

- **Never** skip the `get_service_define('FANTASTICO')` category guard — every handler that acts on a specific service type MUST check `$event['category'] == get_service_define('FANTASTICO')` before doing any work.
- **Always** call `$event->stopPropagation()` as the last statement inside the category guard block. Omitting it causes other plugins to double-process the same event.
- **Always** call `myadmin_log()` at the start of the guarded block, before any API calls, so failures are traceable.
- The `getHooks()` map and the handler method must be updated together — registering a hook with no handler (or the reverse) causes a fatal callback error at dispatch time.
- If the handler calls a procedural function from `src/fantastico.inc.php`, load it first with `function_requirements('function_name')` — do not assume it is already in scope.

## Instructions

### Step 1 — Identify the event name and handler method name

Choose an event name following the pattern `{module}.{action}` (e.g., `licenses.deactivate`). Choose a method name in PascalCase prefixed with `get` (e.g., `getDeactivate`). The module for this plugin is always `self::$module` (`'licenses'`).

Verify: the event name is not already a key in `getHooks()` in `src/Plugin.php`. If it is, you are extending an existing handler, not adding a new one.

### Step 2 — Register the hook in `getHooks()`

File: `src/Plugin.php`, inside the array returned by `getHooks()`.

Add one line:
```php
self::$module.'.your_action' => [__CLASS__, 'getYourAction'],
```

If multiple event names should share the same handler (like `activate` and `reactivate` both map to `getActivate`), add separate keys pointing to the same method:
```php
self::$module.'.your_action'   => [__CLASS__, 'getYourAction'],
self::$module.'.your_action2'  => [__CLASS__, 'getYourAction'],
```

Verify: the `getHooks()` return array is syntactically valid PHP (trailing commas are fine; missing commas are not).

### Step 3 — Register any new procedural functions in `getRequirements()`

File: `src/Plugin.php`, inside `getRequirements()`.

If the handler will call a function defined in `src/fantastico.inc.php`, add:
```php
$loader->add_requirement('your_function_name', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
```

If the handler will be called from a page context, add a page requirement instead:
```php
$loader->add_page_requirement('your_page_function', '/../vendor/detain/myadmin-fantastico-licensing/src/your_page.php');
```

Verify: every function called via `function_requirements('name')` inside the new handler has a matching `add_requirement()` entry here.

### Step 4 — Write the handler method

Add the static method to `src/Plugin.php` using this exact structure:

```php
/**
 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
 */
public static function getYourAction(GenericEvent $event)
{
    $serviceClass = $event->getSubject();
    if ($event['category'] == get_service_define('FANTASTICO')) {
        myadmin_log(self::$module, 'info', 'Descriptive message here', __LINE__, __FILE__, self::$module, $serviceClass->getId());
        function_requirements('your_function_name'); // only if calling a procedural function
        // ... handler logic ...
        $event->stopPropagation();
    }
}
```

For error outcomes, log at `'error'` level and set event status fields before `stopPropagation()`:
```php
myadmin_log(self::$module, 'error', 'Operation failed: '.$result['fault'], __LINE__, __FILE__, self::$module, $serviceClass->getId());
$event['status'] = 'error';
$event['status_text'] = 'Error Code '.$result['faultcode'].': '.$result['fault'];
```

For success outcomes that mutate service state, use the fluent `$serviceClass` setters and call `->save()`:
```php
$serviceClass->setKey($result['licenseid'])->save();
$event['status'] = 'ok';
$event['status_text'] = 'Operation completed.';
```

Verify: `$event->stopPropagation()` is inside the `if` block, not outside it.

### Step 5 — Run tests

```bash
vendor/bin/phpunit tests/PluginTest.php
```

Verify: all tests pass. If `PluginTest` checks `getHooks()` keys, your new key must appear in the returned array.

## Examples

**User says:** "Add a hook handler for `licenses.deactivate` that calls a `deactivate_fantastico($ip)` function."

**Step 2** — add to `getHooks()`:
```php
self::$module.'.deactivate' => [__CLASS__, 'getDeactivate'],
```

**Step 3** — add to `getRequirements()`:
```php
$loader->add_requirement('deactivate_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
```

**Step 4** — add the method:
```php
/**
 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
 */
public static function getDeactivate(GenericEvent $event)
{
    $serviceClass = $event->getSubject();
    if ($event['category'] == get_service_define('FANTASTICO')) {
        myadmin_log(self::$module, 'info', 'Fantastico Deactivation', __LINE__, __FILE__, self::$module, $serviceClass->getId());
        function_requirements('deactivate_fantastico');
        $result = deactivate_fantastico($serviceClass->getIp());
        if (isset($result['faultcode'])) {
            myadmin_log(self::$module, 'error', 'Fantastico deactivate returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__, self::$module, $serviceClass->getId());
            $event['status'] = 'error';
            $event['status_text'] = 'Error Code '.$result['faultcode'].': '.$result['fault'];
        } else {
            $event['status'] = 'ok';
            $event['status_text'] = 'License has been deactivated.';
        }
        $event->stopPropagation();
    }
}
```

**Result:** `getHooks()` returns a `licenses.deactivate` key, the loader registers `deactivate_fantastico`, and the handler guards on `FANTASTICO` category, logs, calls the function, sets status fields, and stops propagation.

## Common Issues

- **`Call to undefined function get_service_define()`** — the handler is running outside of a bootstrapped MyAdmin environment. In tests, verify `tests/bootstrap.php` is loaded and `function_requirements` stubs are in place.

- **Handler fires for all license categories, not just Fantastico** — the `if ($event['category'] == get_service_define('FANTASTICO'))` guard is missing or placed outside the method body. Move it to wrap all logic before `stopPropagation()`.

- **`Undefined index` on `$event['category']`** — the event was dispatched without a `category` key. Check the caller site; for service events this key is always set by the billing flow. In unit tests, construct the event as `new GenericEvent($subject, ['category' => ..., 'field1' => ...])`.

- **`stopPropagation()` called but other handlers still run** — `stopPropagation()` is outside the `if` block, so it fires unconditionally and prevents other plugins from handling non-Fantastico events. Place it as the last line *inside* the `if` block.

- **Tests fail with `Method getYourAction does not exist`** — the method was added to the wrong class or misspelled relative to the `getHooks()` entry. The string in `[__CLASS__, 'getYourAction']` must exactly match the method name defined in the same class.

- **`function_requirements('x')` silently does nothing** — no matching `add_requirement('x', ...)` entry exists in `getRequirements()`. Add the entry (Step 3) and verify the path starts with `/../vendor/detain/myadmin-fantastico-licensing/src/`.