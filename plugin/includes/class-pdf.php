<?php
if(!defined('ABSPATH')) exit;

final class Panje_PDF {
 public static function available():bool{
  return Panje_API::configured();
 }

 public static function generate_for_report(int $id,int $uid=0){
  global $wpdb;
  $uid=$uid?:get_current_user_id();
  $r=$wpdb->get_row($wpdb->prepare(
   "SELECT r.*,p.name pet_name,p.species,p.breed_name,p.breed,p.age_years,p.age_months,p.age_weeks,
           p.sex,p.neutered,p.weight,p.bcs,p.activity,p.status,
           q.request_uuid,q.parent_request_uuid,q.request_type
    FROM {$wpdb->prefix}panje_reports r
    LEFT JOIN {$wpdb->prefix}panje_pets p ON p.id=r.pet_id
    LEFT JOIN {$wpdb->prefix}panje_requests q ON q.id=r.request_id
    WHERE r.id=%d AND r.user_id=%d",
   $id,$uid
  ),ARRAY_A);
  if(!$r)return new WP_Error('panje_report','گزارش یافت نشد',['status'=>404]);
  if(!Panje_API::configured())return new WP_Error('panje_pdf_api','Python API برای ساخت PDF تنظیم نشده است',['status'=>503]);

  $data=json_decode($r['report_json'],true)?:[];
  if(isset($data['warnings']) && is_array($data['warnings'])){
   $data['warnings']=array_map(static function($warning){
    $warning=(array)$warning;
    return ['severity'=>sanitize_key((string)($warning['severity']??'info')),'title'=>sanitize_text_field((string)($warning['title']??'')),'explanation'=>sanitize_textarea_field((string)($warning['explanation']??'')),'reason'=>sanitize_textarea_field((string)($warning['reason']??'')),'recommendation'=>sanitize_textarea_field((string)($warning['recommendation']??''))];
   },$data['warnings']);
  }
  $data=self::merge_chain($r,$data,$uid);
  $settings=Panje_DB::settings();

  // Rendering is stateless on Python; WordPress remains the owner of storage/history/access.
  $binary=Panje_API::pdf([
   'request_id'=>wp_generate_uuid4(),
   'title'=>(string)$settings['pdf_title'],
   'footer'=>(string)$settings['pdf_footer'],
   'created_at'=>(string)$r['created_at'],
   'pet'=>[
    'name'=>(string)$r['pet_name'],'species'=>(string)$r['species'],'breed_name'=>(string)$r['breed_name'],
    'breed'=>(string)$r['breed'],'age_years'=>(int)$r['age_years'],'age_months'=>(int)$r['age_months'],
    'age_weeks'=>(int)$r['age_weeks'],'sex'=>(string)$r['sex'],'neutered'=>(bool)$r['neutered'],
    'weight'=>(float)$r['weight'],'bcs'=>(int)$r['bcs'],'activity'=>(int)$r['activity'],'status'=>(string)$r['status']
   ],
   'report'=>$data
  ]);
  if(is_wp_error($binary))return $binary;

  $uploads=wp_upload_dir();
  if(!empty($uploads['error']))return new WP_Error('panje_uploads',$uploads['error'],['status'=>500]);
  $dir=trailingslashit($uploads['basedir']).'panje/reports/'.$uid;
  if(!wp_mkdir_p($dir))return new WP_Error('panje_pdf_dir','ساخت مسیر PDF ناموفق بود',['status'=>500]);

  $opaque=substr(hash_hmac('sha256',$id.'|'.$uid.'|'.$r['created_at'],wp_salt('auth')),0,24);
  $file='panje-report-'.$id.'-'.$opaque.'.pdf';
  $path=trailingslashit($dir).$file;
  if(file_put_contents($path,$binary,LOCK_EX)===false)
   return new WP_Error('panje_pdf_write','ذخیره PDF ناموفق بود',['status'=>500]);

  $hash=hash_file('sha256',$path);
  $url=trailingslashit($uploads['baseurl']).'panje/reports/'.$uid.'/'.$file;
  $wpdb->update(
   $wpdb->prefix.'panje_reports',
   ['pdf_url'=>esc_url_raw($url),'pdf_hash'=>$hash],
   ['id'=>$id,'user_id'=>$uid]
  );
  Panje_Log::info('pdf','PDF generated',['report_id'=>$id,'user_id'=>$uid,'backend'=>'python-stateless']);
  return['url'=>$url,'hash'=>$hash,'report_id'=>$id];
 }

 public static function invalidate_request_pdf(string $request_uuid,int $uid,int $pet_id):void{
  if($request_uuid==='')return;
  global $wpdb;
  $rows=$wpdb->get_results($wpdb->prepare(
   "SELECT rp.id,rp.pdf_url
    FROM {$wpdb->prefix}panje_reports rp
    INNER JOIN {$wpdb->prefix}panje_requests rq ON rq.id=rp.request_id
    WHERE rq.request_uuid=%s AND rp.user_id=%d AND rp.pet_id=%d",
   $request_uuid,$uid,$pet_id
  ),ARRAY_A)?:[];
  if(!$rows)return;

  $uploads=wp_upload_dir();
  foreach($rows as $row){
   $url=(string)($row['pdf_url']??'');
   if($url!==''&&!empty($uploads['baseurl'])&&str_starts_with($url,$uploads['baseurl'])){
    $relative=ltrim(substr($url,strlen($uploads['baseurl'])),'/');
    $path=trailingslashit($uploads['basedir']).$relative;
    if(is_file($path))@unlink($path);
   }
   $wpdb->update($wpdb->prefix.'panje_reports',['pdf_url'=>null,'pdf_hash'=>null],['id'=>(int)$row['id'],'user_id'=>$uid]);
  }
 }

 private static function merge_chain(array $r,array $current,int $uid):array{
  global $wpdb;
  if(($r['request_type']??$r['report_type'])==='generate'&&!empty($r['parent_request_uuid'])){
   $parent=$wpdb->get_var($wpdb->prepare(
    "SELECT rp.report_json
     FROM {$wpdb->prefix}panje_reports rp
     INNER JOIN {$wpdb->prefix}panje_requests rq ON rq.id=rp.request_id
     WHERE rq.request_uuid=%s AND rp.user_id=%d AND rp.pet_id=%d AND rp.report_type='analyze'
     ORDER BY rp.id DESC LIMIT 1",
    $r['parent_request_uuid'],$uid,(int)$r['pet_id']
   ));
   $analysis=$parent?json_decode($parent,true):null;
   if(is_array($analysis))return array_merge($analysis,$current);
  }

  if(($r['request_type']??$r['report_type'])==='analyze'&&!empty($r['request_uuid'])){
   $child=$wpdb->get_var($wpdb->prepare(
    "SELECT rp.report_json
     FROM {$wpdb->prefix}panje_reports rp
     INNER JOIN {$wpdb->prefix}panje_requests rq ON rq.id=rp.request_id
     WHERE rq.parent_request_uuid=%s AND rp.user_id=%d AND rp.pet_id=%d AND rp.report_type='generate'
     ORDER BY rp.id DESC LIMIT 1",
    $r['request_uuid'],$uid,(int)$r['pet_id']
   ));
   $generated=$child?json_decode($child,true):null;
   if(is_array($generated))return array_merge($current,$generated);
  }
  return$current;
 }
}
