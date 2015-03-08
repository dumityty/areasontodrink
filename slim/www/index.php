<?php
require '../vendor/autoload.php';
require '../vendor/rb.php';

R::setup('mysql:host=localhost; dbname=areasontodrink', 'root', 'titi');

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

  // when selecting perhaps add the reason in a table of most recent ones
  // then when randomly selecting don't select the same ones that have
  // been selected the past 3 times or so
  $reason = R::getRow('SELECT r.id,r.reason FROM reasons r LEFT JOIN queue q ON r.id = q.rid WHERE q.rid IS NULL ORDER BY RAND() LIMIT 1');
  
  // krumo($reason);

  add_reason_queue($reason['id']);
  clean_reason_queue();
  
  $app->render('routes/index.html.twig', array(
    'page_title' => 'SlimPHP Skeleton App',
    'reason' => $reason,
  ));
})->name('home');

function add_reason_queue($rid) {
  $sql = "INSERT INTO queue (rid) VALUES (:rid)";
  R::exec($sql, array(':rid' => $rid));
}

function clean_reason_queue() {
  $sql  = "DELETE FROM queue WHERE id NOT IN (SELECT id FROM (SELECT id FROM queue ORDER BY id DESC LIMIT 5) q)";
  R::exec($sql);
}

$app->get('/add', function() use ($app) {
  // krumo($app->request->get());
  $app->render('routes/add.html.twig', array(
    'page_title' => 'SlimPHP Skeleton App'
  ));
})->name('add');

$app->post('/add', function() use ($app) {
  $post_data = $app->request->post();
  krumo($post_data);

  $reason = R::dispense('reasons');
  $reason->reason = $post_data['reason'];
  $reason->created = time();
  $id = R::store($reason);

  krumo($id);

  $app->redirectTo('home');

  // $app->render('routes/add.html.twig', array(
  //   'page_title' => 'SlimPHP Skeleton App'
  // ));
});

$app->get('/why', function() use ($app) {

})->name('why');

$app->run();

