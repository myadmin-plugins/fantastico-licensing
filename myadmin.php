<?php
/* binds/hooks/events
libraries to use for events:	http://symfony.com/doc/current/components/event_dispatcher.html
Event Name Rules:
 - Use only lowercase letters, numbers, dots (.) and underscores (_);
 - Prefix names with a namespace followed by a dot (e.g. order., user.*);
 - End names with a verb that indicates what action has been taken (e.g. order.placed).
TODO:
 - add easy way to call/hook into activate/deactivate/chagne ip functions
 - add easy way to call extra page / functions
 - service type, category, and services  adding
 - dealing with the SERVICE_TYPES_fantastico define
 - add way to call/hook into install/uninstall
*/
return [
	'name' => 'Fantastico Licensing',
	'description' => 'Allows selling of Fantastico Server and VPS License Types.  More info at https://www.netenberg.com/fantastico.php',
	'help' => 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a fantastico license. Allow 10 minutes for activation.',
	'module' => 'licenses',
	'author' => 'detain@interserver.net',
	'home' => 'https://github.com/detain/myadmin-fantastico',
	'repo' => 'https://github.com/detain/myadmin-fantastico',
	'version' => '1.0.0',
	'type' => 'licenses',
	'hooks' => [
		'function.requirements' => ['Detain\MyAdminFantastico\Requirements', 'update'],
		'licenses.settings' => ['Detain\MyAdminFantastico\Settings', 'update'],
		'ui.menu' => ['Detain\MyAdminFantastico\Menu', 'update']
	],
];
