<?php
require_once(__DIR__ . '/../config.php');
require_once(CONTROLLER_PATH . '/MiddlewareController.php');
$middlewareInstance = new MiddlewareController($mysqli);
$middlewareInstance->check_login();
require_once(VIEW_PATH . '/profile.php');
