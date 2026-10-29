<?php
// config/Helpers.php - funciones de formato compartidas

// 89218911 -> 8921-8911
function fmt_celular($n): string
{
    $d = preg_replace('/\D/', '', (string) $n);
    return strlen($d) === 8 ? substr($d, 0, 4) . '-' . substr($d, 4) : (string) $n;
}
