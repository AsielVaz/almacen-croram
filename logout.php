<?php
require_once __DIR__ . '/auth.php';
cerrar_sesion_usuario();
header('Location: login.php');
exit;
