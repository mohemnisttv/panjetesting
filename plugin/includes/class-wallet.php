<?php
if (!defined('ABSPATH')) exit;

final class Panje_Wallet {
    public static function boot(): void {
        add_action('woocommerce_product_options_general_product_data',[__CLASS__,'product_field']);
        add_action('woocommerce_process_product_meta',[__CLASS__,'save_product_field']);
        add_action('woocommerce_payment_complete',[__CLASS__,'credit_order']);
        add_action('woocommerce_order_status_completed',[__CLASS__,'credit_order']);
        add_action('woocommerce_order_status_refunded',[__CLASS__,'reverse_order_credit']);
    }

    public static function balance(int $user_id): int {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare(
          "SELECT balance FROM {$wpdb->prefix}panje_wallet WHERE user_id=%d",$user_id
        ));
    }

    public static function change(int $uid,int $delta,string $type,string $reference,string $operation='',?string $uuid=null,bool $allow_negative=false) {
        global $wpdb;
        $wallet = $wpdb->prefix.'panje_wallet';
        $wpdb->query('START TRANSACTION');
        try {
            $before = (int)$wpdb->get_var($wpdb->prepare(
              "SELECT balance FROM $wallet WHERE user_id=%d FOR UPDATE",$uid
            ));
            if (!$allow_negative && $delta < 0 && $before < abs($delta)) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('panje_balance','موجودی کیف پول کافی نیست',['status'=>402]);
            }
            $after = $before + $delta;
            $wpdb->query($wpdb->prepare(
              "INSERT INTO $wallet(user_id,balance,updated_at) VALUES(%d,%d,%s)
               ON DUPLICATE KEY UPDATE balance=VALUES(balance),updated_at=VALUES(updated_at)",
              $uid,$after,current_time('mysql')
            ));
            $ok = $wpdb->insert($wpdb->prefix.'panje_transactions',[
              'user_id'=>$uid,'type'=>$type,'operation'=>$operation,'amount'=>abs($delta),
              'balance_before'=>$before,'balance_after'=>$after,'reference'=>$reference,
              'request_uuid'=>$uuid,'created_at'=>current_time('mysql')
            ]);
            if (!$ok) throw new RuntimeException('transaction insert failed');
            $wpdb->query('COMMIT');
            return $after;
        } catch (Throwable $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('panje_wallet_error',$e->getMessage(),['status'=>500]);
        }
    }

    public static function product_field(): void {
        woocommerce_wp_text_input([
          'id'=>'_panje_wallet_credit','label'=>'اعتبار کیف پول پنجه (تومان)','type'=>'number',
          'custom_attributes'=>['min'=>'0','step'=>'1'],
          'description'=>'اگر مقدار داشته باشد این محصول شارژ کیف پول پنجه است.'
        ]);
    }
    public static function save_product_field(int $post_id): void {
        if (isset($_POST['_panje_wallet_credit']))
            update_post_meta($post_id,'_panje_wallet_credit',max(0,(int)$_POST['_panje_wallet_credit']));
    }
    public static function credit_order(int $order_id): void {
        if (!function_exists('wc_get_order')) return;
        $order=wc_get_order($order_id);
        if (!$order || !$order->get_user_id() || $order->get_meta('_panje_wallet_credited')) return;
        $credit=0;
        foreach ($order->get_items() as $item)
            $credit += (int)get_post_meta($item->get_product_id(),'_panje_wallet_credit',true)*(int)$item->get_quantity();
        if ($credit<=0) return;
        $r=self::change((int)$order->get_user_id(),$credit,'credit','order:'.$order_id,'wallet_topup');
        if (!is_wp_error($r)) { $order->update_meta_data('_panje_wallet_credited',1); $order->save(); }
    }
    public static function reverse_order_credit(int $order_id): void {
        if (!function_exists('wc_get_order')) return;
        $order=wc_get_order($order_id);
        if (!$order || !$order->get_user_id()) return;
        if (!$order->get_meta('_panje_wallet_credited') || $order->get_meta('_panje_wallet_reversed')) return;

        $credit=0;
        foreach ($order->get_items() as $item)
            $credit += (int)get_post_meta($item->get_product_id(),'_panje_wallet_credit',true)*(int)$item->get_quantity();
        if ($credit<=0) return;

        // A paid top-up that is fully refunded must be reversed even if the credit
        // has already been spent. Negative balance keeps accounting consistent.
        $r=self::change(
            (int)$order->get_user_id(),
            -$credit,
            'debit',
            'order-refund:'.$order_id,
            'wallet_topup_reversal',
            null,
            true
        );
        if (!is_wp_error($r)) {
            $order->update_meta_data('_panje_wallet_reversed',1);
            $order->save();
        }
    }

}
