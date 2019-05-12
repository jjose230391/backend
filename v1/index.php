<?php
/**
 *
 * @About:      API Interface
 * @File:       index.php
 * @Date:       $Date:$ Nov-2015
 * @Version:    $Rev:$ 1.0
 * @Developer:  Federico Guzman ()
 **/

/* Los headers permiten acceso desde otro dominio (CORS) a nuestro REST API o desde un cliente remoto via HTTP
 * Removiendo las lineas header() limitamos el acceso a nuestro RESTfull API a el mismo dominio
 * Nótese los métodos permitidos en Access-Control-Allow-Methods. Esto nos permite limitar los métodos de consulta a nuestro RESTfull API
 * Mas información: https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
 **/
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"'); 

include_once '../include/Config.php';

/* Puedes utilizar este file para conectar con base de datos incluido en este demo; 
 * si lo usas debes eliminar el include_once del file Config ya que le mismo está incluido en DBHandler 
 **/
require_once '../include/DbHandler.php';

require '../libs/Slim/Slim.php'; 
\Slim\Slim::registerAutoloader(); 
$app = new \Slim\Slim();


/* Usando GET para consultar los autos */

$app->get('/tokens', function() {
    
    $response = array();
	$result = array();
    $db = new DbHandler();
	$autos = $db->Tokens();

	 /* if ($autos != null) {
		 while($row = $autos->fetch_assoc()) {
		 $result[] = array('token'=>$row['token']);
		 }
	 } */
$response["results"] = $autos;


    echoResponse(200, $response);
});


/* Loguearse */

$app->get('/login', 'authorization', function() use ($app){
    // VerifyRequiredParams(array('user', 'pass'));
    // $app = \Slim\Slim::getInstance();
    //$param = json_decode($p, true);
    
    /* print_r($app->request->post('user'));
    $user = $app->request->post('user');
    $pass = $app->request->post('pass');

    $pass = sha1($pass); */
    /* $response["results"] = $user;
echoResponse(200, $response); */
    // login($user,$pass);
});

$app->get('/logout', 'token', 'remove',function(){
    $response["message"] = 'Ok';
    echoResponse(200, $response);
});
/* Fin de loguearse */

/* USUARIOS */
/* Obtener todos los usuario*/
$app->get('/users', 'token', function() {    
    $response = array();
	$result = array();
    $db = new DbHandler();
	$users = $db->AllUsers();
	 if ($users != null) {
		 while($row = $users->fetch_assoc()) {
		 $result[] = array('id'=>$row['id'], 'user'=>$row['user'],'names'=>$row['names'], 'apellidos'=>$row['apellidos'], 'edad'=>$row['edad'], 'sexo'=>$row['sexo'], 'chijos'=>$row['chijos'], 'email'=>$row['email']);
		 }
	 }
$response["results"] = $result;
echoResponse(200, $response);
});

/* Obtener datos de un user */
$app->get('/user/:id', 'token',function($id){    
    $response = array();
	$result = array();
    $db = new DbHandler();
	$user = $db->OneUsers($id);
	 if ($user != null) {
		 while($row = $user->fetch_assoc()) {
		 $result[] = array('id'=>$row['id'], 'user'=>$row['user'], 'names'=>$row['names'], 'email'=>$row['email'], 'pass'=>$row['pass']);
		 }	
     }
     if(sizeof($result) > 0) {
        $response["results"] = $result;
        echoResponse(200, $response);
     } else {
        $response["mensaje"] = 'No existe';
        echoResponse(404, $response);
     }
    
});

/* Usando POST para crear un usuario */

