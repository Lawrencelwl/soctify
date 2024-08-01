<?php
require_once(__DIR__ . '/../config.php');
require_once(CONTROLLER_PATH . '/AuthController.php');
$authInstance = new AuthController($mysqli);
$authInstance->get_password();
?>