<?php

class Con {
    private mysqli $conexion;

    public function __construct() {
        $this->conexion = new mysqli('162.241.62.205', 'grupo465_almacen', 'fGJ~9uhTggk%', 'grupo465_almacen');

        if ($this->conexion->connect_error) {
            throw new Exception(json_encode([
                "status" => "error",
                "message" => "Error al conectar a la base de datos: " . $this->conexion->connect_error,
            ]));
        }

        $this->conexion->set_charset('utf8mb4');
    }

    public function ejecutar(string $query): string {
        $resultado = $this->conexion->query($query);

        if (!$resultado) {
            throw new Exception(json_encode([
                "status" => "error",
                "message" => $this->conexion->error,
            ]));
        }

        // Verificar si es una consulta SELECT (devuelve un objeto mysqli_result)
        if ($resultado instanceof mysqli_result) {
            $datos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $datos[] = $fila;
            }
            $resultado->free(); // Liberar los recursos del resultado
            return json_encode($datos);
        }

        // Para consultas que no devuelven resultados (INSERT, UPDATE, DELETE)
        return json_encode(["status" => "ok"]);
    }

    public function cerrar(): void {
        $this->conexion->close();
    }

    public function ultimoId(): int {
        return (int)$this->conexion->insert_id;
    }

    public function iniciarTransaccion(): void {
        $this->conexion->begin_transaction();
    }

    public function confirmarTransaccion(): void {
        $this->conexion->commit();
    }

    public function revertirTransaccion(): void {
        $this->conexion->rollback();
    }

    public function escapar(string $valor): string {
        return $this->conexion->real_escape_string($valor);
    }
}
