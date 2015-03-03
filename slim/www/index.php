<?php
require '../vendor/autoload.php';

// Include the app configuration file.
// require_once dirname(dirname(__FILE__)) . '/app/config.php';
// Include the DBHandler class.
// require_once dirname(dirname(__FILE__)) . '/app/lib/DbHandler.php';
// Include the Helper functions file.
require_once dirname(dirname(__FILE__)) . '/app/lib/helper.php';

$app = new \Slim\Slim(array(
    'templates.path' => '../templates',
));
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

// MIDDLEWARES
require dirname(dirname(__FILE__)) . '/app/middleware/middleware.php';

// ROUTES
require dirname(dirname(__FILE__)) . '/app/routes/routes.php';

// the default root endpoint
$app->get('/', function() use ($app) {
  $app->render('routes/index.html.twig', array(
    'page_title' => 'SlimPHP Skeleton App'
    ));
});

// example of endpoint that return json
$app->get('/json-example', function() use ($app) {
  try {
    $data = array('hello' => 'world');
  }
  catch(Exception $e) {
    $data = array(
      'error' => array(
        'message' => $e->getMessage(),
      ),
    );
  }

  setResponse(200, $data);
});

$app->run();

