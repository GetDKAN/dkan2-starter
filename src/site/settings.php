<?php

foreach (scandir(__DIR__) as $file) {
  if (preg_match("/settings\..*\.php/", $file) == TRUE
    && substr_count($file, "default") == 0) {
    include __DIR__ . "/{$file}";
  }
}$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => '123',
  'prefix' => '',
  'host' => 'db',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['hash_salt'] = 'tTEqIkcvsy4oV1LA2AsLxbzML184mY-qQRdLo8LZshe9lQcw6UgTUR68cvvG0S8Q2wekkK5iuw';
$config_directories['sync'] = 'sites/default/files/config_kZFagKXiMlrGHhLDIoq9nc_rcKLGmo6COOiky4QILd-F2tA87hjwWqmiEbhgSp6UtaOEHCZrWA/sync';
