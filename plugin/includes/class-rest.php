<?php
if (!defined('ABSPATH')) exit;

final class Panje_REST {
    public static function boot(): void {
        add_action('rest_api_init',[__CLASS__,'routes']);
        add_action('panje_process_request',[__CLASS__,'process'],10,1);
        add_filter('rest_post_dispatch',[__CLASS__,'private_headers'],10,3);
        add_filter('rest_pre_dispatch',[__CLASS__,'limit_payload'],10,3);
    }

    public static function limit_payload($result, $server, $request) {
        if (strpos((string)$request->get_route(),'/panje/v1/')===0 && strlen((string)$request->get_body()) > 2097152)
            return new WP_Error('panje_payload_too_large','حجم درخواست بیش از حد مجاز است',['status'=>413]);
        return $result;
    }

    public static function routes(): void {
        $auth = static fn()=>self::authorized();

        register_rest_route('panje/v1','/health',[
          'methods'=>'GET','callback'=>[__CLASS__,'health'],'permission_callback'=>'__return_true'
        ]);

        register_rest_route('panje/v1','/pets',[
          ['methods'=>'GET','callback'=>[__CLASS__,'pets'],'permission_callback'=>$auth],
          ['methods'=>'POST','callback'=>[__CLASS__,'create_pet'],'permission_callback'=>$auth],
        ]);
        register_rest_route('panje/v1','/pets/(?P<id>\d+)',[
          ['methods'=>'GET','callback'=>[__CLASS__,'get_pet'],'permission_callback'=>$auth],
          ['methods'=>'PUT,PATCH','callback'=>[__CLASS__,'update_pet'],'permission_callback'=>$auth],
          ['methods'=>'DELETE','callback'=>[__CLASS__,'delete_pet'],'permission_callback'=>$auth],
        ]);

        register_rest_route('panje/v1','/foods',[
          'methods'=>'GET','callback'=>[__CLASS__,'foods'],'permission_callback'=>$auth
        ]);
        register_rest_route('panje/v1','/wallet',[
          'methods'=>'GET','callback'=>[__CLASS__,'wallet'],'permission_callback'=>$auth
        ]);
        register_rest_route('panje/v1','/bootstrap',[
          'methods'=>'GET','callback'=>[__CLASS__,'bootstrap'],'permission_callback'=>$auth
        ]);
        register_rest_route('panje/v1','/reports',[
          'methods'=>'GET','callback'=>[__CLASS__,'reports'],'permission_callback'=>$auth,
          'args'=>[
            'page'=>['default'=>1,'sanitize_callback'=>'absint','validate_callback'=>static fn($v)=>(int)$v>=1],
            'per_page'=>['default'=>50,'sanitize_callback'=>'absint','validate_callback'=>static fn($v)=>(int)$v>=1&&(int)$v<=100],
            'pet_id'=>['default'=>0,'sanitize_callback'=>'absint'],
          ]
        ]);
        register_rest_route('panje/v1','/reports/(?P<id>\d+)',[
          'methods'=>'GET','callback'=>[__CLASS__,'report'],'permission_callback'=>$auth
        ]);
        register_rest_route('panje/v1','/reports/(?P<id>\d+)/pdf',[
          'methods'=>'POST','callback'=>[__CLASS__,'report_pdf'],'permission_callback'=>$auth
        ]);

        register_rest_route('panje/v1','/analyze',[
          'methods'=>'POST','callback'=>fn($r)=>self::queue('analyze',$r),'permission_callback'=>$auth
        ]);
        register_rest_route('panje/v1','/generate',[
          'methods'=>'POST','callback'=>fn($r)=>self::queue('generate',$r),'permission_callback'=>$auth
        ]);
        register_rest_route('panje/v1','/requests/(?P<uuid>[a-f0-9-]{36})',[
          'methods'=>'GET','callback'=>[__CLASS__,'status'],'permission_callback'=>$auth
        ]);
    }

