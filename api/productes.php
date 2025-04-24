<?php

$db = new SQLITE3('fakestoreapi.db');

$categories = [];

// Mostra totes les categories
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['categoria']) && !isset($_GET['id']) && !isset($_GET['category'])) {
    $resultats = $db->query("SELECT DISTINCT category FROM productes");

    while ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
        $categories[] = $row['category'];
    }

    header('Content-Type: application/json');
    echo json_encode($categories);

// Mostra tots els productes de una o totes les categories
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['category']) || isset($_GET['categoria']))) {
    $productes = [];

    // Accepta tant 'category' com 'categoria'
    $categoria = $_GET['category'] ?? $_GET['categoria'];

    if ($categoria === "totes") {
        $resultats = $db->query("SELECT * FROM productes");
    } else {
        $stmt = $db->prepare("SELECT * FROM productes WHERE category = :category");
        $stmt->bindValue(':category', $categoria, SQLITE3_TEXT);
        $resultats = $stmt->execute();
    }

    while ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
        $row['rating'] = [
            'rate' => $row['rating.rate'],
            'count' => $row['rating.count']
        ];
        unset($row['rating.rate'], $row['rating.count']);
        $productes[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($productes);

// Mostra un producte a partir d'un id
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $resultats = $db->query("SELECT * FROM productes WHERE id = '$id'");
    while ($row = $resultats->fetchArray(SQLITE3_ASSOC)) {
        $row['rating'] = [
            'rate' => $row['rating.rate'],
            'count' => $row['rating.count']
        ];
        unset($row['rating.rate'], $row['rating.count']);
        $producte = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($producte);
}

$db->close();

?>