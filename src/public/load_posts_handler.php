<?php
require_once(__DIR__ . '/../config.php');
require_once(CONTROLLER_PATH . '/AuthController.php');
require_once(CONTROLLER_PATH . '/PostController.php');
$postInstance = new PostController($mysqli);
$postInstance->load_posts();
?>