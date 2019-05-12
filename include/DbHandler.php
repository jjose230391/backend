<?php
/**
 *
 * @About:      Database connection manager class
 * @File:       Database.php
 * @Date:       $Date:$ Nov-2015
 * @Version:    $Rev:$ 1.0
 * @Developer:  Federico Guzman (federicoguzman@gmail.com)
 **/
class DbHandler {
 
    private $conn;
 
    function __construct() {
		require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
		$db = new DbConnect();
		$this->conn = $db->connect();
		}
		
    public function createUser($auth)
    {
		$id = random_int(100, 10000);
		$pass = sha1($auth['pass']);
		@$sql="INSERT INTO usuarios (id, user, names, apellidos, edad, sexo, chijos, email, pass) VALUES ( '$id', '$auth[user]', '$auth[names]', '$auth[email]', '$pass')";
		$result=mysqli_query($this->conn, $sql);
		if($result){
			return true; 
		}
		else{
			return false;
		}
    }
	public function AllUsers()
    {
        //aqui puede incluir la logica para insertar el nuevo auto a tu base de datos
		@$sql = "SELECT * FROM usuarios";
		$result=mysqli_query($this->conn, $sql);
		return $result;
	}

	public function OneUsers($id)
    {
		@$sql = "SELECT * FROM usuarios where id='$id'";
		$result=mysqli_query($this->conn, $sql);
		return $result;
	}

	public function updateUser($param, $id)
    {
			if (!$param['pass']) {
				@$sql_id = "SELECT id FROM usuarios where id='$id'";
		$result_sql_id = mysqli_query($this->conn, $sql_id);
		$fetch = mysqli_fetch_array($result_sql_id);
		@$sql = "UPDATE usuarios SET user='$param[user]', names='$param[names]', apellidos='$param[apellidos]', edad='$param[edad]', sexo='$param[sexo]', chijos='$param[chijos]', email='$param[email]' where id='$id'";
		$result=mysqli_query($this->conn, $sql);
		if (isset($result)) {
			return true;
		} else {
			return false;
		}	
			}
			else {
		@$sql_id = "SELECT id FROM usuarios where id='$id'";
		$result_sql_id = mysqli_query($this->conn, $sql_id);
		$fetch = mysqli_fetch_array($result_sql_id);
		$pass = sha1($param['pass']);
		@$sql = "UPDATE usuarios SET user='$param[user]', names='$param[names]', email='$param[email]', pass='$pass' where id='$id'";
		$result=mysqli_query($this->conn, $sql);
		if (isset($result)) {
			return true;
		} else {
			return false;
		}
			}
	}

	public function deleteUser($id)
	{
		@$sql_id = "SELECT id FROM usuarios where id='$id'";
		$result_sql_id = mysqli_query($this->conn, $sql_id);
		$fetch = mysqli_fetch_array($result_sql_id);
		@$sql = "DELETE FROM usuarios where id='$id'";
		if (isset($fetch)) {
			$result = mysqli_query($this->conn, $sql);
			if ($result){
				return true;
			} else {
				return false;
			}			
		} else {
			return false;
		}
	}


    public function VerifyUser($params)
    {
		//verficar user y pass
		$results = array();
		@$sql = "SELECT * FROM usuarios WHERE user='$params[user]' and pass='$params[pass]'";
		$result=mysqli_query($this->conn, $sql);
		if ($result != null) {
			while($row = $result->fetch_assoc()) {
			$results[] = array('id'=>$row['id'], 'names'=>$row['names'], 'user'=>$row['user'],'email'=>$row['email']);
			}
			return $results;	
		}
		else{
			return null;
		}
	}
	public function VerifyToken($params)
    {
		//verficar user y pass
		$results = array();
		@$sql = "SELECT token FROM token WHERE token='$params[token]' LIMIT 1";
		$result=mysqli_query($this->conn, $sql);
		if ($result != null) {
			while($row = $result->fetch_assoc()) {
			$results['token'] = array('token'=>$row['token']);
			}
			return $results;	
		}
		else{
			return null;
		}
	}

	public function Tokens()
    {
		//verficar user y pass
		$results = array();
		@$sql = "SELECT * FROM token";
		$result=mysqli_query($this->conn, $sql);
		if ($result != null) {
			while($row = $result->fetch_assoc()) {
			$results['token'] = array('token'=>$row['token']);
			}
			return $results;	
		}
		else{
			return null;
		}
	}

	public function CreateToken($token)
    {
		@$sql="INSERT INTO token (token) VALUES ('$token')";
		$result=mysqli_query($this->conn, $sql);
		if($result){
			return true; 
		}
		else{
			return false;
		}
	}

	public function RemoveToken($params)
    {
		@$sql="DELETE FROM token WHERE token='$params[token]'";
		$result=mysqli_query($this->conn, $sql);
		if($result != null){
			return true; 
		}
		else{
			return false;
		}
    }
 
}
 
?>