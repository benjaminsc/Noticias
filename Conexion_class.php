<?php
/**
*
*/
class Conexion
{
	static protected  $host = 'localhost';
	static protected  $user = 'root';
	static protected  $db = 'noticias';
	static protected $pass = '';
	public  static  $con;
	//protected $db = 'localhost';
	public static function getConexion(){
		if(is_null(self::$con)){
			self::$con = new mysqli(self::$host, self::$user, self::$pass, self::$db);
			if (self::$con->connect_error) {
		  	  die('Error de Conexión: ' .self::$con->connect_errno);
			}
		}
		return self::$con;
	}

	function __construct(){

	}
	static function  get_results($query)
	{
		$data = array();
		if (self::getConexion()->multi_query($query))
		{
	 		/* almacenar primer juego de resultados */
	        if ($result = self::getConexion()->store_result()) {
	            while ($row = $result->fetch_object()) {
	                $data[]= $row;
	            }
	            $result->free();
	        }
		}

		/* cerrar conexión */
		//$this->con->close();
		return $data;
	}
	function get_row($query){
		if (self::getConexion()->multi_query($query))
		{
	 		/* almacenar primer juego de resultados */
	        if ($result = self::getConexion()->store_result()) {
	            return $row = $result->fetch_object();

	        }
		}
		return null;

	}
	function ex_query($query)
	{
		return self::getConexion()->query($query);
	}

}



?>
