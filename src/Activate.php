<?php

namespace Detain\MyAdminFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Activate {

	public function __construct() {
	}

	public static function update(GenericEvent $event) {
		// will be executed when the licenses.license event is dispatched
		$license = $event->getSubject();
		if ($event['category'] == SERVICE_TYPES_FANTASTICO) {
			myadmin_log('licenses', 'info', 'Fantastico Activation', __LINE__, __FILE__);
			function_requirements('activate_fantastico');
			activate_fantastico($license->get_ip(), $event['field1']);
			$event->stopPropagation();
		}
	}

}
