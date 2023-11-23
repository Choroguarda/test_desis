<?php


class Database {
    //le asigno las credenciales correspondientes a la base de datos
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "desis";
    protected $conn; 
    //le entrego las credenciales e intenta conectarse a la base de datos, en caso de que algo este malo tirara un error
    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
       
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        } 
    }
    
    //funcion para poder abrir la conexion de mysql fuera de este 
    public function getConn() {
        return $this->conn;
    }
     //funcion para poder cerrar la conexion de mysql fuera de este
    public function closeConnection() {
        $this->conn->close();
    }
}

?>
