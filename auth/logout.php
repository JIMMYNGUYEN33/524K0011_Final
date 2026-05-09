<?php
require_once __DIR__ . '/../helpers/auth.php';

session_unset();
session_destroy();

header('Location: ' . base_url('/auth/login.php'));
exit;
