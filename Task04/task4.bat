#!/bin/bash
chcp 65001 > /dev/null

echo "Запуск скрипта для генерации db_init.sql..."
py make_db_init.py

echo "Создание и наполнение базы данных movies_rating.db..."
rm -f movies_rating.db
./sqlite3 movies_rating.db < db_init.sql
echo "База данных успешно создана."
echo " "

echo "1. Найти все пары пользователей, оценивших один и тот же фильм..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "SELECT
    u1.name AS user1_name,
    u2.name AS user2_name,
    m.title AS movie_title
FROM
    ratings r1
JOIN
    ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id
JOIN
    users u1 ON r1.user_id = u1.id
JOIN
    users u2 ON r2.user_id = u2.id
JOIN
    movies m ON r1.movie_id = m.id
ORDER BY
    u1.name, u2.name
LIMIT 100;"
echo " "

echo "2. Найти 10 самых старых оценок от разных пользователей..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "SELECT
    m.title,
    u.name,
    r.rating,
    strftime('%Y-%m-%d', r.timestamp, 'unixepoch') AS rating_date
FROM
    ratings r
JOIN
    movies m ON r.movie_id = m.id
JOIN
    users u ON r.user_id = u.id
ORDER BY
    r.timestamp
LIMIT 10;"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным и минимальным средним рейтингом..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "WITH MovieAvgRatings AS (
    SELECT movie_id, AVG(rating) as avg_rating FROM ratings GROUP BY movie_id
)
SELECT
    m.title,
    m.year,
    mar.avg_rating,
    CASE
        WHEN mar.avg_rating = (SELECT MAX(avg_rating) FROM MovieAvgRatings) THEN 'Да'
        ELSE 'Нет'
    END AS 'Рекомендуем'
FROM movies m
JOIN MovieAvgRatings mar ON m.id = mar.movie_id
WHERE mar.avg_rating = (SELECT MAX(avg_rating) FROM MovieAvgRatings)
   OR mar.avg_rating = (SELECT MIN(avg_rating) FROM MovieAvgRatings)
ORDER BY m.year, m.title;"
echo " "

echo "4. Вычислить количество и среднюю оценку от пользователей-мужчин..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "SELECT
    COUNT(r.rating) AS total_ratings,
    AVG(r.rating) AS average_rating
FROM ratings r
JOIN users u ON r.user_id = u.id
WHERE
    u.gender = 'M' AND
    CAST(strftime('%Y', r.timestamp, 'unixepoch') AS INTEGER) BETWEEN 2011 AND 2014;"
echo " "

echo "5. Составить список фильмов со средней оценкой и количеством пользователей..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "SELECT
    m.title,
    m.year,
    AVG(r.rating) AS average_rating,
    COUNT(r.user_id) AS ratings_count
FROM movies m
JOIN ratings r ON m.id = r.movie_id
GROUP BY m.id
ORDER BY m.year, m.title
LIMIT 20;"
echo " "

echo "6. Определить самый распространенный жанр фильма..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "WITH RECURSIVE split(movie_id, genre, rest_genres) AS (
    SELECT id, '', genres || '|' FROM movies
    UNION ALL SELECT
        movie_id,
        substr(rest_genres, 0, instr(rest_genres, '|')),
        substr(rest_genres, instr(rest_genres, '|') + 1)
    FROM split WHERE rest_genres != ''
)
SELECT
    genre,
    COUNT(DISTINCT movie_id) AS movie_count
FROM split
WHERE genre != '' AND genre != '(no genres listed)'
GROUP BY genre
ORDER BY movie_count DESC
LIMIT 1;"
echo " "

echo "7. Вывести список из 10 последних зарегистрированных пользователей..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "SELECT
    CASE
        WHEN INSTR(name, ' ') > 0 THEN SUBSTR(name, INSTR(name, ' ') + 1) || ' ' || SUBSTR(name, 1, INSTR(name, ' ') - 1)
        ELSE name
    END || '|' || register_date AS formatted_user
FROM users
ORDER BY
    CAST(SUBSTR(register_date, 7, 4) AS INTEGER) DESC,
    CAST(SUBSTR(register_date, 4, 2) AS INTEGER) DESC,
    CAST(SUBSTR(register_date, 1, 2) AS INTEGER) DESC
LIMIT 10;"
echo " "

echo "8. Определить дни недели вашего дня рождения..."
echo "--------------------------------------------------"
./sqlite3 movies_rating.db -box -echo "WITH RECURSIVE MyBirthdays(birthday_date) AS (
    SELECT '2005-11-15'
    UNION ALL
    SELECT date(birthday_date, '+1 year')
    FROM MyBirthdays
    WHERE CAST(strftime('%Y', birthday_date) AS INTEGER) < 2025
)
SELECT
    birthday_date AS 'Дата Рождения',
    CASE CAST(strftime('%w', birthday_date) AS INTEGER)
        WHEN 0 THEN 'Воскресенье'
        WHEN 1 THEN 'Понедельник'
        WHEN 2 THEN 'Вторник'
        WHEN 3 THEN 'Среда'
        WHEN 4 THEN 'Четверг'
        WHEN 5 THEN 'Пятница'
        WHEN 6 THEN 'Суббота'
    END AS 'День недели'
FROM MyBirthdays;"
echo " "

echo "Выполнение всех заданий завершено."