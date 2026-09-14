# راهنمای تست افزونه

## بررسی سریع محلی

```bash
python tools/verify_plugin.py
python -m py_compile python/fastapi_server_example.py
```

## بررسی WordPress

در یک سایت آزمایشی، افزونه را فعال کنید، جدول‌ها را بررسی کنید، یک پت بسازید، گزارش آزمایشی ایجاد کنید و مالکیت گزارش را با کاربر دوم بررسی کنید. سپس import و rollback یک CSV، تست اتصال API و تولید PDF را اجرا کنید.

PHP CLI در محیط فعلی موجود نیست؛ بنابراین lint PHP و تست integration باید در محیط WordPress اجرا شود.
