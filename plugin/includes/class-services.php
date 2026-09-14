<?php
if(!defined('ABSPATH')) exit;
final class Panje_Services {
 public static function get(string $key):?array{
  global $wpdb; $r=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}panje_services WHERE service_key=%s",sanitize_key($key)),ARRAY_A);
  return $r?:null;
 }
 public static function all():array{global $wpdb;return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}panje_services ORDER BY service_key",ARRAY_A)?:[];}
 public static function public_catalog(int $uid=0):array{
  $out=[];foreach(self::all() as $s){$q=self::preview($s['service_key'],$uid);$out[$s['service_key']]=['label'=>$s['label'],'active'=>(bool)$s['active'],'base_price'=>(int)$s['price'],'payable'=>$q['payable'],'discount'=>$q['discount']];}return $out;
 }
 public static function preview(string $key,int $uid=0):array{
  global $wpdb;$s=self::get($key);if(!$s)return['active'=>false,'payable'=>0,'discount'=>null];
  $base=(int)$s['price'];if(!(int)$s['active'])return['active'=>false,'payable'=>$base,'discount'=>null];
  $now=current_time('mysql');$rows=$wpdb->get_results($wpdb->prepare(
   "SELECT * FROM {$wpdb->prefix}panje_discounts WHERE service_key=%s AND active=1
    AND (starts_at IS NULL OR starts_at<=%s) AND (ends_at IS NULL OR ends_at>=%s) ORDER BY id DESC",$key,$now,$now
  ),ARRAY_A)?:[];
  foreach($rows as $d){
   if($d['usage_limit']!==null&&(int)$d['used_count']>=(int)$d['usage_limit'])continue;
   if($uid&&$d['per_user_limit']!==null){
    $used=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}panje_discount_usage WHERE discount_id=%d AND user_id=%d",$d['id'],$uid));
    if($used>=(int)$d['per_user_limit'])continue;
   }
   $pay=self::apply($base,$d);return['active'=>true,'payable'=>$pay,'discount'=>['id'=>(int)$d['id'],'title'=>$d['title'],'saved'=>$base-$pay]];
  }
  return['active'=>true,'payable'=>$base,'discount'=>null];
 }
 public static function reserve(string $key,int $uid,string $uuid){
  global $wpdb;$s=self::get($key);if(!$s)return new WP_Error('panje_service','سرویس یافت نشد',['status'=>404]);
  if(!(int)$s['active'])return new WP_Error('panje_service_disabled','سرویس غیرفعال است',['status'=>409]);
  $base=(int)$s['price'];$now=current_time('mysql');$wpdb->query('START TRANSACTION');
  try{
   $rows=$wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}panje_discounts WHERE service_key=%s AND active=1
     AND (starts_at IS NULL OR starts_at<=%s) AND (ends_at IS NULL OR ends_at>=%s)
     ORDER BY id DESC FOR UPDATE",$key,$now,$now
   ),ARRAY_A)?:[];
   foreach($rows as $d){
    if($d['usage_limit']!==null&&(int)$d['used_count']>=(int)$d['usage_limit'])continue;
    if($d['per_user_limit']!==null){
     $used=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}panje_discount_usage WHERE discount_id=%d AND user_id=%d",$d['id'],$uid));
     if($used>=(int)$d['per_user_limit'])continue;
    }
    $pay=self::apply($base,$d);
    if(!$wpdb->insert($wpdb->prefix.'panje_discount_usage',['discount_id'=>$d['id'],'user_id'=>$uid,'request_uuid'=>$uuid,'created_at'=>$now]))throw new RuntimeException('discount usage');
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}panje_discounts SET used_count=used_count+1,updated_at=%s WHERE id=%d",$now,$d['id']));
    $wpdb->query('COMMIT');
    return['base'=>$base,'payable'=>$pay,'discount_id'=>(int)$d['id'],'discount'=>['id'=>(int)$d['id'],'title'=>$d['title'],'saved'=>$base-$pay]];
   }
   $wpdb->query('COMMIT');return['base'=>$base,'payable'=>$base,'discount_id'=>0,'discount'=>null];
  }catch(Throwable $e){$wpdb->query('ROLLBACK');return new WP_Error('panje_discount',$e->getMessage(),['status'=>500]);}
 }
 public static function release(int $id,string $uuid):void{
  if($id<=0)return;global $wpdb;$wpdb->query('START TRANSACTION');
  try{$del=$wpdb->delete($wpdb->prefix.'panje_discount_usage',['discount_id'=>$id,'request_uuid'=>$uuid]);if($del)$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}panje_discounts SET used_count=GREATEST(used_count-1,0),updated_at=%s WHERE id=%d",current_time('mysql'),$id));$wpdb->query('COMMIT');}catch(Throwable $e){$wpdb->query('ROLLBACK');}
 }
 private static function apply(int $base,array $d):int{
  $v=max(0,(int)$d['discount_value']);if($d['discount_type']==='percent')return max(0,(int)round($base*(100-min(100,$v))/100));return max(0,$base-$v);
 }
}
