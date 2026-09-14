<?php
if (PHP_SAPI !== 'cli') exit(1);
$root=realpath($argv[1]??''); if(!$root||!is_file($root.'/wp-load.php')) exit('WordPress root required'.PHP_EOL);
define('DISABLE_WP_CRON',true); $_SERVER['HTTP_HOST']='panje-test.local'; $_SERVER['REQUEST_METHOD']='GET'; require $root.'/wp-load.php';
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']); if(!$admins) exit('no admin'.PHP_EOL); wp_set_current_user((int)$admins[0]);
$_GET=['paged'=>1,'s'=>'']; ob_start(); Panje_Admin::users(); $html=ob_get_clean();
if(strpos($html,'panje-user-search')===false||strpos($html,'تعداد کاربران')===false) exit('admin users render failed'.PHP_EOL);
echo "PASS: admin user directory render\n";
