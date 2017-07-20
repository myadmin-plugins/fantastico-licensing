#!/usr/bin/php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';
$GLOBALS['tf']->session->create(160308, 'services');
$GLOBALS['tf']->session->verify();

activate_fantastico('66.23.229.238', 2);

$GLOBALS['tf']->session->destroy();
