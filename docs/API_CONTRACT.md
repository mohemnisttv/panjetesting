# قرارداد API نسخه ۱

تمام پاسخ‌ها قالب زیر را دارند:

```json
{"success": true, "data": {}, "meta": {"api_version":"v1", "request_id":"..."}, "error": null}
```

در خطا `success=false` و `error` شامل `code`، `message` و در صورت نیاز `details` است. `answers` همان کلیدهای پرسشنامه موجود است و سرور نباید کلید جدیدی اختراع کند.

## تحلیل و تولید

هر دو درخواست شامل `request_id`، `food_version`، `pet`، `answers` و `current_diet` هستند. تولید رژیم علاوه بر آن `selected_foods` دارد. نتیجه تحلیل شامل `deficiencies`، `excesses` و `warnings` است. هشدارها باید مستقل از رژیم و دارای `severity`، `title`، `explanation`، `reason` و `recommendation` باشند.

## سازگاری

فیلدهای جدید باید optional اضافه شوند. حذف یا تغییر معنای فیلدها فقط با افزایش نسخه API انجام شود. نسخه موتور، نسخه غذا و زمان پردازش در `meta` یا نتیجه ثبت شوند.

