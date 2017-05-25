<?php

namespace Detain\MyAdminFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Menu {

	public function __construct() {
	}

	public static function update(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$menu = $event->getSubject();
		$module = 'licenses';
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link($module, 'choice=none.reusable_fantastico', 'icons/database_warning_48.png', 'ReUsable Fantastico Licenses');
			$menu->add_link($module, 'choice=none.fantastico_list', 'icons/database_warning_48.png', 'Fantastico Licenses Breakdown');
			$menu->add_link('licensesapi', 'choice=none.fantastico_licenses_list', 'whm/createacct.gif', 'List all Fantastico Licenses');
		}
	}

}
