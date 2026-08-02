<?php
declare(strict_types=1);
session_start();
unset($_SESSION['google_calendar_token']);
header('Location: ../calendario.php');
exit;
