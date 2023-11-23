<?php

//funcion matetica hecha para verificar si el rut es verdadero o no
function validarRut($rut) {
    $rut = preg_replace('/[^k0-9]/i', '', $rut);
    $dv  = substr($rut, -1);
    $numero = substr($rut, 0, strlen($rut)-1);
    $i = 2;
    $suma = 0;
    foreach(array_reverse(str_split($numero)) as $v) {
        if($i == 8) $i = 2;
        $suma += $v * $i;
        ++$i;
    }
    $dvr = 11 - ($suma % 11);
    if($dvr == 11) $dvr = 0;
    if($dvr == 10) $dvr = 'K';
    return $dvr == strtoupper($dv);
}

$response = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST["rut"];

    // Limpia y valida el RUT
    $rut = preg_replace('/[^k0-9]/i', '', $rut);

    if (validarRut($rut)) {
        $response = "El RUT ingresado ($rut) es válido.";
    } else {
        $response = "El RUT ingresado ($rut) no es válido.";
    }
}

echo $response;

?>
