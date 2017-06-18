<?php

namespace Detain\MyAdminFantastico;

use Detain\Fantastico\Fantastico;
use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public static $name = 'Fantastico Licensing';
	public static $description = 'Allows selling of Fantastico Server and VPS License Types.  More info at https://www.netenberg.com/fantastico.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a fantastico license. Allow 10 minutes for activation.';
	public static $module = 'licenses';
	public static $type = 'service';


	public function __construct() {
	}

	public static function getHooks() {
		return [
			'function.requirements' => [__CLASS__, 'Requirements'],
			'licenses.settings' => [__CLASS__, 'Settings'],
			'licenses.activate' => [__CLASS__, 'Activate'],
			'licenses.change_ip' => [__CLASS__, 'ChangeIp'],
			'ui.menu' => [__CLASS__, 'Menu'],
		];
	}

	public static function Activate(GenericEvent $event) {
		// will be executed when the licenses.license event is dispatched
		$license = $event->getSubject();
		if ($event['category'] == SERVICE_TYPES_FANTASTICO) {
			myadmin_log('licenses', 'info', 'Fantastico Activation', __LINE__, __FILE__);
			function_requirements('activate_fantastico');
			activate_fantastico($license->get_ip(), $event['field1']);
			$event->stopPropagation();
		}
	}

	public static function ChangeIp(GenericEvent $event) {
		if ($event['category'] == SERVICE_TYPES_FANTASTICO) {
			$license = $event->getSubject();
			$settings = get_module_settings('licenses');
			$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
			myadmin_log('licenses', 'info', "IP Change - (OLD:".$license->get_ip().") (NEW:{$event['newip']})", __LINE__, __FILE__);
			$result = $fantastico->editIp($license->get_ip(), $event['newip']);
			if (isset($result['faultcode'])) {
				myadmin_log('licenses', 'error', 'Fantastico editIp('.$license->get_ip().', '.$event['newip'].') returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__);
				$event['status'] = 'error';
				$event['status_text'] = 'Error Code '.$result['faultcode'].': '.$result['fault'];
			} else {
				$GLOBALS['tf']->history->add($settings['TABLE'], 'change_ip', $event['newip'], $license->get_ip());
				$license->set_ip($event['newip'])->save();
				$event['status'] = 'ok';
				$event['status_text'] = 'The IP Address has been changed.';
			}
			$event->stopPropagation();
		}
	}

	public static function Menu(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$menu = $event->getSubject();
		$module = 'licenses';
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link($module, 'choice=none.reusable_fantastico', 'icons/database_warning_48.png', 'ReUsable Fantastico Licenses');
			$menu->add_link($module, 'choice=none.fantastico_list', 'icons/database_warning_48.png', 'Fantastico Licenses Breakdown');
			$menu->add_link($module.'api', 'choice=none.fantastico_licenses_list', 'whm/createacct.gif', 'List all Fantastico Licenses');
		}
	}

	public static function Requirements(GenericEvent $event) {
		// will be executed when the licenses.loader event is dispatched
		$loader = $event->getSubject();
		$loader->add_requirement('crud_fantastico_list', '/../vendor/detain/crud/src/crud/crud_fantastico_list.php');
		$loader->add_requirement('crud_reusable_fantastico', '/../vendor/detain/crud/src/crud/crud_reusable_fantastico.php');
		$loader->add_requirement('get_fantastico_licenses', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('get_fantastico_list', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('fantastico_licenses_list', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico_licenses_list.php');
		$loader->add_requirement('fantastico_list', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico_list.php');
		$loader->add_requirement('get_available_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('activate_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('get_reusable_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('reusable_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/reusable_fantastico.php');
		$loader->add_requirement('class.Fantastico', '/../vendor/detain/fantastico-licensing/src/Fantastico.php');
		$loader->add_requirement('vps_add_fantastico', '/vps/addons/vps_add_fantastico.php');
	}

	public static function Settings(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$settings = $event->getSubject();
		$settings->add_text_setting('licenses', 'Fantastico', 'fantastico_username', 'Fantastico Username:', 'Fantastico Username', $settings->get_setting('FANTASTICO_USERNAME'));
		$settings->add_text_setting('licenses', 'Fantastico', 'fantastico_password', 'Fantastico Password:', 'Fantastico Password', $settings->get_setting('FANTASTICO_PASSWORD'));
		$settings->add_dropdown_setting('licenses', 'Fantastico', 'outofstock_licenses_fantastico', 'Out Of Stock Fantastico Licenses', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_LICENSES_FANTASTICO'), array('0', '1'), array('No', 'Yes', ));
	}

}
