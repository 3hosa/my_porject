<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Unset all session variables
$_SESSION = [];
// Destroy the session on the server
session_destroy();
// Remove session cookie from client
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

header('Location: index.php');
exit;
?>
