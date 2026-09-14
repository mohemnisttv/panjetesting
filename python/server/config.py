from pydantic_settings import BaseSettings, SettingsConfigDict

class Settings(BaseSettings):
    api_key: str
    food_csv: str = "plugin/data/default-foods.csv"
    model_config = SettingsConfigDict(env_prefix="PANJE_", env_file=".env")

settings = Settings()
