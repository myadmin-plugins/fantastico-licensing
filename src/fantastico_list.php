<?php
/**
 * Fantastico Related Functionality
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2018
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

