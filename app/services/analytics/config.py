import os

DB_HOST = os.getenv('TERE_DB_HOST', 'localhost')
DB_USER = os.getenv('TERE_DB_USER', 'root')
DB_PASS = os.getenv('TERE_DB_PASS', '')
DB_NAME = os.getenv('TERE_DB_NAME', 'dbterelearn')
DB_PORT = int(os.getenv('TERE_DB_PORT', '3306'))