$app->post('/user', 'token',function() use ($app) {
    // check for required params
    // verifyRequiredParams(array('user', 'pass', 'names', 'email'));
    // validateEmail($app->request->post('email'));
    $response = array();
    /* $param['user'] = $app->request->post('user');
    $pass = $app->request->get('pass');
    $param['pass'] = sha1($pass);
    $param['names']  = $app->request->post('names');
    $param['email']  = $app->request->post('email'); */
	$p = $app->request->getBody();
    $param = json_decode($p, true);
    $db = new DbHandler();

    $user = $db->createUser($param);
    if ( isset($user) ) {
        $response["message"] = "Usuario creado satisfactoriamente!";
        $response["results"] = $user;
    } else {
        $response["error"] = true;
        $response["message"] = "Error al crear usario. Por favor intenta nuevamente.";
    }
    echoResponse(201, $response);
	
});

/* PUT usuario*/
$app->put('/user/:id', 'token',function($id) use ($app) {
    $response = array();
    /* $param['user'] = $app->request->post('user');
    $param['names'] = $app->request->post('names');
    $param['email'] = $app->request->post('email');
    $param['id'] = $id;
    $pass = $app->request->post('pass'); */
    $p = $app->request->getBody();
    $param = json_decode($p, true);    
	
    $db = new DbHandler();
    $user = $db->updateUser($param, $id);
    if ($user) {
        $response["message"] = "Usuario actualizado satisfactoriamente!";
        //$response["results"] = $user;
    } else {
        $response["error"] = true;
        $response["message"] = "Usuario no encontrado.";
        echoResponse(404, $response);
        $app->stop();
    }
    echoResponse(200, $response);
	
});

/* DELETE usuario*/
$app->delete('/user/:id', 'token',function($id) use ($app) {
    $response = array();    	
    $db = new DbHandler();
    $user = $db->deleteUser($id);
    if ($user == true ) {
        $response["message"] = "Usuario eliminado satisfactoriamente!";
        $response["results"] = $user;
    } else {
        $response["error"] = true;
        $response["message"] = "Usuario no encontrado.";
        echoResponse(404, $response);
        $app->stop();
    }
    echoResponse(200, $response);
	
});

$app->run();

/*********************** USEFULL FUNCTIONS **************************************/

function login ($user,$pass) {
    $app = \Slim\Slim::getInstance();
    $params['user'] = $user;
    $params['pass'] = $pass;
    $db = new DbHandler();
    $result = $db->VerifyUser($params);
    if ($result == null) {
        $response["error"] = true;
        $response["message"] = "User and Pass incorrect";
        echoResponse(401, $response);
        $app->stop();
    } else {
        $token = md5(random_bytes(12));
        $db->CreateToken($token);
        $response["message"] = "Acceso permitio";
        $response["token"] = $token;
        $response["result"] = $result;
        $response["s"] = $user;
        echoResponse(200, $response);
    }
}

function token ($request) {
    $app = \Slim\Slim::getInstance();
    $params['token'] = $app->request->headers->get('HTTP_TOKEN');
    $db = new DbHandler();
    $result = $db->VerifyToken($params);
    if ($result != null) {
    if (implode('',$result['token']) == $params['token']) {
        /* $response["message"] = "Acceso permitio token";
        echoResponse(200, $response);  */       
    } else {
        $response["error"] = true;
        $response["message"] = "Acceso denegadooo";
        echoResponse(403, $response);
        $app->stop();
    }
} else {
    $response["error"] = true;
    $response["message"] = "Acceso denegado";
    echoResponse(403, $response);
    $app->stop();
}

}
    
