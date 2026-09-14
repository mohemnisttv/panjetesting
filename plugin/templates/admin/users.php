<?php
/** @var array $directory Prepared by the admin controller. */
if (!defined('ABSPATH')) { exit; }
$base = add_query_arg(['page' => 'panje-users', 's' => $directory['search']], admin_url('admin.php'));
?>
<div class="wrap panje-admin">
    <h1>کاربران پنجه</h1>
    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="panje-user-search">
        <input type="hidden" name="page" value="panje-users">
        <label for="panje-user-search">جست‌وجوی نام، نام کاربری یا ایمیل</label>
        <input type="search" id="panje-user-search" name="s" value="<?php echo esc_attr($directory['search']); ?>" maxlength="200">
        <button class="button button-primary" type="submit">جست‌وجو</button>
        <?php if ($directory['search'] !== '') : ?>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=panje-users')); ?>">پاک‌کردن جست‌وجو</a>
        <?php endif; ?>
    </form>
    <p role="status"><?php echo esc_html(sprintf('تعداد کاربران: %s — صفحه %s از %s', number_format_i18n($directory['total']), number_format_i18n($directory['page']), number_format_i18n($directory['pages']))); ?></p>
    <div class="panje-table-scroll" role="region" aria-label="فهرست کاربران" tabindex="0">
        <table class="widefat striped">
            <thead><tr><th scope="col">شناسه</th><th scope="col">کاربر</th><th scope="col">ایمیل</th><th scope="col">کیف پول (تومان)</th><th scope="col">تغییر دستی موجودی</th><th scope="col">تعداد پت</th><th scope="col">مصرف (تومان)</th></tr></thead>
            <tbody>
                <?php if (!$directory['items']) : ?>
                    <tr><td colspan="7">کاربری با این مشخصات پیدا نشد.</td></tr>
                <?php endif; ?>
                <?php foreach ($directory['items'] as $user) : ?>
                    <tr>
                        <td><?php echo esc_html((string) $user['id']); ?></td>
                        <td><?php echo esc_html($user['name']); ?></td>
                        <td><bdi><?php echo esc_html($user['email']); ?></bdi></td>
                        <td><?php echo esc_html(number_format_i18n($user['balance'])); ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="panje-wallet-adjust">
                                <?php wp_nonce_field('panje_wallet_adjust'); ?>
                                <input type="hidden" name="action" value="panje_wallet_adjust">
                                <input type="hidden" name="user_id" value="<?php echo esc_attr((string) $user['id']); ?>">
                                <label class="screen-reader-text" for="panje-adjust-<?php echo esc_attr((string) $user['id']); ?>"><?php echo esc_html('تغییر موجودی ' . $user['name']); ?></label>
                                <input id="panje-adjust-<?php echo esc_attr((string) $user['id']); ?>" type="number" name="amount" min="-100000000" step="1" required placeholder="+ / - مبلغ">
                                <button class="button button-primary" type="submit">ثبت</button>
                            </form>
                        </td>
                        <td><?php echo esc_html(number_format_i18n($user['pets'])); ?></td>
                        <td><?php echo esc_html(number_format_i18n($user['spent'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <nav class="panje-pagination" aria-label="صفحه‌بندی کاربران">
        <?php if ($directory['page'] > 1) : ?>
            <a class="button" href="<?php echo esc_url(add_query_arg('paged', $directory['page'] - 1, $base)); ?>">قبلی</a>
        <?php endif; ?>
        <?php if ($directory['page'] < $directory['pages']) : ?>
            <a class="button" href="<?php echo esc_url(add_query_arg('paged', $directory['page'] + 1, $base)); ?>">بعدی</a>
        <?php endif; ?>
    </nav>
</div>