    private static function authorized(): bool {
        if (!is_user_logged_in()) return false;
        $key='panje_rl_'.get_current_user_id();
        $count=(int)get_transient($key);
        if ($count>=120) return false;
        set_transient($key,$count+1,MINUTE_IN_SECONDS);
        return true;
    }

    public static function private_headers($response,$server,$request) {
        if(strpos((string)$request->get_route(),'/panje/v1/')===0 && $response instanceof WP_REST_Response){
            $response->header('Cache-Control','private, no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma','no-cache'); $response->header('Vary','Cookie');
        }
        return $response;
    }

    public static function health(?WP_REST_Request $r=null): array {
        $s=Panje_DB::settings(); $python=null;
        if($r && $r->get_param('verbose') && current_user_can('manage_options')){
            $h=Panje_API::health(); $python=is_wp_error($h)?['status'=>'error','message'=>$h->get_error_message()]:$h;
        }
        return [
          'status'=>'ok','plugin_version'=>PANJE_VERSION,'db_version'=>get_option('panje_db_version'),
          'woocommerce'=>class_exists('WooCommerce'),'action_scheduler'=>function_exists('as_enqueue_async_action'),
          'api_configured'=>(bool)($s['api_url']&&$s['api_key']),'python_health'=>$python
        ];
    }

    public static function bootstrap(): array {
        $uid=get_current_user_id();
        return [
          'user'=>['id'=>$uid,'display_name'=>wp_get_current_user()->display_name],
          'wallet'=>['balance'=>Panje_Wallet::balance($uid),'currency'=>'ØªÙˆÙ…Ø§Ù†'],
          'services'=>Panje_Services::public_catalog($uid),
          'food_version'=>Panje_Foods::active_version(),
          'capabilities'=>['pdf'=>Panje_PDF::available(),'action_scheduler'=>function_exists('as_enqueue_async_action'),'pwa_ready'=>true],
          'api_version'=>'v1','plugin_version'=>PANJE_VERSION
        ];
    }

    public static function report_pdf(WP_REST_Request $r) {
        return Panje_PDF::generate_for_report((int)$r['id'],get_current_user_id());
    }

    private static function owned_pet(int $id, int $uid = 0) {
        global $wpdb; $uid=$uid ?: get_current_user_id();
        return $wpdb->get_row($wpdb->prepare(
          "SELECT * FROM {$wpdb->prefix}panje_pets WHERE id=%d AND user_id=%d",$id,$uid
        ),ARRAY_A);
    }

