<?php
// logout.php
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use App\Auth;

Auth::logout();

setFlashMessage('You have been logged out successfully.', 'info');
redirect('index.php');