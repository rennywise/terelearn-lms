import pymysql
from config import DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT


def get_conn():
    return pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        port=DB_PORT,
        charset='utf8mb4',
        autocommit=False,
        cursorclass=pymysql.cursors.DictCursor,
    )
