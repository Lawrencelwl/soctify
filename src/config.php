<?php
define('BASE_PATH', dirname(__FILE__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('MODEL_PATH', APP_PATH . '/models');
define('VIEW_PATH', APP_PATH . '/views');
define('VIEW_INC_PATH', VIEW_PATH . '/inc');
define('UTILS_PATH', APP_PATH . '/utils');

// All config files are included here
require_once(CONFIG_PATH . '/database.php');
require_once(UTILS_PATH . '/ui.php');

// Require the Composer autoloader.
require '../vendor/autoload.php';

// Start the PHP session system
session_start();