<?php
require_once(__DIR__ . '/../config.php');
require_once(CONTROLLER_PATH . '/AccountController.php');
$accountInstance = new AccountController($mysqli);
$accountInstance->register();
?>