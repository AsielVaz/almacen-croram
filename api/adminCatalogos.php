<?php
include_once 'conector.php';

class AdministradorCatalogos extends Con {

    /* =========================
       FAMILIAS
    ========================= */

    // Crear familia
    public function agregarFamilia($nombre, $descripcion = '', $activo = 1) {

        $nombre = $this->limpiar($nombre);
        $descripcion = $this->limpiar($descripcion);
        $activo = (int)$activo;

        $sql = "
            INSERT INTO familias (nombre, descripcion, activo)
            VALUES ('$nombre', '$descripcion', $activo)
        ";

        return $this->ejecutar($sql);
    }

    // Listar familias
    public function listarFamilias($soloActivas = false) {

        $where = $soloActivas ? "WHERE activo = 1" : "";

        $sql = "
            SELECT id, nombre, descripcion, activo, created_at
            FROM familias
            $where
            ORDER BY nombre
        ";

        return $this->ejecutar($sql);
    }

    // Obtener familia por ID
    public function obtenerFamilia($id) {

        $id = (int)$id;

        $sql = "
            SELECT id, nombre, descripcion, activo
            FROM familias
            WHERE id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    // Actualizar familia
    public function actualizarFamilia($id, $nombre, $descripcion = '', $activo = 1) {

        $id = (int)$id;
        $nombre = $this->limpiar($nombre);
        $descripcion = $this->limpiar($descripcion);
        $activo = (int)$activo;

        $sql = "
            UPDATE familias
            SET nombre = '$nombre',
                descripcion = '$descripcion',
                activo = $activo
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    // Eliminar familia (lógico)
    public function eliminarFamilia($id) {

        $id = (int)$id;

        $sql = "
            UPDATE familias
            SET activo = 0
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    /* =========================
       SUBFAMILIAS
    ========================= */

    // Crear subfamilia
    public function agregarSubfamilia($id_familia, $nombre, $descripcion = '', $activo = 1) {

        $id_familia = (int)$id_familia;
        $nombre = $this->limpiar($nombre);
        $descripcion = $this->limpiar($descripcion);
        $activo = (int)$activo;

        $sql = "
            INSERT INTO subfamilias (id_familia, nombre, descripcion, activo)
            VALUES ($id_familia, '$nombre', '$descripcion', $activo)
        ";

        return $this->ejecutar($sql);
    }

    // Listar subfamilias
    public function listarSubfamilias($id_familia = null, $soloActivas = false) {

        $where = [];

        if ($id_familia !== null) {
            $where[] = "s.id_familia = " . (int)$id_familia;
        }

        if ($soloActivas) {
            $where[] = "s.activo = 1";
        }

        $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "
            SELECT
                s.id,
                s.nombre,
                s.descripcion,
                s.activo,
                s.id_familia,
                f.nombre AS familia
            FROM subfamilias s
            JOIN familias f ON f.id = s.id_familia
            $whereSql
            ORDER BY s.nombre
        ";

        return $this->ejecutar($sql);
    }

    // Obtener subfamilia por ID
    public function obtenerSubfamilia($id) {

        $id = (int)$id;

        $sql = "
            SELECT id, id_familia, nombre, descripcion, activo
            FROM subfamilias
            WHERE id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    // Actualizar subfamilia
    public function actualizarSubfamilia($id, $id_familia, $nombre, $descripcion = '', $activo = 1) {

        $id = (int)$id;
        $id_familia = (int)$id_familia;
        $nombre = $this->limpiar($nombre);
        $descripcion = $this->limpiar($descripcion);
        $activo = (int)$activo;

        $sql = "
            UPDATE subfamilias
            SET id_familia = $id_familia,
                nombre = '$nombre',
                descripcion = '$descripcion',
                activo = $activo
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    // Eliminar subfamilia (lógico)
    public function eliminarSubfamilia($id) {

        $id = (int)$id;

        $sql = "
            UPDATE subfamilias
            SET activo = 0
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    /* =========================
       UTILIDAD
    ========================= */

    private function limpiar($valor) {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}
