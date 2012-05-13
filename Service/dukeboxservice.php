#!/usr/local/bin/php -q
<?php
$config = array(
	"media_location" => "/media/music"
);

require_once(dirname(__FILE__) . "/../bootstrap.php");
$ds = DukeService::Factory();
$ds->set_config($config);
$ds->initialise();
$ds->run();