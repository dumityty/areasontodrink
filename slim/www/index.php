<?php
require '../vendor/autoload.php';
// require '../vendor/rb.php';

// R::setup('mysql:host=localhost; dbname=areasontodrink', 'root', 'titi');

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
  // $rid = 1;
  // $reason = R::findOne('reasons', 'id = :rid', array(':rid' => $rid));

  // krumo($reason);
  // krumo('aa');

  $app->render('routes/index.html.twig', array(
    'page_title' => 'SlimPHP Skeleton App'
    ));
});

$app->get('/add', function() use ($app) {
  // krumo($app->request->get());
  $app->render('routes/add.html.twig', array(
    'page_title' => 'SlimPHP Skeleton App'
  ));
});

$app->post('/add', function() use ($app) {
  $post_data = $app->request->post();
  krumo($post_data);

  // $app->render('routes/add.html.twig', array(
  //   'page_title' => 'SlimPHP Skeleton App'
  // ));
});

$app->run();

