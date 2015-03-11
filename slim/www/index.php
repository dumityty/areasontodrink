<?php
session_cache_limiter(false);
session_start();

require '../vendor/autoload.php';
require '../app/rb.php';
require '../app/config.php';


R::setup('mysql:host=localhost; dbname=' . $db_name, $db_user, $db_pass);

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
  // maybe time the last time it took a count of reasons
  // so you can refresh the total number of reasons after 5 minutes or so
  // what happens at the beginning when there are few reasons
  // take that into account
  // maybe <5 ? always refresh the count if number is small
  if (!isset($_SESSION['count_reasons']) || $_SESSION['count_reasons'] < 5) {
    $count_reasons =R::count('reasons');
    $_SESSION['count_reasons'] = (int)$count_reasons;
  }

  // $rid = 1;
  // $reason = R::findOne('reasons', 'id = :rid', array(':rid' => $rid));

  // when selecting perhaps add the reason in a table of most recent ones
  // then when randomly selecting don't select the same ones that have
  // been selected the past 3 times or so
  if (isset($_SESSION['queue'])) {
    $queue = implode(',',$_SESSION['queue']);
    krumo($queue);
    $sql = "SELECT r.id,r.reason FROM reasons r LEFT JOIN reported re ON r.id = re.rid WHERE (r.id NOT IN ($queue)) AND (re.id IS NULL) ORDER BY RAND() LIMIT 1";
  }
  else {
    $queue = '';
    $sql = "SELECT r.id,r.reason FROM reasons r LEFT JOIN reported re ON r.id = re.rid WHERE re.id IS NULL ORDER BY RAND() LIMIT 1";
  }
  // $queue = $_SESSION['queue'];
  // krumo($queue);
  // $reason = R::getRow('SELECT r.id,r.reason FROM reasons r WHERE r.id NOT IN (:queue) ORDER BY RAND() LIMIT 1', array('queue' => $queue));
  // $place_holders = implode(',', array_fill(0, count($queue), '?'));
  // $reason = R::getRow('SELECT r.id,r.reason FROM reasons r WHERE r.id NOT IN ($place_holders) ORDER BY RAND() LIMIT 1', array($queue));
  
  $reason = R::getRow($sql);
  // krumo($reason);
  if ($reason != null) {
    add_reason_queue($reason['id']);
    clean_reason_queue();
  }
  else {
    purge_queue();
    $app->flashNow('info', 'Sorry! No reasons!');
  }
  
  $app->render('routes/index.html.twig', array(
    'reason' => $reason,
  ));
})->name('home');

function add_reason_queue($rid) {
  if (isset($rid)) {
    // $sql = "INSERT INTO queue (rid) VALUES (:rid)";
    // R::exec($sql, array(':rid' => $rid));
    if ((!isset($_SESSION['queue'])) || (isset($_SESSION['queue']) && !in_array($rid, $_SESSION['queue']))) {
      $_SESSION['queue'][] = $rid;
    }
  }
}

function clean_reason_queue() {
  // $sql  = "DELETE FROM queue WHERE id NOT IN (SELECT id FROM (SELECT id FROM queue ORDER BY id DESC LIMIT 5) q)";
  // R::exec($sql);

  
  $limit_reasons = $_SESSION['count_reasons'] / 2;

  // if we reached the limit of reasons to keep in queue 
  // (half of total number of reasons in this case)
  // then start removing elements from the beginning of the array
  if (count($_SESSION['queue']) > $limit_reasons) {
    array_shift($_SESSION['queue']);
  }

}

function purge_queue() {
  unset($_SESSION['queue']);  
}

$app->get('/add', function() use ($app) {
  $app->render('routes/add.html.twig', array(
    'page_title' => 'SlimPHP Skeleton App'
  ));
})->name('add');

$app->post('/add', function() use ($app) {
  $post_data = $app->request->post();

  $reason = R::dispense('reasons');
  $reason->reason = $post_data['reason'];
  $reason->created = time();
  $id = R::store($reason);

  $app->redirectTo('home');
});

$app->get('/why', function() use ($app) {
  $app->render('routes/why.html.twig', array(
  ));
})->name('why');

$app->get('/report/:rid', function($rid) use ($app) {
  // $sql = "INSERT INTO reported (rid) VALUES (:rid)";
  // R::exec($sql, array(':rid' => $rid));

  $reported = R::dispense('reported');
  $reported->rid = $rid;
  $id = R::store($reported);

  $app->flash('info', 'Reason has been reported.');
  $app->redirectTo('home');
})->name('report');

$app->run();

