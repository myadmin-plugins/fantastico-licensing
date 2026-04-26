#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../../include/functions.inc.php';
\MyAdmin\App::session()->create(160308, 'services');
\MyAdmin\App::session()->verify();

activate_fantastico('66.23.229.238', 2);

\MyAdmin\App::session()->destroy();
