<?php
if(!defined('ABSPATH')) exit;
final class Panje_Log {
 public static function write(string $level,string $module,string $message,array $context=[]): void {
  global $wpdb; $allowed=['INFO','WARNING','ERROR','CRITICAL']; $level=strtoupper($level);
  if(!in_array($level,$allowed,true)) $level='INFO';
  $wpdb->insert($wpdb->prefix.'panje_logs',[
   'level'=>$level,'module'=>sanitize_key($module),'message'=>sanitize_text_field($message),
   'request_uuid'=>isset($context['request_uuid'])?sanitize_text_field((string)$context['request_uuid']):null,
   'user_id'=>isset($context['user_id'])?(int)$context['user_id']:null,
   'context'=>wp_json_encode($context,JSON_UNESCAPED_UNICODE),'created_at'=>current_time('mysql')
  ]);
 }
 public static function info(string $m,string $msg,array $c=[]):void{self::write('INFO',$m,$msg,$c);}
 public static function error(string $m,string $msg,array $c=[]):void{self::write('ERROR',$m,$msg,$c);}
}
