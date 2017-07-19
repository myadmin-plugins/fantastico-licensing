<?php
/**
 * Fantastico Related Functionality
 * Last Changed: $LastChangedDate: 2017-05-25 09:04:25 -0400 (Thu, 25 May 2017) $
 * @author detain
 * @copyright 2017
 * @package MyAdmin
 * @category Licenses
 */

use Detain\Fantastico\Fantastico;

/**
 * get_fantastico_licenses()
 * simple wrapper to get all the fantastico licenses.
 *
 * @return FALSE|array array of licenses. {@link Fantastico.getIpListDetailed}
 */
function get_fantastico_licenses() {
	$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
	$fantasticoIps = $fantastico->getIpListDetailed(Fantastico::ALL_TYPES);
	request_log('licenses', FALSE, __FUNCTION__, 'fantastico', 'getIpListDetailed', 'Fantastico::ALL_TYPES', $fantasticoIps);
	return $fantasticoIps;
}

/**
 * @return array
 */
function get_fantastico_list() {
	$category = get_service_define('FANTASTICO');
	$fantasticoIps = get_fantastico_licenses();
	$ipdata = [];
	// Has ipAddress with a string ip, addedOn with a string date mysql formatted date, isVPS which might be 'No', and status which might be 'Active'
	$ipValues = array_values($fantasticoIps);
	foreach ($ipValues as $data) {
		$data['addedOn'] = explode(' ', $data['addedOn']);
		$data['addedOn'] = array_shift($data['addedOn']);
		$ipdata[$data['ipAddress']] = array_merge($data, array(
			'hostname' => '',
			'billing_status' => '',
			'account_lid' => '',
			'site' => ''));
	}
	$ips = array_keys($ipdata);
	if (isset($GLOBALS['modules']['licenses'])) {
		$db = get_module_db('licenses');
		$db->query("SELECT licenses.license_ip
 , licenses.license_hostname AS hostname
 , licenses.license_status AS billing_status
 , accounts.account_lid
FROM
licenses
LEFT OUTER JOIN services
ON licenses.license_type = services.services_id
LEFT OUTER JOIN accounts
ON licenses.license_custid = accounts.account_id
WHERE
services.services_category = {$category}
and services_module='licenses'
and license_ip in ('".implode("','", $ips)."')", __LINE__, __FILE__);
		while ($db->next_record(MYSQL_ASSOC)) {
			$db->Record['site'] = 'cPanelDirect';
			$ipAddress = $db->Record['license_ip'];
			unset($db->Record['license_ip']);
			$ipdata[$ipAddress] = array_merge($ipdata[$ipAddress], $db->Record);
		}
	}
	if (isset($GLOBALS['modules']['vps'])) {
		$db = get_module_db('vps');
		$db->query("SELECT vps.vps_ip
 , vps.vps_hostname AS hostname
 , vps.vps_status AS billing_status
 , accounts.account_lid
FROM
vps
INNER JOIN repeat_invoices
ON concat('Fantastico for VPS ', vps.vps_id) = repeat_invoices.repeat_invoices_description
INNER JOIN accounts
ON vps.vps_custid = accounts.account_id
WHERE
vps.vps_ip in ('".implode("','", $ips)."')", __LINE__, __FILE__);
		while ($db->next_record(MYSQL_ASSOC)) {
			$db->Record['site'] = 'Interserver VPS';
			$ipAddress = $db->Record['vps_ip'];
			unset($db->Record['vps_ip']);
			$ipdata[$ipAddress] = array_merge($ipdata[$ipAddress], $db->Record);
		}
	}
	$response = [];
	foreach ($ipdata as $ipAddress => $data) {
		$response[] = $data;
	}
	return $response;
}

/**
 * get_available_fantastico()
 * @param mixed $type
 * @return void
 */
function get_available_fantastico($type) {
	$db = get_module_db('licenses');
	$settings = get_module_settings('licenses');
	$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
	$ips = $fantastico->getIpList(Fantastico::ALL_TYPES);
	$db->query("select * from {$settings['TABLE']} left join services on {$settings['PREFIX']}_type=services_id where services_module='licenses' and services_category=".SERVICE_TYPES_FANTASTICO." and {$settings['PREFIX']}_status in ('canceled','expired')");
	// go through all canceled/expired ips
	while ($db->next_record(MYSQL_ASSOC)) {
		// check if ip is still licensed
		if (in_array($db->Record['license_ip'], $ips)) {
			$result = $fantastico->getIpDetails($db->Record['license_ip']);
			if ($type == 1) {
				if ($result['isVPS'] == 'No') {
					echo "Found Reusable Dedicated Server Fantastico License On IP {$db->Record['license_ip']}\n";
				}
			} else {
				if ($result['isVPS'] == 'Yes') {
					echo "Found Reusable VPS Fantastico License On IP {$db->Record['license_ip']}\n";
				}
			}
		}
	}
}

/**
 * activate_fantastico()
 * @param mixed $ipAddress
 * @param mixed $type
 * @return bool
 */
function activate_fantastico($ipAddress, $type) {
	ini_set('max_execution_time', 1000); // just put a lot of time
	ini_set('default_socket_timeout', 1000); // same
	$db = get_module_db('licenses');
	$settings = get_module_settings('licenses');
	$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
	// this is done first because it caches the getIpList() and getIpDetails() responses so they're faster as it loads all the data from 1 command
	get_fantastico_licenses();
	$ips = $fantastico->getIpList(Fantastico::ALL_TYPES);
	$db->query("select * from {$settings['TABLE']} left join services on {$settings['PREFIX']}_type=services_id where services_module='licenses' and services_category=".SERVICE_TYPES_FANTASTICO." and {$settings['PREFIX']}_status in ('canceled','expired')");
	// go through all canceled/expired ips
	while ($db->next_record(MYSQL_ASSOC)) {
		// check if ip is still licensed
		if (in_array($db->Record['license_ip'], $ips)) {
			$result = $fantastico->getIpDetails($db->Record['license_ip']);
			if ($type == 1) {
				if ($result['isVPS'] == 'No') {
					$result = $fantastico->editIp($db->Record['license_ip'], $ipAddress);
					myadmin_log('licenses', 'info', "Fantastico Re-Using IP {$db->Record['license_ip']} Type $type As $ipAddress result: ".myadmin_stringify($result, 'json'), __LINE__, __FILE__);
					return TRUE;
				}
			} else {
				if ($result['isVPS'] == 'Yes') {
					$result = $fantastico->editIp($db->Record['license_ip'], $ipAddress);
					myadmin_log('licenses', 'info', "Fantastico Re-Using IP {$db->Record['license_ip']} Type $type As $ipAddress result: ".myadmin_stringify($result, 'json'), __LINE__, __FILE__);
					return TRUE;
				}
			}
		}
	}
	$result = $fantastico->addIp($ipAddress, $type);
	if (isset($result['faultcode'])) {
		myadmin_log('licenses', 'error', 'Fantastico addIp($ipAddress, $type) returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__);
		return FALSE;
	}
	myadmin_log('licenses', 'info', "Fantastico New License $ipAddress Type $type Licensed ID {$result['id']}", __LINE__, __FILE__);
	return TRUE;
}

/**
 * @return array
 */
function get_reusable_fantastico() {
	$db = get_module_db('licenses');
	$settings = get_module_settings('licenses');
	$fantastico = new Fantastico(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
	$ips = $fantastico->getIpList(Fantastico::ALL_TYPES);
	$query = "select {$settings['PREFIX']}_ip, {$settings['PREFIX']}_status from {$settings['TABLE']} left join services on {$settings['PREFIX']}_type=services_id where services_module='licenses' and services_category=".SERVICE_TYPES_FANTASTICO." and {$settings['PREFIX']}_ip in ('".implode("','", $ips)."') order by {$settings['PREFIX']}_status";
	//echo $query;
	$db->query($query, __LINE__, __FILE__);
	$rows = [];
	$activeips = [];
	while ($db->next_record(MYSQL_ASSOC)) {
		if ($db->Record[$settings['PREFIX'].'_status'] == 'active') {
			$activeips[] = $db->Record[$settings['PREFIX'].'_ip'];
		} elseif (!in_array($db->Record[$settings['PREFIX'].'_ip'], $activeips)) {
			$rows[] = $fantastico->getIpDetails($db->Record[$settings['PREFIX'].'_ip']);
		}
	}
	return $rows;
}
