# سرور مستقل Panje

این سرور اسکلت production است. محاسبات علمی عمداً در `engine.py` حدس زده نشده‌اند؛ باید prototype واقعی به `PrototypeEngine` متصل شود.

## اجرا

```powershell
cd python/server
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
$env:PANJE_API_KEY='یک کلید طولانی و تصادفی'
uvicorn main:app --host 0.0.0.0 --port 8000
```

در اجرای package، از ریشه پروژه استفاده کنید: `uvicorn server.main:app --app-dir python`.

## اتصال موتور علمی

یک کلاس جدید بسازید که متدهای `analyze` و `generate` را پیاده کند، سپس آن را جایگزین `PrototypeEngine` در `main.py` کنید. ورودی‌ها همان پاسخ‌های فرم موجود هستند؛ سؤال یا قانون جدید در سرور اضافه نکنید.
