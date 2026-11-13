<?php
include_once 'conector.php';

class AdministradorProveedores extends Con {

    /* =========================
       PROVEEDORES
    ========================= */

    // Crear proveedor
    public function agregarProveedor(
        $nombre,
        $contacto = '',
        $descripcion = '',
        $rfc = '',
        $domicilio_completo = '',
        $telefono_contacto = '',
        $movil_contacto = '',
        $mail = '',
        $credito = 0,
        $plazo_credito = 0,
        $activo = 1
    ) {

        $nombre = $this->limpiar($nombre);
        $contacto = $this->limpiar($contacto);
        $descripcion = $this->limpiar($descripcion);
        $rfc = $this->limpiar($rfc);
        $domicilio_completo = $this->limpiar($domicilio_completo);
        $telefono_contacto = $this->limpiar($telefono_contacto);
        $movil_contacto = $this->limpiar($movil_contacto);
        $mail = $this->limpiar($mail);

        $credito = (int)$credito;
        $plazo_credito = (int)$plazo_credito;
        $activo = (int)$activo;
        $rfcSql = $rfc !== '' ? "'$rfc'" : 'NULL';

        $sql = "
            INSERT INTO proveedores
            (nombre, contacto, descripcion, rfc, domicilio_completo, telefono_contacto, movil_contacto, mail, credito, plazo_credito, activo)
            VALUES (
                '$nombre',
                '$contacto',
                '$descripcion',
                $rfcSql,
                '$domicilio_completo',
                '$telefono_contacto',
                '$movil_contacto',
                '$mail',
                $credito,
                $plazo_credito,
                $activo
            )
        ";

        return $this->ejecutar($sql);
    }

    // Listar proveedores
    public function listarProveedores($soloActivos = false) {

        $where = $soloActivos ? "WHERE activo = 1" : "";

        $sql = "
            SELECT
                id,
                nombre,
                contacto,
                descripcion,
                rfc,
                telefono_contacto,
                movil_contacto,
                mail,
                credito,
                plazo_credito,
                activo
            FROM proveedores
            $where
            ORDER BY nombre
        ";

        return $this->ejecutar($sql);
    }

    // Obtener proveedor por ID
    public function obtenerProveedor($id) {

        $id = (int)$id;

        $sql = "
            SELECT *
            FROM proveedores
            WHERE id = $id
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    // Actualizar proveedor
    public function actualizarProveedor(
        $id,
        $nombre,
        $contacto = '',
        $descripcion = '',
        $rfc = '',
        $domicilio_completo = '',
        $telefono_contacto = '',
        $movil_contacto = '',
        $mail = '',
        $credito = 0,
        $plazo_credito = 0,
        $activo = 1
    ) {

        $id = (int)$id;
        $nombre = $this->limpiar($nombre);
        $contacto = $this->limpiar($contacto);
        $descripcion = $this->limpiar($descripcion);
        $rfc = $this->limpiar($rfc);
        $domicilio_completo = $this->limpiar($domicilio_completo);
        $telefono_contacto = $this->limpiar($telefono_contacto);
        $movil_contacto = $this->limpiar($movil_contacto);
        $mail = $this->limpiar($mail);

        $credito = (int)$credito;
        $plazo_credito = (int)$plazo_credito;
        $activo = (int)$activo;
        $rfcSql = $rfc !== '' ? "'$rfc'" : 'NULL';

        $sql = "
            UPDATE proveedores
            SET
                nombre = '$nombre',
                contacto = '$contacto',
                descripcion = '$descripcion',
                rfc = $rfcSql,
                domicilio_completo = '$domicilio_completo',
                telefono_contacto = '$telefono_contacto',
                movil_contacto = '$movil_contacto',
                mail = '$mail',
                credito = $credito,
                plazo_credito = $plazo_credito,
                activo = $activo
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    // Eliminar proveedor (lógico)
    public function eliminarProveedor($id) {

        $id = (int)$id;

        $sql = "
            UPDATE proveedores
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

    public function contarProveedores($soloActivos = false) {
        $where = $soloActivos ? "WHERE activo = 1" : "";
        $sql = "
            SELECT COUNT(*) AS total
            FROM proveedores
            $where
        ";

        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['total' => 0];
        return (int)$fila['total'];
    }

    public function existeRfc($rfc, $idExcluido = null) {
        $rfc = $this->limpiar($rfc);
        if ($rfc === '') {
            return false;
        }

        $idExcluido = (int)$idExcluido;
        $sql = "
            SELECT COUNT(*) AS total
            FROM proveedores
            WHERE rfc = '$rfc'
        ";
        if ($idExcluido) {
            $sql .= " AND id != $idExcluido";
        }

        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['total' => 0];
        return $fila['total'] > 0;
    }
}
