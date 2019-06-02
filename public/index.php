<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

require '../vendor/autoload.php';
require '../public/config/db.php';


$config['displayErrorDetails'] = true;
$config['db']['host']   = "27.0.0.1";
$config['db']['user']   = "root";
$config['db']['pass']   = " ";
$config['db']['dbname'] = "community";


$con=new db();
$con->connection();

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};


require  '../src/customer.php';




$app->get('/user/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});
$app->run();
