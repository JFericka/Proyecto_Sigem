-- Devuelve las contraseñas de los 3 usuarios de ejemplo a texto normal.
-- Ejecutar en phpMyAdmin > bodega_municipal > pestaña SQL.
UPDATE `usuarios` SET `password` = 'admin123'   WHERE `id_usuario` = 1;  -- Carlos Admin
UPDATE `usuarios` SET `password` = 'entrada123' WHERE `id_usuario` = 2;  -- María Entradas
UPDATE `usuarios` SET `password` = 'salida123'  WHERE `id_usuario` = 3;  -- Juan Salidas
