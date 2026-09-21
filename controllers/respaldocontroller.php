<?php

class RespaldoController{
    private mysqli $conn;

    public function __construct(mysqli $conexion)
    {
        $this->conn =$conexion;
    }

    public function generarRespaldo()
    {
        $error_respaldo = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_respaldo'])) {$codigo_ingresado = $_POST['codigo_seguridad'] ?? '';$codigo_maestro = "SIGEM2026*Backup"; 

            if ($codigo_ingresado === $codigo_maestro) {$directorio_backup = __DIR__ . '/backup';
                if (!is_dir($directorio_backup)) {
                    mkdir($directorio_backup, 0777, true);
                }

                $fecha = date('Y-m-d_H-i-s');
                $nombre_archivo = "sigem_respaldo_{$fecha}.bak";
                $ruta_completa = $directorio_backup . '/' .$nombre_archivo;

                try {
                    $archivo = fopen($ruta_completa, 'w');
                    
                    fwrite($archivo, "-- ==========================================================\n");
                    fwrite($archivo, "-- Respaldo de Base de Datos MySQL (XAMPP)\n");
                    fwrite($archivo, "-- Fecha y hora: " . date('Y-m-d H:i:s') . "\n");
                    fwrite($archivo, "-- ==========================================================\n\n");
                    fwrite($archivo, "SET NAMES utf8mb4;\n");
                    fwrite($archivo, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

                    $tablas = [];
                    // Uso de mysqli: ->query()
                    $resultado_tablas =$this->conn->query("SHOW TABLES");
                    
                    // Uso de mysqli: ->fetch_row() en lugar de PDO::FETCH_NUM
                    while ($fila =$resultado_tablas->fetch_row()) {
                        $tablas[] =$fila[0];
                    }

                    foreach ($tablas as$tabla) {
                        $resultado_estructura =$this->conn->query("SHOW CREATE TABLE `$tabla`");
                        $fila =$resultado_estructura->fetch_row();
                        
                        fwrite($archivo, "\n-- --------------------------------------------------------\n");
                        fwrite($archivo, "-- Estructura de la tabla `$tabla`\n");
                        fwrite($archivo, "-- --------------------------------------------------------\n");
                        fwrite($archivo, "DROP TABLE IF EXISTS `$tabla`;\n");
                        fwrite($archivo,$fila[1] . ";\n\n");

                        $resultado_datos =$this->conn->query("SELECT * FROM `$tabla`");
                        
                        // Uso de mysqli: ->num_rows en lugar de rowCount()
                        if ($resultado_datos->num_rows > 0) {
                            fwrite($archivo, "-- Volcado de datos para la tabla `$tabla`\n");
                            
                            // Uso de mysqli: ->fetch_assoc() en lugar de PDO::FETCH_ASSOC
                            while ($fila_datos =$resultado_datos->fetch_assoc()) {
                                $valores = array_map(function($valor) {
                                    if (is_null($valor)) {
                                        return 'NULL';
                                    }
                                    // Uso de mysqli: real_escape_string en lugar de quote() de PDO
                                    return "'" . $this->conn->real_escape_string($valor) . "'";
                                }, array_values($fila_datos));

                                $valores_str = implode(", ", $valores);
                                fwrite($archivo, "INSERT INTO `$tabla` VALUES ($valores_str);\n");
                            }
                            fwrite($archivo, "\n");
                        }
                    }

                    fwrite($archivo, "SET FOREIGN_KEY_CHECKS = 1;\n");
                    fclose($archivo);

                    if (file_exists($ruta_completa)) {
                        if (ob_get_level()) {
                            ob_end_clean();
                        }
                        
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream'); 
                        header('Content-Disposition: attachment; filename="' . basename($ruta_completa) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($ruta_completa));
                        
                        readfile($ruta_completa);
                        exit;
                    } else {
                        return "El archivo se procesó pero no pudo guardarse en la carpeta backup.";
                    }

                } catch (mysqli_sql_exception $e) {
                    return "Error de base de datos MySQLi al generar respaldo: " . $e->getMessage();
                } catch (Exception $e) {
                    return "Error del sistema: " . $e->getMessage();
                }

            } else {
                return "Código de seguridad incorrecto. Acceso denegado.";
            }
        }
        
        return null;
    }
}

require_once __DIR__ . '/../config/conexion.php';

// 2. Verificamos que la variable global $conexion exista antes de usarla
if (isset($conexion)) {
    $controller = new RespaldoController($conexion);
    $resultado = $controller->generarRespaldo();

    if (is_string($resultado)) {
        http_response_code(400);
        echo $resultado;
        exit;
    }
} else {
    http_response_code(500);
    echo "Error crítico: No se pudo establecer la conexión a la base de datos.";
    exit;
}


?>