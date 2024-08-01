<?php
require_once(__DIR__ . '/../config.php');
require_once(CONTROLLER_PATH . '/ChatController.php');
$authInstance = new ChatController($mysqli);
$authInstance->del_follow();
?>