# Panje WordPress Plugin 3.0.0

Shortcodes:
- `[panje_diet_form]`
- `[panje_user_history]`

WooCommerce wallet:
1. Create a WooCommerce product.
2. Set "اعتبار کیف پول پنجه" on the product.
3. After successful/completed payment the credit is added exactly once.

Queue:
- Uses WooCommerce Action Scheduler when available.
- Falls back to WP-Cron.
- Wallet debit and refund use DB transactions + row lock.
- Request processing uses an atomic status transition to prevent duplicate workers.

API settings:
WordPress Admin > Panje
Store the FastAPI URL and API key there. Do not hardcode the API key.
