<?php
// Application configuration
define('APP_NAME', 'Book Review System');
define('APP_VERSION', '1.0');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 1800); // 30 minutes

// File upload settings (if needed)
define('MAX_FILE_SIZE', 5242880); // 5MB

// Set secure session parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');
?>