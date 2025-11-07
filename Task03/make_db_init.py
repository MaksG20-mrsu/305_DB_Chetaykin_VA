import csv
import os

DATASET_DIR = 'dataset'
SQL_OUTPUT_FILE = 'db_init.sql'
DB_NAME = 'movies_rating.db'

TABLES_FILES = {
    'movies': 'movies.csv',
    'ratings': 'ratings.csv',
    'tags': 'tags.csv',
    'users': 'users.txt'  
}

def generate_sql_script():
    """
    Генерирует SQL-скрипт для создания и наполнения базы данных.
    """
    with open(SQL_OUTPUT_FILE, 'w', encoding='utf-8') as f:
        f.write("-- Удаление таблиц, если они существуют\n")
        f.write("DROP TABLE IF EXISTS movies;\n")
        f.write("DROP TABLE IF EXISTS ratings;\n")
        f.write("DROP TABLE IF EXISTS tags;\n")
        f.write("DROP TABLE IF EXISTS users;\n")
        f.write("\n")

        f.write("-- Создание таблиц\n")
        f.write("""
CREATE TABLE movies (
    id INTEGER PRIMARY KEY,
    title TEXT,
    year INTEGER,
    genres TEXT
);
""")
        f.write("""
CREATE TABLE ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    movie_id INTEGER,
    rating REAL,
    timestamp INTEGER
);
""")
        f.write("""
CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    movie_id INTEGER,
    tag TEXT,
    timestamp INTEGER
);
""")
        f.write("""
CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT,
    email TEXT,
    gender TEXT,
    register_date TEXT,
    occupation TEXT
);
""")
        f.write("\n")

        f.write("-- Загрузка данных\n")

        print(f"Обработка {TABLES_FILES['movies']}...")
        with open(os.path.join(DATASET_DIR, TABLES_FILES['movies']), 'r', encoding='utf-8') as file:
            reader = csv.reader(file)
            next(reader) 
            for row in reader:
                movie_id, title, genres = row
                title_clean = title.replace("'", "''")
                year_str = title.rfind('(')
                year = 1900 
                if year_str != -1 and title[year_str+1:year_str+5].isdigit():
                    year = int(title[year_str+1:year_str+5])
                    title_clean = title[:year_str].strip().replace("'", "''")
                
                f.write(f"INSERT INTO movies (id, title, year, genres) VALUES ({movie_id}, '{title_clean}', {year}, '{genres}');\n")

        print(f"Обработка {TABLES_FILES['ratings']}...")
        with open(os.path.join(DATASET_DIR, TABLES_FILES['ratings']), 'r', encoding='utf-8') as file:
            reader = csv.reader(file)
            next(reader)
            for row in reader:
                user_id, movie_id, rating, timestamp = row
                f.write(f"INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ({user_id}, {movie_id}, {rating}, {timestamp});\n")

        print(f"Обработка {TABLES_FILES['tags']}...")
        with open(os.path.join(DATASET_DIR, TABLES_FILES['tags']), 'r', encoding='utf-8') as file:
            reader = csv.reader(file)
            next(reader)
            for row in reader:
                user_id, movie_id, tag, timestamp = row
                tag_clean = tag.replace("'", "''")
                f.write(f"INSERT INTO tags (user_id, movie_id, tag, timestamp) VALUES ({user_id}, {movie_id}, '{tag_clean}', {timestamp});\n")
        
        print(f"Обработка {TABLES_FILES['users']}...")
        with open(os.path.join(DATASET_DIR, TABLES_FILES['users']), 'r', encoding='utf-8') as file:
            for line in file:
                if '::' in line:
                    parts = line.strip().split('::')
                else:
                    parts = line.strip().split('|')

                if len(parts) == 6:
                    user_id, name, email, gender, reg_date, occupation = parts
                    name_clean = name.replace("'", "''")
                    f.write(f"INSERT INTO users (id, name, email, gender, register_date, occupation) VALUES ({user_id}, '{name_clean}', '{email}', '{gender}', '{reg_date}', '{occupation}');\n")

if __name__ == '__main__':
    generate_sql_script()
    print(f"\nSQL-скрипт '{SQL_OUTPUT_FILE}' успешно сгенерирован.")