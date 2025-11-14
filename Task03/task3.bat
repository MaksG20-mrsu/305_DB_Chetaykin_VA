@echo off
chcp 65001 >nul

echo Запуск инициализации базы данных...
if exist make_db_init.py (
  python make_db_init.py
)


if exist sqlite3.exe (
  set SQLITE=sqlite3.exe
) else (
  set SQLITE=sqlite3
)

echo.
echo 1. Список фильмов с хотя бы одной оценкой (первые 10)
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT DISTINCT m.title, m.year FROM movies m INNER JOIN ratings r ON m.id = r.movie_id ORDER BY m.year, m.title LIMIT 10;"
echo.

echo 2. Пользователи, фамилия которых начинается на 'A' (первые 5)
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT id, name, email, gender, register_date, occupation FROM users WHERE (CASE WHEN instr(name,' ')=0 THEN name ELSE substr(name, instr(name,' ')+1) END) LIKE 'A%' ORDER BY register_date LIMIT 5;"
echo.

echo 3. Рейтинги в читабельном формате (первые 50)
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT u.name AS full_name, m.title, m.year, r.rating, strftime('%Y-%m-%d', r.timestamp, 'unixepoch') AS rating_date FROM ratings r JOIN users u ON r.user_id = u.id JOIN movies m ON r.movie_id = m.id ORDER BY u.name, m.title, r.rating LIMIT 50;"
echo.

echo 4. Фильмы с тегами (первые 40)
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT m.title, m.year, t.tag FROM movies m JOIN tags t ON m.id = t.movie_id ORDER BY m.year, m.title, t.tag LIMIT 40;"
echo.

echo 5. Самые свежие фильмы (весь последний год)
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT title, year FROM movies WHERE year = (SELECT MAX(year) FROM movies) ORDER BY title;"
echo.

echo 6. Драмы после 2005, понравившиеся женщинам (оценка >=4.5)
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT m.title, m.year, COUNT(*) AS cnt FROM movies m JOIN ratings r ON m.id = r.movie_id JOIN users u ON r.user_id = u.id WHERE m.genres LIKE '%Drama%' AND m.year > 2005 AND LOWER(u.gender) LIKE 'f%' AND r.rating >= 4.5 GROUP BY m.id ORDER BY m.year, m.title;"
echo.

echo 7. Количество пользователей по годам и года max/min
echo --------------------------------------------------
%SQLITE% movies_rating.db -box -echo "SELECT strftime('%Y', register_date, 'unixepoch') AS reg_year, COUNT(*) AS user_count FROM users GROUP BY reg_year ORDER BY reg_year;"
%SQLITE% movies_rating.db -box -echo "SELECT reg_year, user_count FROM (SELECT strftime('%Y', register_date, 'unixepoch') AS reg_year, COUNT(*) AS user_count FROM users GROUP BY reg_year) ORDER BY user_count DESC LIMIT 1;"
%SQLITE% movies_rating.db -box -echo "SELECT reg_year, user_count FROM (SELECT strftime('%Y', register_date, 'unixepoch') AS reg_year, COUNT(*) AS user_count FROM users GROUP BY reg_year) ORDER BY user_count ASC LIMIT 1;"
