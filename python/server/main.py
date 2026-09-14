import hmac
from fastapi import Depends, FastAPI, Header, HTTPException, status
from .config import settings
from .engine import PrototypeEngine
from .schemas import GeneratePayload, RequestPayload

app = FastAPI(title="Panje Nutrition Engine", version="1.0.0")
engine = PrototypeEngine()

def auth(x_panje_api_key: str | None = Header(default=None)) -> None:
    if not settings.api_key or not x_panje_api_key or not hmac.compare_digest(x_panje_api_key, settings.api_key):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="invalid_api_key")

@app.get("/api/v1/health")
def health():
    return {"success": True, "data": {"status": "ok", "engine_version": app.version, "food_version": "managed-by-csv"}, "error": None}

@app.post("/api/v1/analyze", dependencies=[Depends(auth)])
def analyze(payload: RequestPayload):
    try: return {"success": True, "data": engine.analyze(payload), "error": None}
    except NotImplementedError as exc: raise HTTPException(status_code=501, detail=str(exc))

@app.post("/api/v1/generate", dependencies=[Depends(auth)])
def generate(payload: GeneratePayload):
    try: return {"success": True, "data": engine.generate(payload), "error": None}
    except NotImplementedError as exc: raise HTTPException(status_code=501, detail=str(exc))
