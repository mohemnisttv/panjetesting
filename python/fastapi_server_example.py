"""نمونه سرور FastAPI برای الهام‌گیری در پیاده‌سازی موتور واقعی Panje.

این فایل محاسبه علمی انجام نمی‌دهد؛ محل اتصال به موتور واقعی Python را مشخص می‌کند.
اجرا: PANJE_API_KEY=change-me uvicorn fastapi_server_example:app --host 0.0.0.0 --port 8000
"""
from __future__ import annotations

import hmac
import os
from typing import Any

from fastapi import Depends, FastAPI, Header, HTTPException, Request, Response, status
from pydantic import BaseModel, ConfigDict, Field

API_KEY = os.getenv("PANJE_API_KEY", "")
app = FastAPI(title="Panje Nutrition Engine", version="v1")

@app.middleware("http")
async def limit_body_size(request: Request, call_next):
    length = request.headers.get("content-length")
    if length and not length.isdecimal():
        return Response(status_code=400)
    if length and int(length) > 2_000_000:
        return Response(content='{"success":false,"error":{"code":"payload_too_large"}}', status_code=413, media_type="application/json")
    return await call_next(request)


class Pet(BaseModel):
    model_config = ConfigDict(extra="allow")
    name: str = Field(min_length=1, max_length=120)
    species: str = Field(pattern="^(dog|cat)$")
    weight: float = Field(gt=0, le=200)


class AnalysisRequest(BaseModel):
    model_config = ConfigDict(extra="allow")
    request_id: str = Field(min_length=1, max_length=80)
    food_version: str = Field(min_length=1, max_length=50)
    pet: Pet
    answers: dict[str, Any] = Field(default_factory=dict)
    current_diet: list[dict[str, Any]] = Field(default_factory=list)


class GenerateRequest(AnalysisRequest):
    selected_foods: list[str] = Field(default_factory=list)


class Warning(BaseModel):
    severity: str = Field(pattern="^(info|low|medium|high|critical)$")
    title: str = Field(min_length=1, max_length=200)
    explanation: str = ""
    reason: str = ""
    recommendation: str = ""


def require_api_key(x_panje_api_key: str | None = Header(default=None)) -> None:
    if not API_KEY:
        raise HTTPException(status_code=503, detail="server_key_not_configured")
    if not x_panje_api_key or not hmac.compare_digest(x_panje_api_key.encode(), API_KEY.encode()):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="invalid_api_key")


def envelope(data: dict[str, Any], request_id: str | None = None) -> dict[str, Any]:
    return {"success": True, "data": data, "meta": {"api_version": "v1", "request_id": request_id, "engine_version": app.version}, "error": None}


@app.get("/api/v1/health")
def health() -> dict[str, Any]:
    return envelope({"status": "ok", "engine_version": app.version, "food_version": "example"})


@app.post("/api/v1/analyze", dependencies=[Depends(require_api_key)])
def analyze(payload: AnalysisRequest, idempotency_key: str | None = Header(default=None)) -> dict[str, Any]:
    request_id = idempotency_key or payload.request_id
    # TODO: اجرای دقیق prototype موجود؛ این endpoint نباید منطق جدید تعریف کند.
    raise HTTPException(status_code=501, detail="scientific_engine_not_connected")


@app.post("/api/v1/generate", dependencies=[Depends(require_api_key)])
def generate(payload: GenerateRequest, idempotency_key: str | None = Header(default=None)) -> dict[str, Any]:
    request_id = idempotency_key or payload.request_id
    raise HTTPException(status_code=501, detail="scientific_engine_not_connected")


@app.post("/api/v1/render-pdf", dependencies=[Depends(require_api_key)])
def render_pdf(payload: dict[str, Any]) -> Response:
    # جایگزین renderer واقعی کنید؛ این حداقل سند PDF برای تست قرارداد است.
    raise HTTPException(status_code=501, detail="pdf_renderer_not_connected")
