<?php
// models/IntentosLoginModel.php
// Control de intentos fallidos de login por dirección IP: 3 fallos = bloqueo de 5 minutos.

class IntentosLoginModel
{
    const MAX_INTENTOS = 3;
    const MINUTOS_BLOQUEO = 5;
    const MINUTOS_OLVIDO = 15;   // los fallos sueltos se olvidan pasado este tiempo

    private $db;
    private $ip;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
        $this->ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'), 0, 45);

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `login_intentos` (
                `ip` varchar(45) NOT NULL,
                `intentos` int(11) NOT NULL DEFAULT 0,
                `bloqueado_hasta` datetime DEFAULT NULL,
                `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`ip`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    // ['bloqueado' => bool, 'segundos' => int, 'restantes' => int]
    public function estado(): array
    {
        $stmt = $this->db->prepare(
            "SELECT intentos, TIMESTAMPDIFF(SECOND, NOW(), bloqueado_hasta) AS seg
             FROM login_intentos WHERE ip = ?"
        );
        $stmt->bind_param('s', $this->ip);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($fila && $fila['seg'] !== null) {
            if ((int) $fila['seg'] > 0) {
                return ['bloqueado' => true, 'segundos' => (int) $fila['seg'], 'restantes' => 0];
            }
            $this->limpiar();   // el bloqueo ya terminó
            $fila = null;
        }
        $intentos = $fila ? (int) $fila['intentos'] : 0;
        return ['bloqueado' => false, 'segundos' => 0, 'restantes' => max(0, self::MAX_INTENTOS - $intentos)];
    }

    // Registra un intento fallido y devuelve el estado resultante
    public function registrarFallo(): array
    {
        // Olvida fallos antiguos que no llegaron a bloquear
        $stmt = $this->db->prepare(
            "UPDATE login_intentos SET intentos = 0
             WHERE ip = ? AND bloqueado_hasta IS NULL
               AND ultimo_intento < DATE_SUB(NOW(), INTERVAL " . self::MINUTOS_OLVIDO . " MINUTE)"
        );
        $stmt->bind_param('s', $this->ip);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->db->prepare(
            "INSERT INTO login_intentos (ip, intentos, ultimo_intento) VALUES (?, 1, NOW())
             ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()"
        );
        $stmt->bind_param('s', $this->ip);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->db->prepare("SELECT intentos FROM login_intentos WHERE ip = ?");
        $stmt->bind_param('s', $this->ip);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ((int) $fila['intentos'] >= self::MAX_INTENTOS) {
            $stmt = $this->db->prepare(
                "UPDATE login_intentos
                 SET intentos = 0, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL " . self::MINUTOS_BLOQUEO . " MINUTE)
                 WHERE ip = ?"
            );
            $stmt->bind_param('s', $this->ip);
            $stmt->execute();
            $stmt->close();
        }
        return $this->estado();
    }

    public function limpiar(): void
    {
        $stmt = $this->db->prepare("DELETE FROM login_intentos WHERE ip = ?");
        $stmt->bind_param('s', $this->ip);
        $stmt->execute();
        $stmt->close();
    }
}
