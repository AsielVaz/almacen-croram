<?php

/** Valida cada partida por ID de detalle, incluso si se repite un producto. */
function validarRecepcionCompra(array $detalles, array $productos): array
{
    $porId = [];
    foreach ($detalles as $detalle) {
        $porId[(int)$detalle['id']] = $detalle;
    }
    if (!$porId || count($productos) !== count($porId)) {
        throw new InvalidArgumentException('Debes indicar la cantidad recibida de todas las partidas.');
    }
    $resultado = [];
    $total = 0;
    foreach ($productos as $producto) {
        if (!is_array($producto)) {
            throw new InvalidArgumentException('La partida de recepción no es válida.');
        }
        $id = filter_var($producto['id_detalle'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !isset($porId[$id]) || isset($resultado[$id])) {
            throw new InvalidArgumentException('La recepción contiene una partida desconocida o repetida.');
        }
        $detalle = $porId[$id];
        $cantidad = $producto['cantidad_recibida'] ?? null;
        $precio = $producto['precio_real'] ?? null;
        $solicitada = (float)($detalle['cantidad_solicitada'] ?? $detalle['cantidad']);
        foreach ([$cantidad, $precio] as $valor) {
            if (!is_numeric($valor) || !is_finite((float)$valor) || (float)$valor < 0
                || abs(round((float)$valor, 4) - (float)$valor) > 0.0000001) {
                throw new InvalidArgumentException('Cantidad y precio deben ser números no negativos con hasta cuatro decimales.');
            }
        }
        if ((float)$cantidad > $solicitada || (float)$precio > 99999999.9999
            || (float)$cantidad * (float)$precio > 9999999999.9999) {
            throw new InvalidArgumentException('La cantidad recibida supera la solicitada o el importe excede el límite permitido.');
        }
        $resultado[$id] = [
            'id_detalle' => $id,
            'id_producto' => (int)$detalle['id_producto'],
            'cantidad_solicitada' => $solicitada,
            'cantidad_recibida' => (float)$cantidad,
            'precio_real' => (float)$precio,
            'precio_autorizado' => (float)($detalle['precio_autorizado'] ?? $detalle['precio_unitario']),
        ];
        $total += (float)$cantidad;
    }
    if ($total <= 0) {
        throw new InvalidArgumentException('Debes recibir al menos una unidad o fracción de algún producto.');
    }
    return array_values($resultado);
}
