<?php
// TODO: defining endpoints
// TODO: variables in endpoints
// TODO: only allow GET and POST
// TODO: token authentication
// TODO: alles in klassen verschieben

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$requestMethod = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

echo json_encode([
  'method' => $requestMethod,
  'uri' => $uri
]);
