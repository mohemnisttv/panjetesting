# نمونه سرور FastAPI

این پوشه نمونه قرارداد سرور Panje را دارد. فایل `fastapi_server_example.py` فقط اسکلت API است؛ منطق علمی باید از prototype واقعی شما منتقل شود.

## اجرا

```bash
python -m venv .venv
. .venv/bin/activate       # Windows: .venv\Scripts\activate
pip install -r requirements-example.txt
export PANJE_API_KEY=change-me  # Windows PowerShell: $env:PANJE_API_KEY='change-me'
uvicorn fastapi_server_example:app --host 0.0.0.0 --port 8000
```

سپس `GET /api/v1/health` را بررسی کنید. endpointهای تحلیل، تولید و PDF به header `X-Panje-API-Key` نیاز دارند.