    public static function pets() {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
          "SELECT * FROM {$wpdb->prefix}panje_pets WHERE user_id=%d ORDER BY id DESC",get_current_user_id()
        ),ARRAY_A);
    }

    public static function get_pet(WP_REST_Request $r) {
        $pet=self::owned_pet((int)$r['id']);
        return $pet ?: new WP_Error('panje_not_found','Ù¾Øª ÛŒØ§ÙØª Ù†Ø´Ø¯',['status'=>404]);
    }

    public static function create_pet(WP_REST_Request $r) {
        $v=self::pet_fields((array)$r->get_json_params());
        if (is_wp_error($v)) return $v;
        global $wpdb; $v['user_id']=get_current_user_id(); $v['created_at']=current_time('mysql');
        if (!$wpdb->insert($wpdb->prefix.'panje_pets',$v))
            return new WP_Error('panje_db','Ø«Ø¨Øª Ù¾Øª Ù†Ø§Ù…ÙˆÙÙ‚ Ø¨ÙˆØ¯',['status'=>500]);
        return new WP_REST_Response(['id'=>(int)$wpdb->insert_id,'status'=>'created'],201);
    }

    public static function update_pet(WP_REST_Request $r) {
        $id=(int)$r['id']; $existing=self::owned_pet($id); if (!$existing)
            return new WP_Error('panje_not_found','Ù¾Øª ÛŒØ§ÙØª Ù†Ø´Ø¯',['status'=>404]);
        $input=(array)$r->get_json_params();
        if (in_array($r->get_method(),['PUT','PATCH'],true)) $input=array_merge($existing,$input);
        $v=self::pet_fields($input);
        if (is_wp_error($v)) return $v;
        $v['updated_at']=current_time('mysql');
        global $wpdb; $wpdb->update($wpdb->prefix.'panje_pets',$v,['id'=>$id,'user_id'=>get_current_user_id()]);
        return ['id'=>$id,'status'=>'updated'];
    }

    public static function delete_pet(WP_REST_Request $r) {
        $id=(int)$r['id']; if (!self::owned_pet($id))
            return new WP_Error('panje_not_found','Ù¾Øª ÛŒØ§ÙØª Ù†Ø´Ø¯',['status'=>404]);
        global $wpdb;
        $has_history=(int)$wpdb->get_var($wpdb->prepare(
          "SELECT COUNT(*) FROM {$wpdb->prefix}panje_reports WHERE pet_id=%d AND user_id=%d",$id,get_current_user_id()
        ));
        if ($has_history)
            return new WP_Error('panje_pet_has_history','Ù¾ØªÛŒ Ú©Ù‡ Ø³Ø§Ø¨Ù‚Ù‡ Ú¯Ø²Ø§Ø±Ø´ Ø¯Ø§Ø±Ø¯ Ø­Ø°Ù Ù†Ù…ÛŒâ€ŒØ´ÙˆØ¯Ø› Ù…ÛŒâ€ŒØªÙˆØ§Ù†ÛŒØ¯ Ø¨Ø¹Ø¯Ø§Ù‹ Ù‚Ø§Ø¨Ù„ÛŒØª Ø¨Ø§ÛŒÚ¯Ø§Ù†ÛŒ Ø±Ø§ Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯.',['status'=>409]);
        $wpdb->delete($wpdb->prefix.'panje_pets',['id'=>$id,'user_id'=>get_current_user_id()]);
        return ['id'=>$id,'status'=>'deleted'];
    }

    private static function pet_fields(array $d) {
        $species=$d['species']??'';
        if (!in_array($species,['dog','cat'],true))
            return new WP_Error('panje_species','Ú¯ÙˆÙ†Ù‡ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',['status'=>422]);

        $weight=(float)($d['weight']??0); $bcs=(int)($d['bcs']??0); $activity=(int)($d['activity']??0);
        if ($weight<=0 || $weight>250 || $bcs<1 || $bcs>9 || $activity<1 || $activity>10)
            return new WP_Error('panje_pet_validation','ÙˆØ²Ù†ØŒ BCS ÛŒØ§ ÙØ¹Ø§Ù„ÛŒØª Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',['status'=>422]);

        $allowed_breeds=$species==='dog'
          ? ['toy','small','medium','large','giant']
          : ['domestic','exotic'];
        $breed_name=sanitize_text_field($d['breed_name']??'');
        if($breed_name==='') return new WP_Error('panje_breed_name','Ù†Ú˜Ø§Ø¯ Ù¾Øª Ø§Ù„Ø²Ø§Ù…ÛŒ Ø§Ø³Øª',['status'=>422]);
        $breed=sanitize_key($d['breed']??'');
        if (!in_array($breed,$allowed_breeds,true))
            return new WP_Error('panje_breed','Ú¯Ø±ÙˆÙ‡ Ù†Ú˜Ø§Ø¯ÛŒ Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',['status'=>422]);

        $status=in_array($d['status']??'adult',['adult','growing','pregnant','lactating'],true)
          ? $d['status'] : 'adult';
        $sex=in_array($d['sex']??'', ['male','female'],true)?$d['sex']:'';
        if (!$sex) return new WP_Error('panje_sex','Ø¬Ù†Ø³ÛŒØª Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',['status'=>422]);
        if (in_array($status,['pregnant','lactating'],true) && $sex!=='female')
            return new WP_Error('panje_reproductive','ÙˆØ¶Ø¹ÛŒØª Ø¢Ø¨Ø³ØªÙ†ÛŒ/Ø´ÛŒØ±Ø¯Ù‡ÛŒ ÙÙ‚Ø· Ø¨Ø±Ø§ÛŒ Ù¾Øª Ù…Ø§Ø¯Ù‡ Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª',['status'=>422]);

        return [
          'name'=>sanitize_text_field($d['name']??''),'species'=>$species,'breed_name'=>$breed_name,'breed'=>$breed,
          'age_years'=>max(0,min(40,(int)($d['age_years']??0))),
          'age_months'=>max(0,min(11,(int)($d['age_months']??0))),
          'age_weeks'=>max(0,min(53,(int)($d['age_weeks']??0))),
          'sex'=>$sex,'neutered'=>!empty($d['neutered'])?1:0,
          'weight'=>$weight,'bcs'=>$bcs,'activity'=>$activity,'status'=>$status,
          'reproductive_data'=>wp_json_encode(self::sanitize_map($d['reproductive']??[]),JSON_UNESCAPED_UNICODE),
          'medical_data'=>wp_json_encode(self::sanitize_map($d['medical']??[]),JSON_UNESCAPED_UNICODE),
          'environment_data'=>wp_json_encode(self::sanitize_map($d['environment']??[]),JSON_UNESCAPED_UNICODE)
        ];
    }

    private static function sanitize_map($value) {
        if (!is_array($value)) return [];
        $out=[];
        foreach ($value as $k=>$v) {
            $key=sanitize_key((string)$k);
            if (is_array($v)) $out[$key]=array_map('sanitize_text_field',$v);
            elseif (is_bool($v)) $out[$key]=$v;
            elseif (is_numeric($v)) $out[$key]=0+$v;
            else $out[$key]=sanitize_text_field((string)$v);
        }
        return $out;
    }

    public static function wallet() {
        global $wpdb; $uid=get_current_user_id();
        $transactions=$wpdb->get_results($wpdb->prepare(
          "SELECT id,type,operation,amount,balance_before,balance_after,reference,request_uuid,created_at
           FROM {$wpdb->prefix}panje_transactions WHERE user_id=%d ORDER BY id DESC LIMIT 50",$uid
        ),ARRAY_A);
        $topups=[];
        if(function_exists('wc_get_product')){
            $ids=get_posts([
              'post_type'=>'product','post_status'=>'publish','numberposts'=>20,'fields'=>'ids',
              'meta_query'=>[[
                'key'=>'_panje_wallet_credit','value'=>0,'compare'=>'>','type'=>'NUMERIC'
              ]]
            ]);
            foreach($ids as $product_id){
                $product=wc_get_product($product_id);
                if(!$product||!$product->is_purchasable())continue;
                $credit=(int)get_post_meta($product_id,'_panje_wallet_credit',true);
                if($credit<=0)continue;
                $topups[]=[
                  'id'=>(int)$product_id,'name'=>$product->get_name(),'credit'=>$credit,
                  'price'=>(float)$product->get_price(),'url'=>get_permalink($product_id)
                ];
            }
        }
        return [
          'balance'=>Panje_Wallet::balance($uid),
          'currency'=>'ØªÙˆÙ…Ø§Ù†',
          'transactions'=>$transactions,
          'topups'=>$topups
        ];
    }

    public static function foods(WP_REST_Request $r) {
        global $wpdb;
        $search=sanitize_text_field((string)$r->get_param('search'));
        $category=sanitize_text_field((string)$r->get_param('category'));
        $sql="SELECT id,food_key,name,category,source_type,version FROM {$wpdb->prefix}panje_food_database WHERE active=1";
        $args=[];
        if ($search!=='') { $sql.=" AND name LIKE %s"; $args[]='%'.$wpdb->esc_like($search).'%'; }
        if ($category!=='') { $sql.=" AND category=%s"; $args[]=$category; }
        $sql.=" ORDER BY name ASC LIMIT 250";
        return $args ? $wpdb->get_results($wpdb->prepare($sql,...$args),ARRAY_A) : $wpdb->get_results($sql,ARRAY_A);
    }

    public static function reports(WP_REST_Request $r) {
        $uid=get_current_user_id(); $pet_id=max(0,(int)$r->get_param('pet_id'));
        if ($pet_id && !self::owned_pet($pet_id,$uid))
            return new WP_Error('panje_not_found','Ù¾Øª ÛŒØ§ÙØª Ù†Ø´Ø¯',['status'=>404]);
        $limit=max(1,min(100,(int)$r->get_param('per_page') ?: (int)$r->get_param('limit') ?: 50));
        $page=max(1,(int)$r->get_param('page') ?: 1);
        $repo=\Panje\Core\Plugin::boot()->container()->get('report_repository');
        $rows=$repo->pageForUser($uid,$pet_id,$page,$limit);
        $total=$repo->countForUser($uid,$pet_id);
        $items=array_map(static function(array $row): array {
            return ['id'=>(int)$row['id'],'pet_id'=>(int)$row['pet_id'],'pet_name'=>$row['pet_name'],'request_id'=>$row['request_id'],'report_type'=>(string)$row['report_type'],'pdf_url'=>$row['pdf_url']?esc_url($row['pdf_url']):null,'created_at'=>(string)$row['created_at']];
        },$rows);
        // Keep the legacy array body for the current bundled UI; pagination metadata
        // is exposed through standard REST headers until the new UI is deployed.
        $response=new WP_REST_Response($items);
        $response->header('X-WP-Total',(string)$total);
        $response->header('X-WP-TotalPages',(string)ceil($total/$limit));
        $response->header('X-Panje-Page',(string)$page);
        $response->header('X-Panje-Per-Page',(string)$limit);
        return $response;
    }

    public static function report(WP_REST_Request $r) {
        $repo=\Panje\Core\Plugin::boot()->container()->get('report_repository');
        $row=$repo->findOwned((int)$r['id'],get_current_user_id());
        if (!$row) return new WP_Error('panje_not_found','Report not found',['status'=>404]);
        $pet=self::owned_pet((int)$row['pet_id']);
        $row['pet_name']=$pet['name']??'';
        $row['report']=json_decode((string)$row['report_json'],true);
        unset($row['report_json']);
        return $row;
    }
    private static function queue(string $type, WP_REST_Request $r) {
        global $wpdb; $uid=get_current_user_id(); $body=(array)$r->get_json_params(); $pet_id=(int)($body['pet_id']??0);
        $pet=self::owned_pet($pet_id,$uid); if(!$pet) return new WP_Error('panje_pet','Ù¾Øª ÛŒØ§ÙØª Ù†Ø´Ø¯',['status'=>404]);

        $parent_uuid=null;
        if($type==='generate'){
            $parent_uuid=sanitize_text_field((string)($body['analysis_request_id']??''));
            if(!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i',$parent_uuid))
                return new WP_Error('panje_analysis_request','Ø´Ù†Ø§Ø³Ù‡ ØªØ­Ù„ÛŒÙ„ Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª',['status'=>422]);
            $parent=$wpdb->get_var($wpdb->prepare(
              "SELECT id FROM {$wpdb->prefix}panje_requests
               WHERE request_uuid=%s AND user_id=%d AND pet_id=%d AND request_type='analyze' AND status='completed' LIMIT 1",
              $parent_uuid,$uid,$pet_id
            ));
            if(!$parent) return new WP_Error('panje_analysis_request','ØªØ­Ù„ÛŒÙ„ ØªÚ©Ù…ÛŒÙ„â€ŒØ´Ø¯Ù‡ Ù…Ø±Ø¨ÙˆØ· Ø¨Ù‡ Ø§ÛŒÙ† Ù¾Øª ÛŒØ§ÙØª Ù†Ø´Ø¯',['status'=>409]);
        }

        // Validate the nutrition payload before reserving a discount or touching the wallet.
        $diet=self::diet_items($body['current_diet']??[]);
        $foods=array_slice(array_values(array_unique(array_filter(array_map(
          static fn($x)=>sanitize_text_field((string)$x),(array)($body['foods']??[])
        )))),0,100);
        if($type==='analyze'&&!$diet)
            return new WP_Error('panje_diet','Ø­Ø¯Ø§Ù‚Ù„ ÛŒÚ© Ù…Ø§Ø¯Ù‡ ØºØ°Ø§ÛŒÛŒ Ø¨Ø§ Ù…Ù‚Ø¯Ø§Ø± Ù…Ø¹ØªØ¨Ø± Ù„Ø§Ø²Ù… Ø§Ø³Øª',['status'=>422]);
        if($type==='generate'&&!$foods)
            return new WP_Error('panje_foods','Ø­Ø¯Ø§Ù‚Ù„ ÛŒÚ© Ù…Ø§Ø¯Ù‡ ØºØ°Ø§ÛŒÛŒ Ø¨Ø±Ø§ÛŒ ØªÙˆÙ„ÛŒØ¯ Ø±Ú˜ÛŒÙ… Ù„Ø§Ø²Ù… Ø§Ø³Øª',['status'=>422]);

        $client_id=sanitize_text_field((string)($r->get_header('X-Panje-Idempotency-Key') ?: ($body['client_request_id']??'')));
        $client_id=mb_substr($client_id,0,80);
        if($client_id!==''){
            $existing=$wpdb->get_row($wpdb->prepare(
              "SELECT request_uuid,status,cost FROM {$wpdb->prefix}panje_requests WHERE user_id=%d AND request_type=%s AND client_request_id=%s ORDER BY id DESC LIMIT 1",
              $uid,$type,$client_id
            ),ARRAY_A);
            if($existing) return new WP_REST_Response(['request_id'=>$existing['request_uuid'],'status'=>$existing['status'],'charged'=>(int)$existing['cost'],'idempotent_replay'=>true],200);
        }

        $uuid=wp_generate_uuid4();
        $quote=Panje_Services::reserve($type,$uid,$uuid); if(is_wp_error($quote)) return $quote;
        $base_cost=(int)$quote['base']; $cost=(int)$quote['payable']; $discount_id=(int)$quote['discount_id'];

        $charged=Panje_Wallet::change($uid,-$cost,'debit','request:'.$uuid,$type,$uuid);
        if(is_wp_error($charged)){ Panje_Services::release($discount_id,$uuid); return $charged; }

        // Analyze and Generate have deliberately different strict FastAPI contracts.
        $payload=[
          'request_id'=>$uuid,
          'pet'=>self::api_pet($pet),
          'current_diet'=>$diet
        ];
        if($type==='generate'){
          $payload['foods']=$foods;
          $payload['auto_balance']=!empty($body['auto_balance']);
        }

        $ok=$wpdb->insert($wpdb->prefix.'panje_requests',[
          'request_uuid'=>$uuid,'client_request_id'=>$client_id?:null,'parent_request_uuid'=>$parent_uuid,'user_id'=>$uid,'pet_id'=>$pet_id,
          'request_type'=>$type,'status'=>'pending','base_cost'=>$base_cost,'cost'=>$cost,'discount_id'=>$discount_id?:null,
          'payload'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE),'created_at'=>current_time('mysql')
        ]);
        $id=(int)$wpdb->insert_id;
        if(!$ok||!$id){
            Panje_Wallet::change($uid,$cost,'refund','refund:'.$uuid,$type,$uuid); Panje_Services::release($discount_id,$uuid);
            return new WP_Error('panje_request','Ø«Ø¨Øª Ø¯Ø±Ø®ÙˆØ§Ø³Øª Ù†Ø§Ù…ÙˆÙÙ‚ Ø¨ÙˆØ¯',['status'=>500]);
        }

        if(function_exists('as_enqueue_async_action')){
            $scheduled=as_enqueue_async_action('panje_process_request',[$id],'panje');
            $schedule_ok=(bool)$scheduled;
        }else{
            $scheduled=wp_schedule_single_event(time()+3,'panje_process_request',[$id],true);
            $schedule_ok=!is_wp_error($scheduled)&&$scheduled!==false;
        }
        if(!$schedule_ok){
            $wpdb->update($wpdb->prefix.'panje_requests',[
              'status'=>'failed','error_message'=>'Queue scheduling failed','completed_at'=>current_time('mysql')
            ],['id'=>$id]);
            Panje_Wallet::change($uid,$cost,'refund','refund:'.$uuid,$type,$uuid);
            Panje_Services::release($discount_id,$uuid);
            Panje_Log::error('queue','Queue scheduling failed',['request_uuid'=>$uuid,'user_id'=>$uid]);
            return new WP_Error('panje_queue','Ø²Ù…Ø§Ù†â€ŒØ¨Ù†Ø¯ÛŒ Ù¾Ø±Ø¯Ø§Ø²Ø´ Ù†Ø§Ù…ÙˆÙÙ‚ Ø¨ÙˆØ¯ Ùˆ Ù…Ø¨Ù„Øº Ø¨Ø§Ø²Ú¯Ø´Øª Ø¯Ø§Ø¯Ù‡ Ø´Ø¯',['status'=>503]);
        }
        Panje_Log::info('queue','Request queued',['request_uuid'=>$uuid,'user_id'=>$uid,'type'=>$type,'cost'=>$cost]);

        return new WP_REST_Response([
          'request_id'=>$uuid,'status'=>'pending','base_price'=>$base_cost,'charged'=>$cost,'discount'=>$quote['discount'],'balance'=>$charged
        ],202);
    }

    private static function diet_items($items): array {
        $out=[];
        foreach (array_slice((array)$items,0,200) as $item) {
            if (!is_array($item)) continue;
            $source=sanitize_key($item['source_type']??'home');
            if (!in_array($source,['dry','wet','home','treat','supplement'],true)) continue;
            $food=sanitize_text_field($item['food']??'');
            $grams=(float)($item['grams_per_day']??0);
            if ($food!=='' && $grams>0 && $grams<=10000)
                $out[]=['source_type'=>$source,'food'=>$food,'grams_per_day'=>$grams];
        }
        return $out;
    }

    private static function api_pet(array $p): array {
        return [
          'name'=>$p['name'],'species'=>$p['species'],'breed'=>$p['breed'],
          'age_years'=>(int)$p['age_years'],'age_months'=>(int)$p['age_months'],'age_weeks'=>(int)$p['age_weeks'],
          'sex'=>$p['sex'],'neutered'=>(bool)$p['neutered'],'weight'=>(float)$p['weight'],
          'bcs'=>(int)$p['bcs'],'activity'=>(int)$p['activity'],'status'=>$p['status'],
          'reproductive'=>json_decode($p['reproductive_data']?:'[]',true),
          'medical'=>json_decode($p['medical_data']?:'[]',true),
          'environment'=>json_decode($p['environment_data']?:'[]',true)
        ];
    }

    public static function process(int $id): void {
        global $wpdb; $table=$wpdb->prefix.'panje_requests';
        $req=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id),ARRAY_A);
        if (!$req || !in_array($req['status'],['pending','retry'],true)) return;

        // Atomic claim: only one worker can move this request into processing.
        $claimed=$wpdb->query($wpdb->prepare(
          "UPDATE $table SET status='processing',started_at=%s,attempts=attempts+1
           WHERE id=%d AND status IN('pending','retry')",current_time('mysql'),$id
        ));
        if (!$claimed) return;

        $payload=json_decode($req['payload'],true);
        $resp=Panje_API::post($req['request_type']==='generate'?'generate':'analyze',$payload);
        $code=is_wp_error($resp)?0:wp_remote_retrieve_response_code($resp);
        $s=Panje_DB::settings();

        if (is_wp_error($resp)||$code<200||$code>=300) {
            $attempt=(int)$req['attempts']+1;
            $err=is_wp_error($resp)?$resp->get_error_message():'HTTP '.$code;
            $retryable=is_wp_error($resp)||in_array($code,[408,425,429],true)||$code>=500;
            if ($retryable && $attempt<=(int)$s['max_retries']) {
                $wpdb->update($table,['status'=>'retry','error_message'=>$err],['id'=>$id]);
                $delay=min(300,30*$attempt);
                if(function_exists('as_schedule_single_action')){
                    $scheduled=as_schedule_single_action(time()+$delay,'panje_process_request',[$id],'panje');
                }else{
                    $scheduled=wp_schedule_single_event(time()+$delay,'panje_process_request',[$id],true);
                }
                if($scheduled&&!is_wp_error($scheduled))return;
                $err.='; retry scheduling failed';
            }
            $wpdb->update($table,['status'=>'failed','error_message'=>$err,'completed_at'=>current_time('mysql')],['id'=>$id]);
            Panje_Wallet::change((int)$req['user_id'],(int)$req['cost'],'refund','refund:'.$req['request_uuid'],$req['request_type'],$req['request_uuid']);
            Panje_Services::release((int)($req['discount_id']??0),$req['request_uuid']);
            Panje_Log::error('queue','Request failed',['request_uuid'=>$req['request_uuid'],'user_id'=>(int)$req['user_id'],'error'=>$err]);
            return;
        }

        $json=json_decode(wp_remote_retrieve_body($resp),true);
        $semantic_error = !is_array($json)
          || (($json['status'] ?? '') !== 'ok')
          || (($json['request_id'] ?? '') !== $req['request_uuid']);
        if ($semantic_error) {
            $err = !is_array($json)
              ? 'Invalid JSON response'
              : sanitize_text_field((string)($json['message'] ?? 'Python response contract error'));
            $wpdb->update($table,['status'=>'failed','error_message'=>$err,'completed_at'=>current_time('mysql')],['id'=>$id]);
            Panje_Wallet::change((int)$req['user_id'],(int)$req['cost'],'refund','refund:'.$req['request_uuid'],$req['request_type'],$req['request_uuid']);
            Panje_Services::release((int)($req['discount_id']??0),$req['request_uuid']);
            Panje_Log::error('queue','Python semantic failure',['request_uuid'=>$req['request_uuid'],'user_id'=>(int)$req['user_id'],'error'=>$err]);
            return;
        }

        $wpdb->update($table,[
          'status'=>'completed','response'=>wp_json_encode($json,JSON_UNESCAPED_UNICODE),
          'error_message'=>null,'completed_at'=>current_time('mysql')
        ],['id'=>$id]);

        Panje_Log::info('queue','Request completed',['request_uuid'=>$req['request_uuid'],'user_id'=>(int)$req['user_id']]);
        $wpdb->insert($wpdb->prefix.'panje_reports',[
          'user_id'=>$req['user_id'],'pet_id'=>$req['pet_id'],'request_id'=>$id,'report_type'=>$req['request_type'],
          'report_json'=>wp_json_encode($json,JSON_UNESCAPED_UNICODE),'created_at'=>current_time('mysql')
        ]);
        if($req['request_type']==='generate'&&!empty($req['parent_request_uuid'])){
            Panje_PDF::invalidate_request_pdf(
              (string)$req['parent_request_uuid'],
              (int)$req['user_id'],
              (int)$req['pet_id']
            );
        }
    }

    public static function status(WP_REST_Request $r) {
        $uuid=sanitize_text_field((string)$r['uuid']);
        $repo=\Panje\Core\Plugin::boot()->container()->get('request_repository');
        $row=$repo->findByUuid($uuid,get_current_user_id());
        if (!$row) return new WP_Error('panje_not_found','Request not found',['status'=>404]);
        $row=array_intersect_key($row,array_flip(['request_uuid','request_type','status','response','error_message','created_at','started_at','completed_at']));
        if ($row['response']) $row['response']=json_decode($row['response'],true);
        return $row;    }
}

