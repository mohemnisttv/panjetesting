<?php
if(!defined('ABSPATH')) exit;

final class Panje_Admin {
 public static function boot():void{
  add_action('admin_menu',[__CLASS__,'menu']); add_action('admin_init',[__CLASS__,'settings']);
  foreach(['panje_api_test','panje_service_save','panje_discount_create','panje_discount_toggle','panje_food_import','panje_food_rollback','panje_food_sync','panje_wallet_adjust'] as $a)
   add_action('admin_post_'.$a,[__CLASS__,str_replace('panje_','',$a)]);
  add_action('admin_enqueue_scripts',[__CLASS__,'assets']);
}
 public static function assets($hook):void{if(strpos((string)$hook,'panje')===false)return;wp_enqueue_style('panje-admin',PANJE_URL.'assets/css/panje-admin.css',[],PANJE_VERSION);wp_enqueue_style('panje-admin-base',PANJE_URL.'assets/css/panje.css',['panje-admin'],PANJE_VERSION);if(strpos((string)$hook,'panje-users')!==false)wp_enqueue_style('panje-users',PANJE_URL.'assets/css/panje-users.css',['panje-admin'],PANJE_VERSION);}
 public static function settings():void{register_setting('panje_settings','panje_settings',[__CLASS__,'sanitize']);}
 public static function sanitize($v):array{
  $raw=(array)get_option('panje_settings',[]);
  $new_key=isset($v['api_key'])?trim((string)$v['api_key']):'';
  $stored_key=$new_key!==''?Panje_DB::protect_secret(sanitize_text_field($new_key)):(string)($raw['api_key']??'');
  $api_url=esc_url_raw($v['api_url']??'');
  $parts=$api_url?wp_parse_url($api_url):[];
  if($api_url && (!is_array($parts)||!in_array(strtolower((string)($parts['scheme']??'')),['https','http'],true)||empty($parts['host'])))$api_url='';
  return[
   'api_url'=>$api_url,
   'api_key'=>$stored_key,
   'max_retries'=>min(5,max(0,(int)($v['max_retries']??2))),
   'timeout'=>min(120,max(10,(int)($v['timeout']??45))),
   'pdf_title'=>sanitize_text_field($v['pdf_title']??'گزارش تخصصی پنجه'),
   'pdf_footer'=>sanitize_text_field($v['pdf_footer']??'پنجه — تغذیه هوشمند حیوانات خانگی'),
   'brand_color'=>'#0C953B'
  ];
 }
 public static function menu():void{
  add_menu_page('پنجه','پنجه','manage_options','panje',[__CLASS__,'dashboard'],'dashicons-pets',56);
  add_submenu_page('panje','داشبورد','داشبورد','manage_options','panje',[__CLASS__,'dashboard']);
  add_submenu_page('panje','کاربران','کاربران','manage_options','panje-users',[__CLASS__,'users']);
  add_submenu_page('panje','پت‌ها','پت‌ها','manage_options','panje-pets',[__CLASS__,'pets']);
  add_submenu_page('panje','سرویس‌ها و تخفیف‌ها','سرویس‌ها و تخفیف‌ها','manage_options','panje-services',[__CLASS__,'services']);
  add_submenu_page('panje','گزارش‌ها','گزارش‌ها','manage_options','panje-reports',[__CLASS__,'reports']);
  add_submenu_page('panje','تراکنش‌ها','تراکنش‌ها','manage_options','panje-transactions',[__CLASS__,'transactions']);
  add_submenu_page('panje','دیتابیس غذاها','دیتابیس غذاها','manage_options','panje-foods',[__CLASS__,'foods']);
  add_submenu_page('panje','لاگ‌ها','لاگ‌ها','manage_options','panje-logs',[__CLASS__,'logs']);
  add_submenu_page('panje','تنظیمات','تنظیمات','manage_options','panje-settings',[__CLASS__,'page']);
 }
 public static function dashboard():void{
  if(!current_user_can('manage_options'))wp_die('Forbidden','',['response'=>403]);
  global $wpdb;$p=$wpdb->prefix;$stats=[
   'کاربران دارای پت'=>(int)$wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$p}panje_pets"),
   'پت‌ها'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$p}panje_pets"),
   'گزارش‌ها'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$p}panje_reports"),
   'درخواست ناموفق'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$p}panje_requests WHERE status='failed'"),
   'مصرف کیف پول'=>(int)$wpdb->get_var("SELECT COALESCE(SUM(amount),0) FROM {$p}panje_transactions WHERE type='debit'")];
  $settings=Panje_DB::settings();$recent=$wpdb->get_results("SELECT level,module,message,created_at FROM {$p}panje_logs ORDER BY id DESC LIMIT 8",ARRAY_A)?:[];
  echo'<div class="wrap panje-admin"><h1>پیشخوان پنجه</h1><div class="panje-kpi-grid">';
  foreach($stats as $k=>$v)echo'<div class="panje-kpi"><div>'.esc_html($k).'</div><strong>'.esc_html(number_format_i18n($v)).'</strong></div>';
  echo'</div><div class="panje-dashboard-grid"><section class="panje-admin-panel"><h2>وضعیت اتصال</h2><p><span class="panje-status-dot '.($settings['api_url']&&$settings['api_key']?'is-ok':'is-warning').'"></span>'.esc_html($settings['api_url']&&$settings['api_key']?'API پیکربندی شده است':'API هنوز پیکربندی نشده است').'</p><a class="button button-primary" href="'.esc_url(admin_url('admin.php?page=panje-settings')).'">مدیریت تنظیمات API</a></section><section class="panje-admin-panel"><h2>فعالیت‌های اخیر</h2>';
  if(!$recent)echo'<p class="description">هنوز فعالیتی ثبت نشده است.</p>';foreach($recent as $log)echo'<div class="panje-activity"><b>'.esc_html($log['module']).'</b><span>'.esc_html($log['message']).'</span><small>'.esc_html($log['created_at']).'</small></div>';
  echo'</section></div></div>';
 }
 public static function page():void{$s=Panje_DB::settings();?>
  <div class="wrap"><h1>تنظیمات پنجه</h1><?php if(isset($_GET['api_test']))echo'<div class="notice notice-'.($_GET['api_test']==='ok'?'success':'error').'"><p>'.($_GET['api_test']==='ok'?'اتصال Python برقرار است.':'اتصال Python ناموفق است.').'</p></div>';?>
  <form method="post" action="options.php"><?php settings_fields('panje_settings');?><table class="form-table">
  <tr><th>Python API URL</th><td><input class="regular-text" name="panje_settings[api_url]" value="<?php echo esc_attr($s['api_url']);?>"></td></tr>
  <tr><th>API Key</th><td><input type="password" class="regular-text" name="panje_settings[api_key]" value="" autocomplete="new-password" placeholder="برای تغییر Secret وارد کنید"><p class="description">Secret فعلی نمایش داده نمی‌شود؛ خالی بگذارید تا بدون تغییر بماند.</p></td></tr>
  <tr><th>Timeout</th><td><input type="number" name="panje_settings[timeout]" value="<?php echo esc_attr($s['timeout']);?>"></td></tr>
  <tr><th>Retry</th><td><input type="number" name="panje_settings[max_retries]" value="<?php echo esc_attr($s['max_retries']);?>"></td></tr>
  <tr><th>عنوان PDF</th><td><input class="regular-text" name="panje_settings[pdf_title]" value="<?php echo esc_attr($s['pdf_title']);?>"></td></tr>
  <tr><th>Footer PDF</th><td><input class="regular-text" name="panje_settings[pdf_footer]" value="<?php echo esc_attr($s['pdf_footer']);?>"></td></tr>
  </table><?php submit_button();?></form><hr><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><?php wp_nonce_field('panje_api_test');?><input type="hidden" name="action" value="panje_api_test"><?php submit_button('تست اتصال Python','secondary','submit',false);?></form></div><?php
 }
 public static function users():void{
  if (!current_user_can('manage_options')) wp_die('Forbidden', '', ['response'=>403]);
  $page=isset($_GET['paged']) && is_scalar($_GET['paged']) ? max(1,absint($_GET['paged'])) : 1;
  $search=isset($_GET['s']) && is_string($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
  $directory=(new \Panje\Admin\UserDirectory())->search($search,$page);
  if (is_wp_error($directory)) wp_die(esc_html($directory->get_error_message()), '', ['response'=>500]);
  require PANJE_DIR.'templates/admin/users.php';
 }
 public static function pets():void{global $wpdb;$rows=$wpdb->get_results("SELECT p.*,u.display_name,u.user_email FROM {$wpdb->prefix}panje_pets p LEFT JOIN {$wpdb->users} u ON u.ID=p.user_id ORDER BY p.id DESC LIMIT 200",ARRAY_A);echo'<div class="wrap"><h1>پت‌ها</h1><table class="widefat striped"><tr><th>ID</th><th>پت</th><th>کاربر</th><th>گونه</th><th>نژاد</th><th>وزن</th><th>BCS</th><th>وضعیت</th></tr>';foreach($rows as $r)echo'<tr><td>'.$r['id'].'</td><td>'.esc_html($r['name']).'</td><td>'.esc_html($r['display_name']).'</td><td>'.esc_html($r['species']).'</td><td>'.esc_html($r['breed_name']).'</td><td>'.esc_html($r['weight']).'</td><td>'.esc_html($r['bcs']).'</td><td>'.esc_html($r['status']).'</td></tr>';echo'</table></div>';}
 public static function services():void{
  global $wpdb;
  $services=Panje_Services::all();
  $discounts=$wpdb->get_results("SELECT * FROM {$wpdb->prefix}panje_discounts ORDER BY id DESC LIMIT 100",ARRAY_A)?:[];
  echo'<div class="wrap"><h1>سرویس‌ها و تخفیف‌ها</h1>';
  foreach($services as $s){
   echo'<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="background:#fff;padding:14px;margin:8px 0">';
   wp_nonce_field('panje_service_save');
   echo'<input type="hidden" name="action" value="panje_service_save"><input type="hidden" name="service_key" value="'.esc_attr($s['service_key']).'"><b>'.esc_html($s['label']).'</b> <input type="number" name="price" min="0" value="'.esc_attr($s['price']).'"> تومان <label><input type="checkbox" name="active" value="1" '.checked($s['active'],1,false).'>فعال</label> ';
   submit_button('ذخیره','primary','submit',false);
   echo'</form>';
  }
  echo'<h2>تخفیف جدید</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
  wp_nonce_field('panje_discount_create');
  echo'<input type="hidden" name="action" value="panje_discount_create"><select name="service_key">';
  foreach($services as $s)echo'<option value="'.esc_attr($s['service_key']).'">'.esc_html($s['label']).'</option>';
  echo'</select> <input name="title" placeholder="عنوان" required> <select name="discount_type"><option value="percent">درصدی</option><option value="fixed">مبلغ ثابت</option></select> <input type="number" name="discount_value" min="0" required> <input type="number" name="usage_limit" min="1" placeholder="سقف کل"> <input type="number" name="per_user_limit" min="1" placeholder="سقف هر کاربر"> <label>شروع <input type="datetime-local" name="starts_at"></label> <label>پایان <input type="datetime-local" name="ends_at"></label> ';
  submit_button('ایجاد','primary','submit',false);
  echo'</form><h2>لیست تخفیف‌ها</h2><table class="widefat striped"><tr><th>ID</th><th>عنوان</th><th>سرویس</th><th>نوع</th><th>مقدار</th><th>مصرف</th><th>بازه</th><th>وضعیت</th><th>عملیات</th></tr>';
  foreach($discounts as $d){
   echo'<tr><td>'.(int)$d['id'].'</td><td>'.esc_html($d['title']).'</td><td>'.esc_html($d['service_key']).'</td><td>'.esc_html($d['discount_type']).'</td><td>'.(int)$d['discount_value'].'</td><td>'.(int)$d['used_count'].'</td><td>'.esc_html(($d['starts_at']?:'—').' تا '.($d['ends_at']?:'—')).'</td><td>'.($d['active']?'فعال':'غیرفعال').'</td><td>';
   echo'<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
   wp_nonce_field('panje_discount_toggle_'.(int)$d['id']);
   echo'<input type="hidden" name="action" value="panje_discount_toggle"><input type="hidden" name="discount_id" value="'.(int)$d['id'].'">';
   submit_button($d['active']?'غیرفعال‌کردن':'فعال‌کردن','secondary','submit',false);
   echo'</form></td></tr>';
  }
  echo'</table></div>';
 }
 public static function reports():void{
  global $wpdb;
  $page=max(1,absint($_GET['paged']??1));$per_page=50;$offset=($page-1)*$per_page;
  $rows=$wpdb->get_results(
   $wpdb->prepare("SELECT r.id,r.user_id,r.report_type,r.pdf_url,r.created_at,p.name pet_name,u.display_name,q.request_uuid
    FROM {$wpdb->prefix}panje_reports r
    LEFT JOIN {$wpdb->prefix}panje_pets p ON p.id=r.pet_id
    LEFT JOIN {$wpdb->users} u ON u.ID=r.user_id
    LEFT JOIN {$wpdb->prefix}panje_requests q ON q.id=r.request_id
    ORDER BY r.id DESC LIMIT %d OFFSET %d",$per_page,$offset),ARRAY_A
  )?:[];
  $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}panje_reports");$pages=max(1,(int)ceil($total/$per_page));
  echo'<div class="wrap panje-admin"><h1>گزارش‌ها</h1><p>صفحه '.esc_html($page).' از '.esc_html($pages).' — مجموع '.esc_html($total).' گزارش</p><table class="widefat striped"><thead><tr><th>ID</th><th>کاربر</th><th>پت</th><th>نوع</th><th>Request ID</th><th>PDF</th><th>تاریخ</th></tr></thead><tbody>';
  foreach($rows as $r){
   echo'<tr><td>'.(int)$r['id'].'</td><td>'.esc_html($r['display_name']?:('#'.$r['user_id'])).'</td><td>'.esc_html($r['pet_name']).'</td><td>'.esc_html($r['report_type']).'</td><td><code>'.esc_html($r['request_uuid']).'</code></td><td>';
   echo $r['pdf_url']?'<a href="'.esc_url($r['pdf_url']).'" target="_blank" rel="noopener noreferrer">مشاهده</a>':'—';
   echo'</td><td>'.esc_html($r['created_at']).'</td></tr>';
  }
  echo'</tbody></table><p class="tablenav">';if($page>1)echo'<a class="button" href="'.esc_url(add_query_arg('paged',$page-1)).'">قبلی</a> ';if($page<$pages)echo'<a class="button" href="'.esc_url(add_query_arg('paged',$page+1)).'">بعدی</a>';echo'</p></div>';
 }
 public static function transactions():void{
  global $wpdb;
  $rows=$wpdb->get_results(
   "SELECT t.*,u.display_name FROM {$wpdb->prefix}panje_transactions t
    LEFT JOIN {$wpdb->users} u ON u.ID=t.user_id ORDER BY t.id DESC LIMIT 250",ARRAY_A
  )?:[];
  echo'<div class="wrap"><h1>تراکنش‌های پنجه</h1><table class="widefat striped"><thead><tr><th>ID</th><th>کاربر</th><th>نوع</th><th>عملیات</th><th>مبلغ</th><th>قبل</th><th>بعد</th><th>مرجع</th><th>تاریخ</th></tr></thead><tbody>';
  foreach($rows as $r){
   echo'<tr><td>'.(int)$r['id'].'</td><td>'.esc_html($r['display_name']?:('#'.$r['user_id'])).'</td><td>'.esc_html($r['type']).'</td><td>'.esc_html($r['operation']).'</td><td>'.esc_html(number_format_i18n((int)$r['amount'])).'</td><td>'.esc_html(number_format_i18n((int)$r['balance_before'])).'</td><td>'.esc_html(number_format_i18n((int)$r['balance_after'])).'</td><td><code>'.esc_html($r['reference']).'</code></td><td>'.esc_html($r['created_at']).'</td></tr>';
  }
  echo'</tbody></table></div>';
 }
 public static function logs():void{
  global $wpdb;
  $page=max(1,absint($_GET['paged']??1));$per_page=100;$offset=($page-1)*$per_page;
  $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}panje_logs ORDER BY id DESC LIMIT %d OFFSET %d",$per_page,$offset),ARRAY_A)?:[];
  $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}panje_logs");$pages=max(1,(int)ceil($total/$per_page));
  echo'<div class="wrap panje-admin"><h1>لاگ‌های پنجه</h1><p>صفحه '.esc_html($page).' از '.esc_html($pages).' — مجموع '.esc_html($total).' رویداد</p><table class="widefat striped"><thead><tr><th>زمان</th><th>سطح</th><th>ماژول</th><th>پیام</th><th>Request</th><th>User</th></tr></thead><tbody>';
  foreach($rows as $r){
   echo'<tr><td>'.esc_html($r['created_at']).'</td><td>'.esc_html($r['level']).'</td><td>'.esc_html($r['module']).'</td><td>'.esc_html($r['message']).'</td><td><code>'.esc_html($r['request_uuid']).'</code></td><td>'.esc_html((string)$r['user_id']).'</td></tr>';
  }
  echo'</tbody></table><p class="tablenav">';if($page>1)echo'<a class="button" href="'.esc_url(add_query_arg('paged',$page-1)).'">قبلی</a> ';if($page<$pages)echo'<a class="button" href="'.esc_url(add_query_arg('paged',$page+1)).'">بعدی</a>';echo'</p></div>';
 }
 public static function foods():void{
  global $wpdb;
  $versions=$wpdb->get_results("SELECT * FROM {$wpdb->prefix}panje_food_versions ORDER BY id DESC LIMIT 30",ARRAY_A)?:[];
  $active=Panje_Foods::active_version();
  echo'<div class="wrap"><h1>دیتابیس غذاها</h1>';
  if(isset($_GET['food_status'])){
   $ok=$_GET['food_status']==='ok';
   echo'<div class="notice notice-'.($ok?'success':'error').'"><p>'.($ok?'عملیات دیتابیس غذا با موفقیت انجام شد.':'عملیات دیتابیس غذا کامل نشد؛ لاگ پنجه را بررسی کنید.').'</p></div>';
  }
  echo'<p>نسخه فعال: <b>'.esc_html($active?:'ندارد').'</b></p>';
  echo'<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">';
  echo'<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">';
  wp_nonce_field('panje_food_import');
  echo'<input type="hidden" name="action" value="panje_food_import"><input name="version" placeholder="نسخه" required> <input name="category_override" placeholder="دسته این فایل (اختیاری)"> <input type="file" name="csv" accept=".csv,text/csv" required> ';
  submit_button('آپلود CSV','primary','submit',false);
  echo'</form>';
  if($active){
   echo'<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
   wp_nonce_field('panje_food_sync');
   echo'<input type="hidden" name="action" value="panje_food_sync">';
   submit_button('همگام‌سازی نسخه فعال با Python','secondary','submit',false);
   echo'</form>';
  }
  echo'</div><table class="widefat striped"><tr><th>نسخه</th><th>وضعیت</th><th>ردیف</th><th>تاریخ</th><th>عملیات</th></tr>';
  foreach($versions as $v){
   echo'<tr><td>'.esc_html($v['version']).'</td><td>'.esc_html($v['status']).'</td><td>'.(int)$v['row_count'].'</td><td>'.esc_html($v['created_at']).'</td><td>';
   if($v['status']!=='active'){
    echo'<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('panje_food_rollback_'.(int)$v['id']);
    echo'<input type="hidden" name="action" value="panje_food_rollback"><input type="hidden" name="version_id" value="'.(int)$v['id'].'">';
    submit_button('بازگردانی این نسخه','secondary','submit',false);
    echo'</form>';
   }else echo'فعال';
   echo'</td></tr>';
  }
  echo'</table></div>';
 }
 public static function api_test():void{if(!current_user_can('manage_options'))wp_die('Forbidden');check_admin_referer('panje_api_test');$r=Panje_API::health();Panje_Log::write(is_wp_error($r)?'ERROR':'INFO','api',is_wp_error($r)?'Health failed':'Health ok',['user_id'=>get_current_user_id()]);wp_safe_redirect(add_query_arg('api_test',is_wp_error($r)?'error':'ok',admin_url('admin.php?page=panje-settings')));exit;}
 public static function service_save():void{if(!current_user_can('manage_options'))wp_die('Forbidden');check_admin_referer('panje_service_save');global $wpdb;$k=sanitize_key($_POST['service_key']??'');$wpdb->update($wpdb->prefix.'panje_services',['price'=>max(0,(int)($_POST['price']??0)),'active'=>isset($_POST['active'])?1:0,'updated_at'=>current_time('mysql')],['service_key'=>$k]);wp_safe_redirect(admin_url('admin.php?page=panje-services'));exit;}
 public static function discount_create():void{
  if(!current_user_can('manage_options'))wp_die('Forbidden');
  check_admin_referer('panje_discount_create');
  global $wpdb;
  $type=in_array($_POST['discount_type']??'',['percent','fixed'],true)?$_POST['discount_type']:'fixed';
  $v=max(0,(int)($_POST['discount_value']??0));if($type==='percent')$v=min(100,$v);
  $normalize=static function($value){
   $value=sanitize_text_field((string)$value);
   if($value==='')return null;
   if(!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/',$value))return null;
   return str_replace('T',' ',$value).':00';
  };
  $starts=$normalize($_POST['starts_at']??'');$ends=$normalize($_POST['ends_at']??'');
  if($starts&&$ends&&$ends<$starts)wp_die('زمان پایان تخفیف باید بعد از زمان شروع باشد.');
  $wpdb->insert($wpdb->prefix.'panje_discounts',[
   'service_key'=>sanitize_key($_POST['service_key']??''),'title'=>sanitize_text_field($_POST['title']??''),
   'discount_type'=>$type,'discount_value'=>$v,'active'=>1,'starts_at'=>$starts,'ends_at'=>$ends,
   'usage_limit'=>($_POST['usage_limit']??'')!==''?max(1,(int)$_POST['usage_limit']):null,
   'per_user_limit'=>($_POST['per_user_limit']??'')!==''?max(1,(int)$_POST['per_user_limit']):null,
   'created_at'=>current_time('mysql')
  ]);
  wp_safe_redirect(admin_url('admin.php?page=panje-services'));exit;
 }
 public static function discount_toggle():void{
  if(!current_user_can('manage_options'))wp_die('Forbidden');
  $id=max(0,(int)($_POST['discount_id']??0));
  check_admin_referer('panje_discount_toggle_'.$id);
  if(!$id)wp_die('Invalid discount');
  global $wpdb;
  $wpdb->query($wpdb->prepare(
   "UPDATE {$wpdb->prefix}panje_discounts SET active=IF(active=1,0,1),updated_at=%s WHERE id=%d",
   current_time('mysql'),$id
  ));
  wp_safe_redirect(admin_url('admin.php?page=panje-services'));exit;
 }
 public static function food_import():void{
  if(!current_user_can('manage_options'))wp_die('Forbidden');
  check_admin_referer('panje_food_import');
  $file=$_FILES['csv']??null;
  $ok=is_array($file)&&($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK;
  $name=$ok?sanitize_file_name((string)$file['name']):'';
  $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
  if(!$ok||$ext!=='csv'||(int)($file['size']??0)>20*1024*1024){
   Panje_Log::error('foods','Invalid CSV upload',['user_id'=>get_current_user_id()]);
   wp_safe_redirect(add_query_arg('food_status','error',admin_url('admin.php?page=panje-foods')));exit;
  }
  $r=Panje_Foods::import_csv((string)$file['tmp_name'],sanitize_text_field($_POST['version']??''),get_current_user_id(),sanitize_text_field($_POST['category_override']??''));
  if(!is_wp_error($r)){
   $sync=Panje_Foods::sync_active();
   if(is_wp_error($sync))Panje_Log::error('foods','Food sync failed after import',['error'=>$sync->get_error_message()]);
  }
  wp_safe_redirect(add_query_arg('food_status',is_wp_error($r)?'error':'ok',admin_url('admin.php?page=panje-foods')));exit;
 }
 public static function wallet_adjust():void{if(!current_user_can('manage_options'))wp_die('Forbidden');check_admin_referer('panje_wallet_adjust');$uid=max(0,(int)($_POST['user_id']??0));$amount=(int)($_POST['amount']??0);if(!$uid||$amount===0)wp_die('مبلغ یا کاربر نامعتبر است');$r=Panje_Wallet::change($uid,$amount,$amount>0?'credit':'debit','admin:'.get_current_user_id(),'admin_adjust',null,true);if(is_wp_error($r))wp_die(esc_html($r->get_error_message()));wp_safe_redirect(admin_url('admin.php?page=panje-users'));exit;}
 public static function food_rollback():void{
  if(!current_user_can('manage_options'))wp_die('Forbidden');
  $id=max(0,(int)($_POST['version_id']??0));
  check_admin_referer('panje_food_rollback_'.$id);
  global $wpdb;
  $version=$wpdb->get_var($wpdb->prepare("SELECT version FROM {$wpdb->prefix}panje_food_versions WHERE id=%d",$id));
  $r=$version?Panje_Foods::rollback((string)$version):new WP_Error('panje_food_version','نسخه پیدا نشد');
  if(!is_wp_error($r)){
   $sync=Panje_Foods::sync_active();
   if(is_wp_error($sync))Panje_Log::error('foods','Food sync failed after rollback',['error'=>$sync->get_error_message()]);
  }
  wp_safe_redirect(add_query_arg('food_status',is_wp_error($r)?'error':'ok',admin_url('admin.php?page=panje-foods')));exit;
 }
 public static function food_sync():void{
  if(!current_user_can('manage_options'))wp_die('Forbidden');
  check_admin_referer('panje_food_sync');
  $r=Panje_Foods::sync_active();
  if(is_wp_error($r))Panje_Log::error('foods','Manual food sync failed',['error'=>$r->get_error_message()]);
  wp_safe_redirect(add_query_arg('food_status',is_wp_error($r)?'error':'ok',admin_url('admin.php?page=panje-foods')));exit;
 }
}
