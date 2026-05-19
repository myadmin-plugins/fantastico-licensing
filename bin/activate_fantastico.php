#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';
\MyAdmin\App::session()->sessionid = substr(basename($_SERVER['argv'][0], '.php'), 0, 32);
\MyAdmin\App::session()->account_id = 160308;
\MyAdmin\App::session()->appnocache('ima', 'services');
\MyAdmin\App::tf()->ima = 'services';

activate_fantastico('66.23.229.238', 2);