function remove () {                //elimina el token
    $app = \Slim\Slim::getInstance();
    $params['token'] = $app->request->headers->get('HTTP_TOKEN');
    $db = new DbHandler();
    $result = $db->RemoveToken($params);
    if ($result != false) {
        $response["message"] = "Token eliminado";
        echoResponse(200, $response); 
        $app->stop();       
    } else {
        $response["error"] = true;
        $response["message"] = "Token no existe";
        echoResponse(403, $response);
        $app->stop();
    }
}
/**
 * Verificando los parametros requeridos en el metodo o endpoint
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $ar = array();
    $request_params = $_REQUEST;
    // Peticiones PUT y POST com parametros
    if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'GET') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        $ar[$field] = $app->request->post('user');
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        $response["body"] = $ar;
        echoResponse(400, $response);
        
        $app->stop();
    }
}
 
/**
 * Validando parametro email si necesario; un Extra ;)
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoResponse(400, $response);
        $app->stop();
    }
}
 
function validateNumeric($number_field) {
    $app = \Slim\Slim::getInstance();
    $request_params = array();
    $request_params = $_REQUEST;
    parse_str($app->request()->getBody(), $request_params);

    foreach ($number_field as $field) {
        if (!is_numeric($request_params[$field])) {
            $response["error"] = true;
            $response["message"] = 'field is not number'.' '. $field;
            echoResponse(400, $response);
            $app->stop();
        }
    }    
}

/**
 * Mostrando la respuesta en formato json al cliente o navegador
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
    $app->stop();
}

/**
 * Agregando un leyer intermedio e autenticación para uno o todos los metodos, usar segun necesidad
 * Revisa si la consulta contiene un Header "Authorization" para validar
 */
function tokenauthorization(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    $headers = $app->request->headers;
    $response = array();
    if (isset($headers['Authorization'])) {
        $token = $headers['Authorization'];
        if (!($token == API_KEY)) { //API_KEY declarada en Config.php
            
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);
            
            $app->stop(); //Detenemos la ejecución del programa al no validar
            
        }
    }
}
function authorization(\Slim\Route $route) {
    // Getting request headers
    $app = \Slim\Slim::getInstance();
    // $headers = apache_request_headers();
    
    $headers = $app->request->headers;
    //print_r($headers['Authorization']);
    $response = array();
    

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        // $db = new DbHandler(); //utilizar para manejar autenticacion contra base de datos
 
        // get the api key
        $token = $headers['Authorization'];
        
        // validating api key
        if (!($token == API_KEY)) { //API_KEY declarada en Config.php
            
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);
            
            $app->stop(); //Detenemos la ejecución del programa al no validar
            
        } else {
            //procede utilizar el recurso o metodo del llamado
            $user = $app->request->headers->get('HTTP_USER');
            $pass = $app->request->headers->get('HTTP_PASS');
            $pass = sha1($pass);

            $db = new DbHandler();
            $params['user'] = $user;
            $params['pass'] = $pass;
            $db = new DbHandler();
            $result = $db->VerifyUser($params);
            if ($result == null) {
                $response["error"] = true;
                $response["message"] = "User and Pass incorrect";
                echoResponse(401, $response);
                $app->stop();
            } else {
                $token = md5(random_bytes(12));
                $db->CreateToken($token);
                $response["message"] = "Acceso permitio";
                $response["token"] = $token;
                $response["result"] = $result;
                echoResponse(200, $response);
            }
            /* $isValid = login($user, $pass);
            if (!$isValid) {
                $response["error"] = true;
                $response["messages"] = $user;
                $response["message"] = "Acceso denegadooo";
                echoResponse(401, $response);
                $app->stop();
            } */
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Falta token de autorización";
        $response["body"] = $app->request->headers->get('HTTP_AUTHORIZATION');
        echoResponse(400, $response);
        
        $app->stop();
    }
}

function author(\Slim\Route $route) {
    // Getting request headers
    $app = \Slim\Slim::getInstance();
    // $headers = apache_request_headers();
    
    $headers = $app->request->headers;
    //print_r($headers['Authorization']);
    $response = array();
    
 
    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        // $db = new DbHandler(); //utilizar para manejar autenticacion contra base de datos
 
        // get the api key
        $token = $headers['Authorization'];
        
        // validating api key
        if (!($token == API_KEY)) { //API_KEY declarada en Config.php
            
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);
            
            $app->stop(); //Detenemos la ejecución del programa al no validar
            
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Falta token de autorización";
        $response["body"] = $app->request->headers->get('HTTP_AUTHORIZATION');
        echoResponse(400, $response);
        
        $app->stop();
    }
}
?>