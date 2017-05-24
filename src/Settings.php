<?php

namespace Detain\MyAdminFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Settings {

	public function __construct() {
	}

	public static function update(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$settings = $event->getSubject();
		$settings->add_text_setting('apisettings', 'fantastico_username', 'Fantastico Username:', 'Fantastico Username', FANTASTICO_USERNAME);
		$settings->add_text_setting('apisettings', 'fantastico_password', 'Fantastico Password:', 'Fantastico Password', FANTASTICO_PASSWORD);
	}

}
