<?php

namespace Detain\MyAdminFantastico\Tests;

use Detain\MyAdminFantastico\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Test suite for the Plugin class.
 *
 * Validates class structure, static properties, hook registration,
 * and event handler method signatures using reflection.
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass
     */
    private $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    /**
     * Tests that the Plugin class exists and can be reflected.
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }

    /**
     * Tests that Plugin resides in the correct namespace.
     */
    public function testClassNamespace(): void
    {
        $this->assertSame('Detain\\MyAdminFantastico', $this->reflection->getNamespaceName());
    }

    /**
     * Tests that Plugin is not abstract and not an interface.
     */
    public function testClassIsInstantiable(): void
    {
        $this->assertTrue($this->reflection->isInstantiable());
        $this->assertFalse($this->reflection->isAbstract());
        $this->assertFalse($this->reflection->isInterface());
    }

    /**
     * Tests that the constructor exists and takes no required parameters.
     */
    public function testConstructorHasNoRequiredParameters(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertSame(0, $constructor->getNumberOfRequiredParameters());
    }

    /**
     * Tests that Plugin can be instantiated without errors.
     */
    public function testCanInstantiate(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Tests that the $name static property exists and has the expected value.
     */
    public function testNameProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('name'));
        $prop = $this->reflection->getProperty('name');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('Fantastico Licensing', Plugin::$name);
    }

    /**
     * Tests that the $description static property exists and contains expected content.
     */
    public function testDescriptionProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('description'));
        $prop = $this->reflection->getProperty('description');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertIsString(Plugin::$description);
        $this->assertStringContainsString('Fantastico', Plugin::$description);
        $this->assertStringContainsString('netenberg.com', Plugin::$description);
    }

    /**
     * Tests that the $help static property exists and contains expected content.
     */
    public function testHelpProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('help'));
        $prop = $this->reflection->getProperty('help');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertIsString(Plugin::$help);
        $this->assertStringContainsString('cPanel', Plugin::$help);
    }

    /**
     * Tests that the $module static property is set to 'licenses'.
     */
    public function testModuleProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('module'));
        $prop = $this->reflection->getProperty('module');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('licenses', Plugin::$module);
    }

    /**
     * Tests that the $type static property is set to 'service'.
     */
    public function testTypeProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('type'));
        $prop = $this->reflection->getProperty('type');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('service', Plugin::$type);
    }

    /**
     * Tests that all five expected static properties exist.
     */
    public function testAllStaticPropertiesExist(): void
    {
        $expected = ['name', 'description', 'help', 'module', 'type'];
        foreach ($expected as $propName) {
            $this->assertTrue(
                $this->reflection->hasProperty($propName),
                "Missing static property: {$propName}"
            );
        }
    }

    /**
     * Tests that getHooks() returns an array with the expected event keys.
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Tests that getHooks() contains the function.requirements hook.
     */
    public function testGetHooksContainsFunctionRequirements(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('function.requirements', $hooks);
        $this->assertSame([Plugin::class, 'getRequirements'], $hooks['function.requirements']);
    }

    /**
     * Tests that getHooks() contains the licenses.settings hook.
     */
    public function testGetHooksContainsSettings(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('licenses.settings', $hooks);
        $this->assertSame([Plugin::class, 'getSettings'], $hooks['licenses.settings']);
    }

    /**
     * Tests that getHooks() contains the licenses.activate hook.
     */
    public function testGetHooksContainsActivate(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('licenses.activate', $hooks);
        $this->assertSame([Plugin::class, 'getActivate'], $hooks['licenses.activate']);
    }

    /**
     * Tests that getHooks() contains the licenses.reactivate hook pointing to getActivate.
     */
    public function testGetHooksContainsReactivate(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('licenses.reactivate', $hooks);
        $this->assertSame([Plugin::class, 'getActivate'], $hooks['licenses.reactivate']);
    }

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
     * Tests that getHooks() contains the ui.menu hook.
     */
    public function testGetHooksContainsMenu(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('ui.menu', $hooks);
        $this->assertSame([Plugin::class, 'getMenu'], $hooks['ui.menu']);
    }

    /**
     * Tests that getHooks() returns exactly 6 hook entries.
     */
    public function testGetHooksCount(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertCount(6, $hooks);
    }

    /**
     * Tests that hook keys use the $module property value as prefix where applicable.
     */
    public function testHookKeysUseModulePrefix(): void
    {
        $hooks = Plugin::getHooks();
        $module = Plugin::$module;
        $this->assertArrayHasKey("{$module}.settings", $hooks);
        $this->assertArrayHasKey("{$module}.activate", $hooks);
        $this->assertArrayHasKey("{$module}.reactivate", $hooks);
        $this->assertArrayHasKey("{$module}.change_ip", $hooks);
    }

    /**
     * Tests that all hook callbacks reference callable static methods on Plugin.
     */
    public function testAllHookCallbacksAreCallableSignatures(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $eventName => $callback) {
            $this->assertIsArray($callback, "Hook '{$eventName}' callback should be an array");
            $this->assertCount(2, $callback, "Hook '{$eventName}' callback should have exactly 2 elements");
            $this->assertSame(Plugin::class, $callback[0], "Hook '{$eventName}' should reference Plugin class");
            $this->assertTrue(
                $this->reflection->hasMethod($callback[1]),
                "Hook '{$eventName}' references non-existent method: {$callback[1]}"
            );
        }
    }

    /**
     * Tests that getHooks() is a static method.
     */
    public function testGetHooksIsStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    /**
     * Tests that getActivate() accepts exactly one parameter of type GenericEvent.
     */
    public function testGetActivateMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getActivate');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());

        $params = $method->getParameters();
        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
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
        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
    }

    /**
     * Tests that getMenu() accepts exactly one parameter of type GenericEvent.
     */
    public function testGetMenuMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());

        $params = $method->getParameters();
        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
    }

    /**
     * Tests that getRequirements() accepts exactly one parameter of type GenericEvent.
     */
    public function testGetRequirementsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());

        $params = $method->getParameters();
        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
    }

    /**
     * Tests that getSettings() accepts exactly one parameter of type GenericEvent.
     */
    public function testGetSettingsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());

        $params = $method->getParameters();
        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
    }

    /**
     * Tests that the Plugin class has exactly the expected set of public methods.
     */
    public function testExpectedPublicMethods(): void
    {
        $expectedMethods = [
            '__construct',
            'getHooks',
            'getActivate',
            'getChangeIp',
            'getMenu',
            'getRequirements',
            'getSettings',
        ];

        $publicMethods = array_map(
            fn (ReflectionMethod $m) => $m->getName(),
            $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC)
        );

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $publicMethods, "Missing public method: {$method}");
        }
    }

    /**
     * Tests that activate and reactivate hooks point to the same handler.
     */
    public function testActivateAndReactivateShareSameHandler(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame(
            $hooks['licenses.activate'],
            $hooks['licenses.reactivate'],
            'activate and reactivate should share the same handler'
        );
    }

    /**
     * Tests that the class does not extend any parent class.
     */
    public function testPluginHasNoParentClass(): void
    {
        $this->assertFalse($this->reflection->getParentClass());
    }

    /**
     * Tests that the class implements no interfaces.
     */
    public function testPluginImplementsNoInterfaces(): void
    {
        $this->assertCount(0, $this->reflection->getInterfaces());
    }
}
