<?php
if (!defined('ABSPATH')) exit;

final class Panje_Shortcodes {
    private static bool $needs_ui = false;

    public static function boot(): void {
        add_shortcode('panje_diet_form',[__CLASS__,'form']);
        add_shortcode('panje_user_history',[__CLASS__,'history']);
        add_action('wp_enqueue_scripts',[__CLASS__,'assets']);
        add_filter('script_loader_tag',[__CLASS__,'module_tags'],10,3);
    }

    public static function assets(): void {
        global $post;
        $content = is_object($post) && isset($post->post_content) ? (string)$post->post_content : '';
        $elementor = '';
        if (is_singular()) {
            $post_id = get_queried_object_id();
            if ($post_id) $elementor = (string)get_post_meta($post_id,'_elementor_data',true);
        }
        self::$needs_ui =
            has_shortcode($content,'panje_diet_form') ||
            has_shortcode($content,'panje_user_history') ||
            str_contains($elementor,'panje_diet_form') ||
            str_contains($elementor,'panje_user_history');
        if (!self::$needs_ui) return;

        wp_enqueue_style('panje-ui',PANJE_URL.'assets/css/panje.css',[],PANJE_VERSION);

        // Official Microsoft Fluent UI Web Components, pinned for deterministic production behavior.
        wp_enqueue_script(
            'panje-fluent',
            'https://unpkg.com/@fluentui/web-components@3.1.3',
            [],
            '3.1.3',
            true
        );
        wp_enqueue_script('panje-ui',PANJE_URL.'assets/ui/panje-ui.js',['panje-fluent'],PANJE_VERSION,true);
        wp_add_inline_script('panje-ui','window.PanjeConfig='.wp_json_encode([
          'restUrl'=>esc_url_raw(rest_url('panje/v1/')),
          'nonce'=>wp_create_nonce('wp_rest'),
          'brand'=>'#0C953B',
          'currency'=>'تومان'
        ],JSON_UNESCAPED_UNICODE).';','before');
    }

    public static function module_tags(string $tag,string $handle,string $src): string {
        if ($handle!=='panje-fluent') return $tag;
        return sprintf(
            '<script type="module" src="%s" id="%s-js"></script>'."\n",
            esc_url($src),
            esc_attr($handle)
        );
    }

    public static function form(): string {
        if(!is_user_logged_in()) return '<div class="panje-card">برای استفاده از پنجه وارد حساب کاربری شوید.</div>';
        $balance=Panje_Wallet::balance(get_current_user_id());
        $fallback='<section class="panje-hero"><h2>پنجه</h2><p>تحلیل و تولید رژیم هوشمند سگ و گربه</p><span class="panje-pill">موجودی: '.esc_html(number_format_i18n($balance)).' تومان</span></section>
        <section class="panje-card"><h3>فرآیند رژیم</h3><div class="panje-grid"><div>۱. انتخاب پت</div><div>۲. رژیم فعلی</div><div>۳. تحلیل</div><div>۴. رژیم اختصاصی</div></div></section>';
        return '<div id="panje-react-root" class="panje-shell" dir="rtl">'.$fallback.'</div>';
    }

    public static function history(): string {
        if(!is_user_logged_in()) return '<div class="panje-card">ابتدا وارد شوید.</div>';
        global $wpdb;$rows=$wpdb->get_results($wpdb->prepare(
          "SELECT r.id,r.pet_id,r.report_type,r.pdf_url,r.created_at,p.name pet_name FROM {$wpdb->prefix}panje_reports r
           LEFT JOIN {$wpdb->prefix}panje_pets p ON p.id=r.pet_id
           WHERE r.user_id=%d ORDER BY r.id DESC LIMIT 100",get_current_user_id()
        ));
        ob_start();?><div class="panje-shell" dir="rtl"><section class="panje-card"><h3>تاریخچه پنجه</h3><?php
        if(!$rows) echo '<p>هنوز گزارشی ثبت نشده است.</p>';
        $last_pet=null;
        foreach($rows as $r) {
            if($last_pet!==(int)$r->pet_id){
                $last_pet=(int)$r->pet_id;
                echo '<h4 style="margin:18px 0 6px">'.esc_html($r->pet_name).'</h4>';
            }
            echo '<div class="panje-row"><span>'.esc_html($r->report_type==='analyze'?'تحلیل':'رژیم جدید').'</span><small>'.esc_html($r->created_at).'</small>';
            if($r->pdf_url) echo '<a href="'.esc_url($r->pdf_url).'" target="_blank" rel="noopener noreferrer">PDF</a>';
            else echo '<span class="panje-muted">PDF ساخته نشده</span>';
            echo '</div>';
        }
        ?></section></div><?php return ob_get_clean();
    }
}
