<?php

set_time_limit(0);
$path=array(
  'short'=>__DIR__.'/../',
  'long'=>__DIR__.'/../../../../../../../',
);

(@include_once $path['short'] . 'vendor/autoload.php') || @include_once $path['long'] . 'vendor/autoload.php';
if (PHP_VERSION_ID < 70000) {
  (@include_once $path['short'] . 'var/bootstrap.php.cache') || @include_once $path['long'] . 'var/bootstrap.php.cache';
}

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

/*
 * Look both in $_SERVER, $_POST and $argv after some data.
 */
$data = null;
if (isset($_SERVER['DEFERRED_DATA'])) {
  $data = $_SERVER['DEFERRED_DATA'];
} elseif (isset($_POST['DEFERRED_DATA'])) {
  $data = $_POST['DEFERRED_DATA'];
} elseif (isset($argv)) {
  // if shell
  if (isset($argv[1])) {
    $data = urldecode($argv[1]);
  }
}
if ($data === null) {
  trigger_error('No message data found', E_USER_WARNING);
  exit(1);
}
$message = base64_decode($data);
$headers = array();
$body = null;
$lines = explode("\n", $message);
foreach ($lines as $i => $line) {
  if ($line == '') {
    $body = $lines[$i + 1];
    break;
  }
  list($name, $value) = explode(':', $line, 2);
  $headers[$name] = trim($value);
}

$input = new ArgvInput(['dispatch.php', 'fervo:deferred-event:dispatch', $body, $headers['event_name']]);

$kernel = new AppKernel('dev', true);
$application = new Application($kernel);
$application->run($input);
