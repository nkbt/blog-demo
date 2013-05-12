<?php
echo '<pre>';
$_SERVER['argv'] = array(
    'scripts/load.php',
    '--withdata',
);
require_once('../scripts/load.php');