<?php
require_once(__DIR__ . '/../config.php');
require_once(CONTROLLER_PATH . '/ChatController.php');
$chatInstance = new ChatController($mysqli);
$chatInstance->send_message();
?>