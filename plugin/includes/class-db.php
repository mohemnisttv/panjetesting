<?php
if (!defined('ABSPATH')) exit;

final class Panje_DB {
    public static function maybe_upgrade(): void {
        if (get_option('panje_db_version') !== PANJE_DB_VERSION) {
            ob_start();
            try { self::install(); } finally { ob_end_clean(); }
        }
    }
    public static function install(): void {
        global $wpdb; require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $c=$wpdb->get_charset_collate(); $p=$wpdb->prefix;

        dbDelta("CREATE TABLE {$p}panje_pets (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          user_id BIGINT UNSIGNED NOT NULL,
          name VARCHAR(120) NOT NULL, species VARCHAR(20) NOT NULL,
          breed_name VARCHAR(120) DEFAULT '', breed VARCHAR(80) DEFAULT '',
          age_years INT DEFAULT 0, age_months INT DEFAULT 0, age_weeks INT DEFAULT 0,
          sex VARCHAR(20) DEFAULT '', neutered TINYINT(1) DEFAULT 0,
          weight DECIMAL(8,3) NOT NULL, bcs TINYINT NOT NULL, activity TINYINT NOT NULL,
          status VARCHAR(30) NOT NULL DEFAULT 'adult',
          reproductive_data LONGTEXT NULL, medical_data LONGTEXT NULL, environment_data LONGTEXT NULL,
          created_at DATETIME NOT NULL, updated_at DATETIME NULL,
          PRIMARY KEY(id), KEY user_id(user_id), KEY species(species)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_requests (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          request_uuid CHAR(36) NOT NULL, client_request_id VARCHAR(80) NULL, parent_request_uuid CHAR(36) NULL,
          user_id BIGINT UNSIGNED NOT NULL, pet_id BIGINT UNSIGNED NOT NULL,
          request_type VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL DEFAULT 'pending',
          base_cost BIGINT NOT NULL DEFAULT 0, cost BIGINT NOT NULL DEFAULT 0, discount_id BIGINT UNSIGNED NULL,
          payload LONGTEXT NULL, response LONGTEXT NULL, error_message TEXT NULL,
          attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
          created_at DATETIME NOT NULL, started_at DATETIME NULL, completed_at DATETIME NULL,
          PRIMARY KEY(id), UNIQUE KEY request_uuid(request_uuid),
          UNIQUE KEY uniq_client_request(user_id,request_type,client_request_id),
          KEY user_status(user_id,status), KEY pet_id(pet_id), KEY parent_request_uuid(parent_request_uuid), KEY status_created(status,created_at)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_reports (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          user_id BIGINT UNSIGNED NOT NULL, pet_id BIGINT UNSIGNED NOT NULL, request_id BIGINT UNSIGNED NOT NULL,
          report_type VARCHAR(30) NOT NULL, report_json LONGTEXT NOT NULL, pdf_url TEXT NULL, pdf_hash CHAR(64) NULL,
          created_at DATETIME NOT NULL,
          PRIMARY KEY(id), KEY user_pet(user_id,pet_id), KEY request_id(request_id)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_wallet (
          user_id BIGINT UNSIGNED NOT NULL, balance BIGINT NOT NULL DEFAULT 0, updated_at DATETIME NOT NULL,
          PRIMARY KEY(user_id)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_transactions (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          user_id BIGINT UNSIGNED NOT NULL, type VARCHAR(30) NOT NULL, operation VARCHAR(50) NULL,
          amount BIGINT NOT NULL, balance_before BIGINT NOT NULL, balance_after BIGINT NOT NULL,
          reference VARCHAR(120) NULL, request_uuid CHAR(36) NULL, created_at DATETIME NOT NULL,
          PRIMARY KEY(id), UNIQUE KEY uniq_ref(reference,type),
          KEY user_created(user_id,created_at), KEY request_uuid(request_uuid)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_food_database (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          food_key VARCHAR(191) NOT NULL, name VARCHAR(191) NOT NULL, category VARCHAR(120) NULL,
          source_type VARCHAR(30) NULL, nutrients LONGTEXT NOT NULL, version VARCHAR(50) NOT NULL,
          active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL,
          PRIMARY KEY(id), UNIQUE KEY food_version(food_key,version), KEY active_category(active,category)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_food_versions (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, version VARCHAR(50) NOT NULL,
          status VARCHAR(20) NOT NULL DEFAULT 'active', filename VARCHAR(191) NULL,
          row_count INT UNSIGNED NOT NULL DEFAULT 0, checksum CHAR(64) NULL,
          imported_by BIGINT UNSIGNED NULL, created_at DATETIME NOT NULL,
          PRIMARY KEY(id), UNIQUE KEY version(version), KEY status(status)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_services (
          service_key VARCHAR(50) NOT NULL, label VARCHAR(120) NOT NULL,
          price BIGINT NOT NULL DEFAULT 0, active TINYINT(1) NOT NULL DEFAULT 1,
          created_at DATETIME NOT NULL, updated_at DATETIME NULL,
          PRIMARY KEY(service_key), KEY active(active)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_discounts (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, service_key VARCHAR(50) NOT NULL,
          title VARCHAR(120) NOT NULL, discount_type VARCHAR(20) NOT NULL,
          discount_value BIGINT NOT NULL DEFAULT 0, active TINYINT(1) NOT NULL DEFAULT 1,
          starts_at DATETIME NULL, ends_at DATETIME NULL,
          usage_limit INT UNSIGNED NULL, used_count INT UNSIGNED NOT NULL DEFAULT 0,
          per_user_limit INT UNSIGNED NULL, created_at DATETIME NOT NULL, updated_at DATETIME NULL,
          PRIMARY KEY(id), KEY service_active(service_key,active), KEY date_window(starts_at,ends_at)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_discount_usage (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, discount_id BIGINT UNSIGNED NOT NULL,
          user_id BIGINT UNSIGNED NOT NULL, request_uuid CHAR(36) NOT NULL, created_at DATETIME NOT NULL,
          PRIMARY KEY(id), UNIQUE KEY uniq_discount_request(discount_id,request_uuid),
          KEY discount_user(discount_id,user_id)
        ) $c;");

        dbDelta("CREATE TABLE {$p}panje_logs (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, level VARCHAR(20) NOT NULL,
          module VARCHAR(50) NOT NULL, message TEXT NOT NULL, request_uuid CHAR(36) NULL,
          user_id BIGINT UNSIGNED NULL, context LONGTEXT NULL, created_at DATETIME NOT NULL,
          PRIMARY KEY(id), KEY level_created(level,created_at), KEY request_uuid(request_uuid), KEY user_created(user_id,created_at)
        ) $c;");

        $now=current_time('mysql');
        $wpdb->query($wpdb->prepare(
          "INSERT IGNORE INTO {$p}panje_services(service_key,label,price,active,created_at) VALUES
           ('analyze','تحلیل رژیم فعلی',%d,1,%s),('generate','ساخت رژیم جدید',%d,1,%s)",
          20000,$now,100000,$now
        ));

        add_option('panje_settings',[
          'api_url'=>'','api_key'=>'','max_retries'=>2,'timeout'=>45,
          'pdf_title'=>'گزارش تخصصی پنجه','pdf_footer'=>'پنجه — تغذیه هوشمند حیوانات خانگی',
          'brand_color'=>'#0C953B'
        ]);
        // Encrypt legacy plaintext API secrets opportunistically during upgrade.
        $stored=(array)get_option('panje_settings',[]);
        if(!empty($stored['api_key']) && !str_starts_with((string)$stored['api_key'],'enc:')){
            $stored['api_key']=self::protect_secret((string)$stored['api_key']);
            update_option('panje_settings',$stored,false);
        }

        update_option('panje_db_version',PANJE_DB_VERSION,false);
        if(class_exists('Panje_Foods')) Panje_Foods::seed_default();
    }

    public static function protect_secret(string $plain): string {
        $plain=trim($plain);
        if($plain==='' || str_starts_with($plain,'enc:')) return $plain;
        $key=hash('sha256',wp_salt('auth'),true);

        if(function_exists('sodium_crypto_secretbox')){
            $nonce=random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $cipher=sodium_crypto_secretbox($plain,$nonce,$key);
            return 'enc:sodium:'.base64_encode($nonce.$cipher);
        }

        if(function_exists('openssl_encrypt')){
            $iv=random_bytes(12);$tag='';
            $cipher=openssl_encrypt($plain,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag);
            if($cipher!==false) return 'enc:gcm:'.base64_encode($iv.$tag.$cipher);
        }

        // Extremely old/minimal PHP builds: preserve functionality without pretending encryption exists.
        return $plain;
    }

    public static function reveal_secret(string $stored): string {
        if($stored==='' || !str_starts_with($stored,'enc:')) return $stored;
        $key=hash('sha256',wp_salt('auth'),true);

        if(str_starts_with($stored,'enc:sodium:') && function_exists('sodium_crypto_secretbox_open')){
            $raw=base64_decode(substr($stored,11),true);
            if($raw===false || strlen($raw)<=SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return '';
            $nonce=substr($raw,0,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $cipher=substr($raw,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $plain=sodium_crypto_secretbox_open($cipher,$nonce,$key);
            return $plain===false?'':$plain;
        }

        if(str_starts_with($stored,'enc:gcm:') && function_exists('openssl_decrypt')){
            $raw=base64_decode(substr($stored,8),true);
            if($raw===false || strlen($raw)<=28) return '';
            $iv=substr($raw,0,12);$tag=substr($raw,12,16);$cipher=substr($raw,28);
            $plain=openssl_decrypt($cipher,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag);
            return $plain===false?'':$plain;
        }
        return '';
    }

    public static function settings(): array {
        $s=wp_parse_args((array)get_option('panje_settings',[]),[
          'api_url'=>'','api_key'=>'','max_retries'=>2,'timeout'=>45,
          'pdf_title'=>'گزارش تخصصی پنجه','pdf_footer'=>'پنجه — تغذیه هوشمند حیوانات خانگی',
          'brand_color'=>'#0C953B'
        ]);
        $s['api_key']=self::reveal_secret((string)$s['api_key']);
        return $s;
    }
}
