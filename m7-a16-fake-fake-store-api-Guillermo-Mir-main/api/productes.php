<?php
$db = new SQLITE3('../api/fakestoreapi.db');
header('Content-Type: application/json');

$articles = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['categories'])) {
        $resultats = $db->query("SELECT DISTINCT category FROM productes");
        while ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
            $articles[] = $row['category'];
        }
    } elseif (isset($_GET['id'])) {
        $id = $_GET['id'];
        $resultats = $db->query("SELECT * FROM productes WHERE id = $id");
        if ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
            $articles[] = $row;
        }
    } elseif (isset($_GET['categoria'])) {
        $categoria = $_GET['categoria'];
        if ($categoria === 'totes') {
            $resultats = $db->query("SELECT * FROM productes");
        } else {
            $stmt = $db->prepare("SELECT * FROM productes WHERE category = :cat");
            $stmt->bindValue(':cat', $categoria, SQLITE3_TEXT);
            $resultats = $stmt->execute();
        }
        while ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
            $articles[] = $row;
        }
    } else {
        $resultats = $db->query("SELECT * FROM productes ORDER BY id");
        while ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
            $articles[] = $row;
        }
    }

    echo json_encode($articles);
}
$db->close();