<?php
/**
 * Fantastico Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2017
 * @package MyAdmin
 * @category Licenses
 */

use Detain\Fantastico\Fantastico;

function fantastico_licenses_list() {
	if ($GLOBALS['tf']->ima == 'admin') {
		$table = new \TFTable;
		$table->set_title('Fantastico License List');
		$header = FALSE;
		$licenses = get_fantastico_licenses();
		$licensesValues = array_values($licenses);
		foreach ($licensesValues as $data) {
			if (!$header) {
				$dataKeys = array_keys($data);
				foreach ($dataKeys as $field)
					$table->add_field(ucwords(str_replace('_', ' ', $field)));
				$table->add_row();
				$header = TRUE;
			}
			$dataValues = array_values($data);
			foreach ($dataValues as $field)
				$table->add_field($field);
			$table->add_row();
		}
		add_output($table->get_table());
	}
}

