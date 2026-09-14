from typing import Any, Protocol
from .schemas import GeneratePayload, RequestPayload

class ScientificEngine(Protocol):
    def analyze(self, payload: RequestPayload) -> dict[str, Any]: ...
    def generate(self, payload: GeneratePayload) -> dict[str, Any]: ...

class PrototypeEngine:
    """Adapter point for the real prototype; never invents scientific results."""
    def analyze(self, payload: RequestPayload) -> dict[str, Any]:
        raise NotImplementedError("Connect the validated prototype engine here")
    def generate(self, payload: GeneratePayload) -> dict[str, Any]:
        raise NotImplementedError("Connect the validated prototype engine here")
