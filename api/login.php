<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../backend/controller/UsuarioController.php';
require_once '../backend/model/Usuario.php';
require_once '../backend/config/Database.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = explode( '/', $uri );
/*
array(4) {
  [0]=>
  string(0) ""
  [1]=>
  string(3) "api"
  [2]=>
  string(9) "index.php"
  [3]=>
  string(8) "usuarios"
}
*/

if ($uri[3] !== 'usuarios') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$userId = null;
if (isset($uri[2])) {
    $userId = $uri[2];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$db = new Database();
$usuario = new Usuario();
$controller = new UsuarioController($db,$usuario);


switch ($requestMethod) {
    // case 'GET':
    //     if (isset($_GET['username'])) {
    //         $username = $_GET['username'];
    //         $response = $controller->getByUsername($username);
    //     } elseif ($userId) {
    //         $response = $controller->get($userId);
    //     } else {
    //         $response = $controller->getAll();
    //     };
    //     break;
    
    case 'POST':
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $response = $controller->verificarLogin($data);
        break;
    case 'PUT':
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $response = $controller->update($userId, $data);
        break;
    case 'DELETE':
        $response = $controller->delete($userId);
        break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        exit();
        break;
}
echo json_encode($response);

?>
