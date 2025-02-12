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
        die('Ошибка подключения к БД ' . $e->getMessage() . "\n");
    }
    return $pdo;
}

function loadData(string $url) : mixed {
    $json = file_get_contents($url);
    return json_decode($json, true);
}

function insertData(
    PDO $pdo,
    array $table,
    mixed $data
) : int {
    try {
        $strCount = getNumberTableRows($pdo, $table['name']);

        if ($strCount !== 0) {
            return $strCount;
        }
        $placeholders = array_fill(0, count($table['apiColumns']), '?');
        $query = 'INSERT INTO ' . $table['name'] . '(' . implode(', ', $table['tableColumns']) . ') VALUES (' . implode(', ', $placeholders) . ')';

        foreach ($data as $str) {
            $dataValues = [];
            foreach ($table['apiColumns'] as $column) {
                $dataValues[] = $str[$column];
            }
            $insertQuery = $pdo->prepare($query);
            $insertQuery->execute($dataValues);
        }
        $strCount = getNumberTableRows($pdo, $table['name']);
    } catch (PDOException $e) {
        echo 'Ошибка при вставке данных ' . $e->getMessage() . "\n";
    }

    return $strCount;
}

function getNumberTableRows(PDO $pdo, string $tableName) {
    $query = $pdo->prepare("SELECT COUNT(*) FROM $tableName");
    $query->execute();
    $count = $query->fetchColumn();
    return $count;
}

function run() : void {
    $postTable = ['name' => 'posts', 'apiColumns' => ['id', 'userId', 'title', 'body'], 'tableColumns' => ['id', 'user_id', 'title', 'body']];
    $commentsTable = ['name' => 'comments', 'apiColumns' => ['id', 'postId', 'name', 'email', 'body'], 'tableColumns' => ['id', 'post_id', 'name', 'email', 'body']];
    
    $host = 'localhost';
    $port = '5432';
    $user = 'postgres';
    $password = 'postgres';
    $db = 'blog';
    
    $postsUrl = 'https://jsonplaceholder.typicode.com/posts';
    $commentsUrl = 'https://jsonplaceholder.typicode.com/comments';
    
    $posts = loadData($postsUrl);
    $comments = loadData($commentsUrl);
    
    $pdo = dbConnect(
        $host,
        $port,
        $user,
        $password,
        $db
    );
    
    $postsCount = insertData($pdo, $postTable, $posts);
    $commentsCount = insertData($pdo, $commentsTable, $comments);
    
    echo "Загружено $postsCount записей и $commentsCount комментариев\n";
}

run();