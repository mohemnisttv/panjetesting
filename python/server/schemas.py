from typing import Any, Literal
from pydantic import BaseModel, Field

class Pet(BaseModel):
    name: str = Field(min_length=1, max_length=120)
    species: Literal["dog", "cat"]
    weight: float = Field(gt=0, le=200)

class RequestPayload(BaseModel):
    request_id: str = Field(min_length=1, max_length=80)
    food_version: str = Field(min_length=1, max_length=50)
    pet: Pet
    answers: dict[str, Any] = Field(default_factory=dict)
    current_diet: list[dict[str, Any]] = Field(default_factory=list)

class GeneratePayload(RequestPayload):
    selected_foods: list[str] = Field(default_factory=list)

class Warning(BaseModel):
    severity: Literal["info", "low", "medium", "high", "critical"]
    title: str
    explanation: str = ""
    reason: str = ""
    recommendation: str = ""
