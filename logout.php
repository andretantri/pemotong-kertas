<?php
require_once 'config/database.php';
require_once 'config/auth.php';

logoutUser();
header('Location: login.php');
exit();

