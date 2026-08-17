<?php

function normalizar_texto_bd($valor): string
{
    $texto = trim((string)$valor);

    // Algunos archivos y formularios pueden enviar entidades HTML ya codificadas.
    // La base de datos conserva texto real; el escape HTML corresponde a la vista.
    for ($intento = 0; $intento < 3; $intento++) {
        $decodificado = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decodificado === $texto) {
            break;
        }
        $texto = $decodificado;
    }

    $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texto) ?? $texto;
}
