<?php
require_once '../config/auth.php';
startSession();
session_destroy();
header('Location: ../index.php');
exit;
