from fastapi import FastAPI
import pymysql
from typing import List
from pydantic import BaseModel

DB_HOST = "37.140.192.23"
DB_PORT = 3306
DB_USER = "u2604360_wp766"
DB_PASS = "14rS93p]G["
DB_NAME = "u2604360_wpposter"
TABLE_NAME = "google_trends_data"

app = FastAPI()

class TrendItem(BaseModel):
    popular: str
    trend: str

def get_trends_from_db():
    connection = pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        charset="utf8mb4"
    )
    with connection.cursor() as cursor:
        cursor.execute(f"SELECT popular, trend FROM `{TABLE_NAME}`")
        rows = cursor.fetchall()
    connection.close()
    return [{"popular": row[0], "trend": row[1]} for row in rows]

@app.get("/trends", response_model=List[TrendItem])
def read_trends():
    return get_trends_from_db()