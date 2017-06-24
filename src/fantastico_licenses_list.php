<?php
/**
 * Fantastico Related Functionality
 * Last Changed: $LastChangedDate: 2017-05-25 09:04:25 -0400 (Thu, 25 May 2017) $
 * @author detain
 * @version $Revision: 24748 $
 * @copyright 2017
 * @package MyAdmin
 * @category Licenses
 */

use Detain\Fantastico\Fantastico;

function fantastico_licenses_list() {
	if ($GLOBALS['tf']->ima == 'admin') {
		$table = new TFTable;
		$table->set_title('Fantastico License List');
		$header = FALSE;
		$licenses = get_fantastico_licenses();
		foreach ($licenses as $lid => $data) {
			if (!$header) {
				foreach (array_keys($data) as $field) {
					$table->add_field(ucwords(str_replace('_', ' ', $field)));
				}
				$table->add_row();
				$header = TRUE;
			}
			foreach ($data as $key => $field) {
				$table->add_field($field);
			}
			$table->add_row();
		}
		add_output($table->get_table());
	}
}

