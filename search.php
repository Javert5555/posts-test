<?php

function dbConnect(
    string $host,
    int $port,
    string $user,
    string $password,
    string $db
) : PDO {
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Ошибка подключения к БД ' . $e->getMessage());
    }
    return $pdo;
}

function getSearch() : mixed {
    $search = $_GET['comment'] ?? '';

    if (strlen($search) < 3) {
        echo '<p>Поисковый запрос должен содержать минимум 3 символа</p>';
        exit();
    }

    return $search;
}

function getPostsByComment(
    PDO $pdo,
    string $search
) : array {
    $query = "
    SELECT
        posts.id,
        posts.title,
        comments.body
    FROM
        posts
    LEFT JOIN comments ON
        posts.id = comments.post_id
    WHERE
        comments.body LIKE :search
    ";
    
    $postsQuery = $pdo->prepare($query);
    $postsQuery->execute(['search' => "%$search%"]);
    $postsQueryResult = $postsQuery->fetchAll(PDO::FETCH_ASSOC);
    return $postsQueryResult;
}

function showResults(array $queryResult) : void {
    if (!$queryResult) {
        echo '<p>Ничего не найдено</p>';
        return;
    }
    echo '<h2>Результаты поиска:</h2>';
    foreach ($queryResult as $str) {
        echo '<h2>' . htmlspecialchars($str['title']) . '</h2>';
        echo '<p>' . htmlspecialchars($str['body']) . '</p>';
        echo '<hr>';
        echo '<br>';
    }
}

function run() : void {
    $host = 'localhost';
    $port = '5432';
    $user = 'postgres';
    $password = 'postgres';
    $db = 'blog';
    
    $pdo = dbConnect(
        $host,
        $port,
        $user,
        $password,
        $db
    );
    
    $search = getSearch();
    
    $posts = getPostsByComment($pdo, $search);
    showResults($posts);
}

run();