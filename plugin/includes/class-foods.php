<?php
if(!defined('ABSPATH')) exit;
final class Panje_Foods {
 public static function seed_default():void{
  global $wpdb;if((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}panje_food_versions"))return;
  $path=PANJE_DIR.'data/default-foods.csv';if(is_readable($path))self::import_csv($path,'legacy-0.2',0);
 }
 public static function active_version():?string{global $wpdb;$v=$wpdb->get_var("SELECT version FROM {$wpdb->prefix}panje_food_versions WHERE status='active' ORDER BY id DESC LIMIT 1");return $v?(string)$v:null;}
 public static function import_csv(string $path,string $version,int $uid=0,string $category_override=''){
  if(!is_readable($path))return new WP_Error('panje_food_file','CSV قابل خواندن نیست');$version=sanitize_text_field($version);if(!$version)return new WP_Error('panje_food_version','نسخه الزامی است');
  global $wpdb;if((int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}panje_food_versions WHERE version=%s",$version)))return new WP_Error('panje_food_exists','نسخه تکراری است');
  $category_override=sanitize_text_field($category_override);$fh=fopen($path,'r');$header=fgetcsv($fh);if(!$header){fclose($fh);return new WP_Error('panje_food_header','CSV فاقد هدر است');}
  $header=array_map(static function($x){$x=trim((string)$x);return preg_replace('/^\xEF\xBB\xBF/','',$x);},$header);
  if(count($header)!==count(array_unique($header))){fclose($fh);return new WP_Error('panje_food_columns','CSV دارای ستون تکراری است');}
  foreach(['foods','category'] as $r)if(!in_array($r,$header,true)){fclose($fh);return new WP_Error('panje_food_columns',"ستون {$r} وجود ندارد");}
  $wpdb->query('START TRANSACTION');$count=0;$invalid=[];$line=1;
  try{
   while(($row=fgetcsv($fh))!==false){
    $line++;if(count($row)!==count($header)){if(count($invalid)<20)$invalid[]=$line;continue;}$d=array_combine($header,$row);$name=sanitize_text_field($d['foods']??'');if(!$name){if(count($invalid)<20)$invalid[]=$line;continue;}
    $nut=[];foreach($d as $k=>$v){if(in_array($k,['foods','category','source_type'],true)||$v==='')continue;$nut[sanitize_text_field($k)]=is_numeric($v)?(float)$v:sanitize_text_field($v);}
    $source=sanitize_key($d['source_type']??'home');if(!in_array($source,['dry','wet','home','treat','supplement'],true))$source='home';
    if($wpdb->insert($wpdb->prefix.'panje_food_database',['food_key'=>sanitize_title($name),'name'=>$name,'category'=>$category_override?:sanitize_text_field($d['category']??''),'source_type'=>$source,'nutrients'=>wp_json_encode($nut,JSON_UNESCAPED_UNICODE),'version'=>$version,'active'=>0,'created_at'=>current_time('mysql')]))$count++;
   }
   fclose($fh);if(!$count)throw new RuntimeException('هیچ ردیف معتبری وارد نشد');
   $wpdb->query("UPDATE {$wpdb->prefix}panje_food_database SET active=0");
   $wpdb->update($wpdb->prefix.'panje_food_database',['active'=>1],['version'=>$version]);
   $wpdb->query("UPDATE {$wpdb->prefix}panje_food_versions SET status='archived'");
   $wpdb->insert($wpdb->prefix.'panje_food_versions',['version'=>$version,'status'=>'active','filename'=>basename($path),'row_count'=>$count,'checksum'=>hash_file('sha256',$path),'imported_by'=>$uid,'created_at'=>current_time('mysql')]);
   $wpdb->query('COMMIT');Panje_Log::info('foods','Food DB imported',['version'=>$version,'rows'=>$count,'invalid_rows'=>count($invalid),'user_id'=>$uid]);return['version'=>$version,'rows'=>$count,'invalid_rows'=>count($invalid),'invalid_lines'=>$invalid];
  }catch(Throwable $e){if(is_resource($fh))fclose($fh);$wpdb->query('ROLLBACK');return new WP_Error('panje_food_import',$e->getMessage());}
 }
 public static function rollback(string $version){
  global $wpdb;if(!(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}panje_food_versions WHERE version=%s",$version)))return new WP_Error('panje_food_version','نسخه پیدا نشد');
  $wpdb->query('START TRANSACTION');try{$wpdb->query("UPDATE {$wpdb->prefix}panje_food_database SET active=0");$wpdb->update($wpdb->prefix.'panje_food_database',['active'=>1],['version'=>$version]);$wpdb->query("UPDATE {$wpdb->prefix}panje_food_versions SET status='archived'");$wpdb->update($wpdb->prefix.'panje_food_versions',['status'=>'active'],['version'=>$version]);$wpdb->query('COMMIT');return['version'=>$version,'status'=>'active'];}catch(Throwable $e){$wpdb->query('ROLLBACK');return new WP_Error('panje_food_rollback',$e->getMessage());}
 }

 public static function active_rows():array{
  global $wpdb;
  $rows=$wpdb->get_results("SELECT name,category,source_type,nutrients FROM {$wpdb->prefix}panje_food_database WHERE active=1 ORDER BY id ASC",ARRAY_A)?:[];
  $out=[];
  foreach($rows as $row){
   $nut=json_decode($row['nutrients']?:'{}',true);
   if(!is_array($nut))$nut=[];
   $clean=[];
   foreach($nut as $k=>$v)if(is_numeric($v))$clean[(string)$k]=(float)$v;
   $out[]=[
    'name'=>(string)$row['name'],
    'category'=>(string)$row['category'],
    'source_type'=>in_array($row['source_type'],['dry','wet','home','treat','supplement'],true)?$row['source_type']:'home',
    'nutrients'=>$clean
   ];
  }
  return $out;
 }
 public static function sync_active(){
  global $wpdb;
  $version=self::active_version();
  if(!$version)return new WP_Error('panje_food_version','نسخه فعال غذا وجود ندارد');
  $foods=self::active_rows();
  if(!$foods)return new WP_Error('panje_food_empty','دیتابیس غذای فعال خالی است');
  $checksum=(string)$wpdb->get_var($wpdb->prepare(
   "SELECT checksum FROM {$wpdb->prefix}panje_food_versions WHERE version=%s LIMIT 1",$version
  ));
  $uuid=wp_generate_uuid4();
  $resp=Panje_API::post('admin/foods/sync',[
   'request_id'=>$uuid,'version'=>$version,'checksum'=>$checksum,'foods'=>$foods
  ]);
  if(is_wp_error($resp))return $resp;
  $code=wp_remote_retrieve_response_code($resp);
  $body=json_decode(wp_remote_retrieve_body($resp),true);
  if($code<200||$code>=300||!is_array($body)||($body['status']??'')!=='ok'||($body['request_id']??'')!==$uuid){
   return new WP_Error('panje_food_sync','همگام‌سازی دیتابیس غذا با Python ناموفق بود',['status'=>$code]);
  }
  Panje_Log::info('foods','Food DB synced to Python',['version'=>$version,'rows'=>count($foods)]);
  return['version'=>$version,'rows'=>count($foods),'status'=>'synced'];
 }
}
