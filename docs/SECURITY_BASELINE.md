# خط مبنای امنیت

- API فقط روی HTTPS منتشر شود و `PANJE_API_KEY` از environment یا secret manager خوانده شود.
- اندازه body محدود باشد و ورودی با Pydantic validate شود.
- برای production rate limit، reverse proxy و لاگ ساختاریافته فعال شود.
- API Key در log و response چاپ نشود.
- WordPress دسترسی گزارش را بر اساس مالکیت بررسی کند و فایل PDF را با endpoint محافظت‌شده ارائه دهد.
- خطای قابل نمایش به کاربر از جزئیات داخلی موتور جدا باشد.

