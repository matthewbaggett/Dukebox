#!/usr/local/bin/php -q
<?php

require_once(dirname(__FILE__) . "/../bootstrap.php");
$ds = DukeService::Factory();
$ds->run();