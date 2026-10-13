<?php

$configuracion = [
    'vapid_subject' => getenv('CRORAM_VAPID_SUBJECT') ?: 'mailto:soporte@croram.com',
    'vapid_public_key' => getenv('CRORAM_VAPID_PUBLIC_KEY') ?: '',
    'vapid_private_key' => getenv('CRORAM_VAPID_PRIVATE_KEY') ?: '',
];

$archivoLocal = __DIR__ . '/notificaciones.local.php';
if (is_file($archivoLocal)) {
    $configuracion = array_merge($configuracion, require $archivoLocal);
}

return $configuracion;

