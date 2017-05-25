<?php

namespace Detain\MyAdminFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Requirements {

	public function __construct() {
	}

	public static function update(GenericEvent $event) {
		// will be executed when the licenses.loader event is dispatched
		$loader = $event->getSubject();
		$loader->add_requirement('crud_fantastico_list', '/../vendor/detain/crud/src/crud/crud_fantastico_list.php');
		$loader->add_requirement('crud_reusable_fantastico', '/../vendor/detain/crud/src/crud/crud_reusable_fantastico.php');
		$loader->add_requirement('get_fantastico_licenses', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('get_fantastico_list', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('fantastico_list', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('get_available_fantastico', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('activate_fantastico', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('get_reusable_fantastico', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('reusable_fantastico', '/licenses/fantastico.functions.inc.php');
		$loader->add_requirement('class.fantastico', '/../vendor/detain/fantastico/class.fantastico.inc.php');
		$loader->add_requirement('vps_add_fantastico', '/vps/addons/vps_add_fantastico.php');
	}

}
