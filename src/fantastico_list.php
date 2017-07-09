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

function fantastico_list() {
	if ($GLOBALS['tf']->ima == 'admin') {
		page_title('Fantastico License List');
		add_output(render_form('fantastico_list'));
	}
}

