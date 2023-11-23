<?php
// se utiliza para imprimir los errores en la consola, respecto a las peticiones post
error_log(print_r($_POST, true));

// incluimos el archivo para activar la conexion de la base de datos mientras se ejecutan los scripts
require_once 'database.php';
// incluimos el archivo para activar verificar los ruts ingresados.
include('validaterut.php');


// Manejar obtiene la accion entregada o solicitada por ajax, dependiendo de la accion se hara
// algo diferente
if (isset($_GET['accion'])) {
    // obtiene la accion
    $accion = $_GET['accion'];

    // identifica cuál es la acción y ejecuta la función correspondiente
    switch ($accion) {
        case 'obtenerRegiones':
            obtenerRegiones();
            break;

        case 'obtenerProvincias':
            if (isset($_GET['idRegion'])) {
                $idRegion = $_GET['idRegion'];
                obtenerProvincias($idRegion);
            } else {
                // Manejar el caso de falta del id de la Región
                echo json_encode(array('error' => 'Falta el parámetro idRegion.'));
            }
            break;
        case 'obtenerComunas':
            if (isset($_GET['idProvincia'])) {
                $idProvincia = $_GET['idProvincia'];
                obtenerComunas($idProvincia);
            } else {
                // Manejar el caso de falta del id de la provincia
                echo json_encode(array('error' => 'Falta el parámetro idProvincia.'));
            }
            break;
        case 'obtenerCandidatos':
            obtenerCandidatos();
            break;
            case 'insertarVotante':
                // Verifica si se enviaron datos del formulario
                if (
                    isset($_POST['nombre'])
                    && isset($_POST['alias'])
                    && isset($_POST['rut'])
                    && isset($_POST['email'])
                    && isset($_POST['comuna'])
                    && isset($_POST['candidato'])
                ) {
                    // Obtiene los datos del formulario
                    $nombre = $_POST['nombre'];
                    $alias = $_POST['alias'];
                    $rut = $_POST['rut'];
                    $email = $_POST['email'];
                    $comuna = $_POST['comuna'];
                    $candidato = $_POST['candidato'];
                    $web = isset($_POST['web']) ? $_POST['web'] : 0;
                    $tv = isset($_POST['tv']) ? $_POST['tv'] : 0;
                    $socialnet = isset($_POST['socialnet']) ? $_POST['socialnet'] : 0;
                    $friend = isset($_POST['friend']) ? $_POST['friend'] : 0;
            
                    //primero verifica si el rut  es valido, es decir que si el rut existe.
                    if (!validarRut($rut)) {
                        echo json_encode(array('error' => 'rut', 'mensaje' => 'El RUT ingresado no es válido.'));
                        break;
                    }
            
                    // Verificar que el RUT no esté ya registrado en la base de datos
                    if (rutNoRegistrado($rut)) {
                        // Inserta los datos en la base de datos
                        insertarVotante($nombre, $alias, $rut, $email, $comuna, $candidato, $web, $tv, $socialnet, $friend);
                    
                        // Responde con un mensaje de éxito
                        echo json_encode(array('mensaje' => 'Datos del votante insertados correctamente.'));
                    } else {
                        echo json_encode(array('error' => 'rut', 'mensaje' => 'El RUT ingresado ya está registrado en la base de datos.'));
                    }
                } else {
                    // Maneja el caso de falta de parámetros
                    echo json_encode(array('error' => 'Faltan parámetros del formulario.'));
                }
                break;
            // ...

    }
} else {
    // Manejar el caso de falta de acción
    echo json_encode(array('error' => 'Falta el parámetro acción.'));
}

function insertarVotante($nombre, $alias, $rut, $email, $comuna, $candidato, $web, $tv, $socialnet, $friend)
{
    $db = new Database();
    //se realiza el statement para ingresar los datos a la tabla creada 
    $stmt = $db->getConn()->prepare("INSERT INTO Votante (nombre, alias, rut, email, comuna_id, candidato_id, web, tv, redes_sociales, amigo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //luego se asigna las variables correspondientes en el orden correcto, ademas se prepara para recibir tipos de datos especificos, es decir para recibir
    //en este caso int o string, para evitar inyeccions sql
    $stmt->bind_param("ssssiiiiii", $nombre, $alias, $rut, $email, $comuna, $candidato, $web, $tv, $socialnet, $friend);

    if ($stmt->execute()) {
        
        $stmt->close();

       
        $db->closeConnection();

        echo json_encode(array('mensaje' => 'Datos del votante insertados correctamente.'));
    } else {
       
        $stmt->close();
        $db->closeConnection();

        echo json_encode(array('error' => 'Error al insertar datos del votante.'));
    }
}

// Función que obtiene todas las regiones
function obtenerRegiones()
{
    // Inicia la instancia de la base de datos
    $db = new Database();
    // Hace la consulta de la base de datos e inicia la conexión para ejecutar la query.
    $sql = "SELECT * FROM Regiones";
    $result = $db->getConn()->query($sql);

    // Inicia una variable regiones que es un array,
    $regiones = array();

    // Recorre los resultados de las consultas y los guarda en el array uno por uno
    while ($row = $result->fetch_assoc()) {
        $regiones[] = $row;
    }

    // Por seguridad se cierra la base de datos
    $db->closeConnection();

    // Se convierte en json  el array.
    echo json_encode($regiones);
}

// Obtiene todas las provincias a través del id de la región
function obtenerProvincias($idRegion)
{
    $db = new Database();

    // La consulta es similar a la anterior, sin embargo tan solo carga las provincias que cumplan la condición de
    // tener el id de la región como llave foránea
    $sql = "SELECT * FROM Provincias WHERE region_id = $idRegion";
    $result = $db->getConn()->query($sql);

    $provincias = array();
    while ($row = $result->fetch_assoc()) {
        $provincias[] = $row;
    }

    $db->closeConnection();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($provincias);
}

// Obtiene todas las provincias a través del id de la región
function obtenerComunas($idProvincia)
{
    $db = new Database();
    // La consulta es similar a la anterior, sin embargo tan solo carga las provincias que cumplan la condición de
    // tener el id de la provincia como llave foránea
    $sql = "SELECT * FROM Comunas WHERE provincia_id = $idProvincia";
    $result = $db->getConn()->query($sql);

    $comunas = array();
    while ($row = $result->fetch_assoc()) {
        $comunas[] = $row;
    }

    $db->closeConnection();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($comunas);
}

// Obtiene todas los candidatos que están en la base de datos y en la tabla de ese nombre, esencialmente hace lo mismo que las demás.
function obtenerCandidatos()
{
    $db = new Database();
    $sql = "SELECT * FROM Candidatos";
    $result = $db->getConn()->query($sql);

    $candidatos = array();
    while ($row = $result->fetch_assoc()) {
        $candidatos[] = $row;
    }

    $db->closeConnection();

    echo json_encode($candidatos);
}

//consulta sql, que compara el rut ingresado con los que hay en la base de datos, si 
function rutNoRegistrado($rut) {
    $db = new Database();
    $stmt = $db->getConn()->prepare("SELECT COUNT(*) FROM Votante WHERE rut = ?");
    $stmt->bind_param("s", $rut);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $db->closeConnection();

    return $count == 0;
}