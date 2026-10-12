<?php
require_once __DIR__ . '/conector.php';

class AdministradorConfiguraciones extends Con
{
    public function listar(): array
    {
        return json_decode($this->ejecutar('SELECT * FROM configuraciones ORDER BY grupo, etiqueta'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function habilitada(string $clave): bool
    {
        $clave = $this->escapar($clave);
        $filas = json_decode($this->ejecutar("SELECT valor, tipo FROM configuraciones WHERE clave = '$clave'"), true, 512, JSON_THROW_ON_ERROR);
        return ($filas[0]['tipo'] ?? '') === 'booleano' && ($filas[0]['valor'] ?? '') === '1';
    }

    public function guardar(array $valores, int $idUsuario): void
    {
        $this->iniciarTransaccion();
        try {
            $filas = json_decode($this->ejecutar('SELECT * FROM configuraciones ORDER BY clave FOR UPDATE'), true, 512, JSON_THROW_ON_ERROR);
            $configuraciones = array_column($filas, null, 'clave');
            foreach ($valores as $clave => $valor) {
                if (!isset($configuraciones[$clave]) || !is_string($valor)) {
                    throw new InvalidArgumentException('La configuración enviada no es válida.');
                }
                $tipo = $configuraciones[$clave]['tipo'];
                if (($tipo === 'booleano' && !in_array($valor, ['0', '1'], true))
                    || ($tipo === 'entero' && filter_var($valor, FILTER_VALIDATE_INT) === false)
                    || !in_array($tipo, ['booleano', 'entero', 'texto'], true)
                    || strlen($valor) > 10000) {
                    throw new InvalidArgumentException('Revisa el valor de ' . $configuraciones[$clave]['etiqueta'] . '.');
                }
            }
            foreach ($valores as $clave => $valor) {
                if ($configuraciones[$clave]['valor'] === $valor) continue;
                $clave = $this->escapar($clave);
                $valor = $this->escapar($valor);
                $this->ejecutar("UPDATE configuraciones SET valor = '$valor', actualizado_por = $idUsuario, updated_at = NOW() WHERE clave = '$clave'");
            }
            $this->confirmarTransaccion();
        } catch (Throwable $e) {
            $this->revertirTransaccion();
            throw $e;
        }
    }
}

function almacen_puede_confirmar_sin_qr(): bool
{
    static $habilitada = null;
    if ($habilitada === null) {
        try {
            $admin = new AdministradorConfiguraciones();
            $habilitada = $admin->habilitada('almacen_confirmar_salida_sin_qr');
            $admin->cerrar();
        } catch (Throwable $e) {
            // Ante un problema de configuración, no conceder el permiso.
            error_log('No se pudo consultar la configuración de confirmación de salidas.');
            $habilitada = false;
        }
    }
    return $habilitada;
}
