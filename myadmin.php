<?php
/* binds/hooks/events
        individual action binds/hooks/events
        configuation fields
        functions/classes
        installation
        uninstallation
        menu links
        service links
        host server links
        customer links
	db table/fields
libraries to use for events:	http://symfony.com/doc/current/components/event_dispatcher.html
Event Name Rules:
 - Use only lowercase letters, numbers, dots (.) and underscores (_);
 - Prefix names with a namespace followed by a dot (e.g. order., user.*);
 - End names with a verb that indicates what action has been taken (e.g. order.placed).
*/
return [
	'name' => 'Fantastico Licensing',
	'description' => 'Allows selling of Fantastico Server and VPS License Types.  More info at https://www.netenberg.com/fantastico.php',
	'module' => 'licenses',
	'author' => 'detain@interserver.net',
	'home' => '',
	'repo' => '',
	'version' => '1.0.0',
	'type' => 'licenses',
	
	'settings' => [
	],
	'hooks' => [
		'licenses.settings' => ['Detain\MyAdminFantastico\Settings', 'update'],
		'ui.menu' => ['Detain\MyAdminFantastico\Menu', 'update']
	],
];
