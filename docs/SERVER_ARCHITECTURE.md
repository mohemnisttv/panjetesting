# راهنمای ساده طراحی سرور Python

سرور Python موتور علمی Panje است. WordPress فقط اطلاعات فرم را جمع‌آوری، درخواست را صف‌بندی و نتیجه را نمایش می‌دهد.

## اجزای پیشنهادی

```text
FastAPI
 ├─ API routers و احراز هویت
 ├─ Pydantic schemas
 ├─ Service layer برای اجرای use case
 ├─ موتور علمی موجود prototype
 ├─ Food repository برای CSV versioned
 ├─ PDF renderer
 └─ logging و health checks
```

## endpointها

| مسیر | کاربرد |
|---|---|
| `GET /api/v1/health` | بررسی زنده بودن سرور و نسخه موتور/غذا |
| `POST /api/v1/analyze` | تحلیل رژیم فعلی |
| `POST /api/v1/generate` | تولید رژیم جدید |
| `POST /api/v1/render-pdf` | ساخت PDF از نتیجه آماده |

تمام endpointهای غیر از health باید `X-Panje-API-Key` داشته باشند. مقدار `request_id` باید در تمام لاگ‌ها و پاسخ‌ها تکرار شود. WordPress برای جلوگیری از اجرای دوباره، `Idempotency-Key` می‌فرستد؛ سرور باید نتیجه همان کلید را cache یا ذخیره کند.

## مسئولیت‌ها

- سؤال‌ها، شرط‌های نمایش، محاسبات انرژی و مواد مغذی، قوانین، هشدارها و توصیه‌ها فقط در موتور Python هستند.
- WordPress نباید نتیجه را دوباره محاسبه یا اصلاح علمی کند.
- پاسخ باید شامل نسخه موتور و `food_version` باشد تا گزارش قابل بازتولید بماند.
- خطاها باید کد پایدار داشته باشند؛ پیام فنی در لاگ بماند و پیام قابل فهم به WordPress برگردد.

## چرخه درخواست

WordPress درخواست را ثبت می‌کند، آن را به صف می‌فرستد و Python پردازش می‌کند. برای محاسبات طولانی، پاسخ asynchronous با `status=pending` و شناسه درخواست برگردانید؛ WordPress وضعیت را با شناسه پیگیری می‌کند.

## استقرار production

از HTTPS، secret environment، محدودیت اندازه body، timeout، rate limit، لاگ ساختاریافته، health check و worker جداگانه استفاده کنید. فایل CSV فقط پس از بررسی header، نوع داده، checksum و نسخه معتبر فعال شود. API نباید فایل CSV یا secret را در پاسخ برگرداند.

