<?php
if(!defined('ABSPATH')) exit;

final class Panje_API {
 public static function configured(): bool {
  $s=Panje_DB::settings();
  return !empty($s['api_url']) && !empty($s['api_key']);
 }

 public static function health(){
  $s=Panje_DB::settings();
  if(!$s['api_url']||!$s['api_key'])return new WP_Error('panje_api_config','Python API تنظیم نشده است');
  $start=microtime(true);
  $r=wp_remote_get(rtrim($s['api_url'],'/').'/api/v1/health',[
   'timeout'=>min(15,(int)$s['timeout']),
   'headers'=>['X-Panje-API-Key'=>$s['api_key'],'Cache-Control'=>'no-store']
  ]);
  if(is_wp_error($r))return $r;
  $code=wp_remote_retrieve_response_code($r);
  $body=json_decode(wp_remote_retrieve_body($r),true);
  if($code<200||$code>=300||!is_array($body))
   return new WP_Error('panje_api_health','Health Check ناموفق بود',['status'=>$code]);
  return[
   'status'=>$body['status']??'unknown',
   'engine_version'=>$body['version']??null,
   'food_version'=>$body['food_version']??null,
   'latency_ms'=>(int)round((microtime(true)-$start)*1000)
  ];
 }

 public static function post(string $endpoint,array $payload){
  $s=Panje_DB::settings();
  if(!$s['api_url']||!$s['api_key'])return new WP_Error('panje_api_config','Python API تنظیم نشده است');
  return wp_remote_post(rtrim($s['api_url'],'/').'/api/v1/'.ltrim($endpoint,'/'),[
   'timeout'=>(int)$s['timeout'],
   'redirection'=>0,
   'headers'=>[
    'Content-Type'=>'application/json',
    'Accept'=>'application/json, application/pdf',
    'Cache-Control'=>'no-store',
    'X-Panje-API-Key'=>$s['api_key'],
    'Idempotency-Key'=>$payload['request_id']??wp_generate_uuid4()
   ],
   'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE)
  ]);
 }

 public static function pdf(array $payload){
  $r=self::post('render-pdf',$payload);
  if(is_wp_error($r))return $r;
  $code=(int)wp_remote_retrieve_response_code($r);
  $body=wp_remote_retrieve_body($r);
  $type=(string)wp_remote_retrieve_header($r,'content-type');
  if($code<200||$code>=300)
   return new WP_Error('panje_pdf_api','ساخت PDF در موتور Python ناموفق بود',['status'=>$code]);
  if($body===''||strncmp($body,'%PDF-',5)!==0)
   return new WP_Error('panje_pdf_api','پاسخ PDF معتبر نیست',['status'=>502,'content_type'=>$type]);
  return $body;
 }
}
