<?php

namespace Detain\MyAdminFantastico;

use Detain\Fantastico\Fantastico;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminFantastico
 */
class Plugin {

	public static $name = 'Fantastico Licensing';
	public static $description = 'Allows selling of Fantastico Server and VPS License Types.  More info at https://www.netenberg.com/fantastico.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a fantastico license. Allow 10 minutes for activation.';
	public static $module = 'licenses';
	public static $type = 'service';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return array
	 */
	public static function getHooks() {
		return [
			'function.requirements' => [__CLASS__, 'getRequirements'],
			self::$module.'.settings' => [__CLASS__, 'getSettings'],
			self::$module.'.activate' => [__CLASS__, 'getActivate'],
			self::$module.'.reactivate' => [__CLASS__, 'getActivate'],
			self::$module.'.change_ip' => [__CLASS__, 'getChangeIp'],
			'ui.menu' => [__CLASS__, 'getMenu']
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getActivate(GenericEvent $event) {
		$serviceClass = $event->getSubject();
		if ($event['category'] == get_service_define('FANTASTICO')) {
			myadmin_log(self::$module, 'info', 'Fantastico Activation', __LINE__, __FILE__);
			function_requirements('activate_fantastico');
			activate_fantastico($serviceClass->getIp(), $event['field1']);
			$event->stopPropagation();
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getChangeIp(GenericEvent $event) {
		if ($event['category'] == get_service_define('FANTASTICO')) {
			$serviceClass = $event->getSubject();
			$settings = get_module_settings(self::$module);
			$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
			myadmin_log(self::$module, 'info', 'IP Change - (OLD:'.$serviceClass->getIp().") (NEW:{$event['newip']})", __LINE__, __FILE__);
			$result = $fantastico->editIp($serviceClass->getIp(), $event['newip']);
			if (isset($result['faultcode'])) {
				myadmin_log(self::$module, 'error', 'Fantastico editIp('.$serviceClass->getIp().', '.$event['newip'].') returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__);
				$event['status'] = 'error';
				$event['status_text'] = 'Error Code '.$result['faultcode'].': '.$result['fault'];
			} else {
				$GLOBALS['tf']->history->add($settings['TABLE'], 'change_ip', $event['newip'], $serviceClass->getIp());
				$serviceClass->set_ip($event['newip'])->save();
				$event['status'] = 'ok';
				$event['status_text'] = 'The IP Address has been changed.';
			}
			$event->stopPropagation();
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event) {
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link(self::$module, 'choice=none.reusable_fantastico', 'images/icons/database_warning_48.png', 'ReUsable Fantastico Licenses');
			$menu->add_link(self::$module, 'choice=none.fantastico_list', 'images/icons/database_warning_48.png', 'Fantastico Licenses Breakdown');
			$menu->add_link(self::$module.'api', 'choice=none.fantastico_licenses_list', 'whm/createacct.gif', 'List all Fantastico Licenses');
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event) {
		$loader = $event->getSubject();
		$loader->add_page_requirement('crud_fantastico_list', '/../vendor/detain/crud/src/crud/crud_fantastico_list.php');
		$loader->add_page_requirement('crud_reusable_fantastico', '/../vendor/detain/crud/src/crud/crud_reusable_fantastico.php');
		$loader->add_requirement('get_fantastico_licenses', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('get_fantastico_list', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_page_requirement('fantastico_licenses_list', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico_licenses_list.php');
		$loader->add_page_requirement('fantastico_list', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico_list.php');
		$loader->add_requirement('get_available_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('activate_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_requirement('get_reusable_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/fantastico.inc.php');
		$loader->add_page_requirement('reusable_fantastico', '/../vendor/detain/myadmin-fantastico-licensing/src/reusable_fantastico.php');
		$loader->add_requirement('class.Fantastico', '/../vendor/detain/fantastico-licensing/src/Fantastico.php');
		$loader->add_page_requirement('vps_add_fantastico', '/vps/addons/vps_add_fantastico.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_text_setting(self::$module, 'Fantastico', 'fantastico_username', 'Fantastico Username:', 'Fantastico Username', $settings->get_setting('FANTASTICO_USERNAME'));
		$settings->add_text_setting(self::$module, 'Fantastico', 'fantastico_password', 'Fantastico Password:', 'Fantastico Password', $settings->get_setting('FANTASTICO_PASSWORD'));
		$settings->add_dropdown_setting(self::$module, 'Fantastico', 'outofstock_licenses_fantastico', 'Out Of Stock Fantastico Licenses', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_LICENSES_FANTASTICO'), ['0', '1'], ['No', 'Yes']);
	}

}
