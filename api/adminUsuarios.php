<?php
include_once 'conector.php';

class AdministradorUsuarios extends Con {
    public function listarUsuarios($soloActivos = false) {
        $where = $soloActivos ? 'WHERE activo = 1' : '';
        $sql = "
            SELECT id, nombre, usuario, password, email, rol, activo, created_at
            FROM usuarios
            $where
            ORDER BY nombre, usuario
        ";
        return $this->ejecutar($sql);
    }

    public function obtenerUsuario($id) {
        $id = (int)$id;
        $sql = "
            SELECT id, nombre, usuario, password, email, rol, activo, created_at
            FROM usuarios
            WHERE id = $id
            LIMIT 1
        ";
        return $this->ejecutar($sql);
    }

    public function crearUsuario($nombre, $usuario, $password, $email = '', $rol = '', $activo = 1) {
        $nombre = $this->limpiar($nombre);
        $usuario = $this->limpiar($usuario);
        $password = $this->limpiar($password);
        $email = $this->limpiar($email);
        $rol = $this->limpiar($rol);
        $activo = (int)$activo;

        $sql = "
            INSERT INTO usuarios (nombre, usuario, password, email, rol, activo)
            VALUES ('$nombre', '$usuario', '$password', " . ($email !== '' ? "'$email'" : 'NULL') . ", " . ($rol !== '' ? "'$rol'" : 'NULL') . ", $activo)
        ";

        return $this->ejecutar($sql);
    }

    public function actualizarUsuario($id, $nombre, $usuario, $password, $email = '', $rol = '', $activo = 1) {
        $id = (int)$id;
        $nombre = $this->limpiar($nombre);
        $usuario = $this->limpiar($usuario);
        $password = $this->limpiar($password);
        $email = $this->limpiar($email);
        $rol = $this->limpiar($rol);
        $activo = (int)$activo;

        $sql = "
            UPDATE usuarios
            SET
                nombre = '$nombre',
                usuario = '$usuario',
                password = '$password',
                email = " . ($email !== '' ? "'$email'" : 'NULL') . ",
                rol = " . ($rol !== '' ? "'$rol'" : 'NULL') . ",
                activo = $activo
            WHERE id = $id
        ";

        return $this->ejecutar($sql);
    }

    public function eliminarUsuario($id) {
        $id = (int)$id;
        $sql = "
            UPDATE usuarios
            SET activo = 0
            WHERE id = $id
        ";
        return $this->ejecutar($sql);
    }

    public function activarUsuario($id) {
        $id = (int)$id;
        $sql = "
            UPDATE usuarios
            SET activo = 1
            WHERE id = $id
        ";
        return $this->ejecutar($sql);
    }

    public function existeUsuario($usuario, $idExcluido = null) {
        $usuario = $this->limpiar($usuario);
        $idExcluido = (int)$idExcluido;

        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE usuario = '$usuario'";
        if ($idExcluido > 0) {
            $sql .= " AND id != $idExcluido";
        }

        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['total' => 0];
        return (int)$fila['total'] > 0;
    }

    public function existeEmail($email, $idExcluido = null) {
        $email = $this->limpiar($email);
        if ($email === '') {
            return false;
        }

        $idExcluido = (int)$idExcluido;
        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE email = '$email'";
        if ($idExcluido > 0) {
            $sql .= " AND id != $idExcluido";
        }

        $resultado = $this->ejecutar($sql);
        $fila = json_decode($resultado, true)[0] ?? ['total' => 0];
        return (int)$fila['total'] > 0;
    }

    public function autenticarPorEmail($email, $password) {
        $email = mb_strtolower($this->limpiar($email), 'UTF-8');
        $password = $this->limpiar($password);

        $sql = "
            SELECT id, nombre, usuario, password, email, rol, activo
            FROM usuarios
            WHERE LOWER(COALESCE(email, '')) = '$email'
              AND password = '$password'
              AND activo = 1
            LIMIT 1
        ";

        return $this->ejecutar($sql);
    }

    private function limpiar($valor) {
        return htmlspecialchars(trim((string)$valor), ENT_QUOTES, 'UTF-8');
    }
}
