<?php
include_once 'conector.php';

class AdministradorAreas extends Con {
    public function listarAreas() {
        $sql = "
            SELECT
                a.id,
                a.nombre,
                a.presupuesto,
                a.id_encargado,
                a.fecha_inserta,
                COALESCE(u.nombre, CONCAT('Usuario #', a.id_encargado)) AS nombre_encargado,
                u.email,
                u.rol
            FROM areas a
            LEFT JOIN usuarios u ON u.id = a.id_encargado
            ORDER BY a.nombre ASC, a.id ASC
        ";

        return $this->ejecutar($sql);
    }

    public function obtenerArea($id) {
        $id = (int)$id;
        $sql = "
            SELECT id, nombre, presupuesto, id_encargado, fecha_inserta
            FROM areas
            WHERE id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    public function crearArea($nombre, $presupuesto, $idEncargado) {
        $nombre = $this->limpiar($nombre);
        $presupuesto = (float)$presupuesto;
        $idEncargado = (int)$idEncargado;

        $sql = "
            INSERT INTO areas (nombre, presupuesto, id_encargado, fecha_inserta)
            VALUES ('$nombre', $presupuesto, $idEncargado, NOW())
        ";

        return $this->ejecutar($sql);
    }

    public function actualizarArea($id, $nombre, $presupuesto, $idEncargado) {
        $id = (int)$id;
        $nombre = $this->limpiar($nombre);
        $presupuesto = (float)$presupuesto;
        $idEncargado = (int)$idEncargado;

        $sql = "
            UPDATE areas
            SET
                nombre = '$nombre',
                presupuesto = $presupuesto,
                id_encargado = $idEncargado
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    public function eliminarArea($id) {
        $id = (int)$id;
        $sql = "DELETE FROM areas WHERE id = $id";
        return $this->ejecutar($sql);
    }

    private function limpiar($valor) {
        return htmlspecialchars(trim((string)$valor), ENT_QUOTES, 'UTF-8');
    }
}
