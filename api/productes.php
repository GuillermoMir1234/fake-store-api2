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
}else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['title']) && isset($input['price']) && isset($input['description']) && isset($input['category']) && isset($input['image'])) {
        $stmt = $db->prepare("INSERT INTO productes (title, price, description, category, image, rating.rate, rating.count) VALUES (:title, :price, :description, :category, :image, 0, 0)");
        $stmt->bindValue(':title', $input['title'], SQLITE3_TEXT);
        $stmt->bindValue(':price', $input['price'], SQLITE3_FLOAT);
        $stmt->bindValue(':description', $input['description'], SQLITE3_TEXT);
        $stmt->bindValue(':category', $input['category'], SQLITE3_TEXT);
        $stmt->bindValue(':image', $input['image'], SQLITE3_TEXT);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["success" => "Producte afegit correctament"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al inserir el producte"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falten camps obligatoris"]);
    }

// Peticions PUT
} else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : null;

    if ($id === null || !isset($input['title'], $input['price'], $input['description'], $input['category'], $input['image'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Falten l\'identificador o camps obligatoris']);
    } else {
        $stmt = $db->prepare(
            "UPDATE productes SET title = :title, price = :price, description = :description, category = :category, image = :image WHERE id = :id"
        );
        $stmt->bindValue(':title', $input['title'], SQLITE3_TEXT);
        $stmt->bindValue(':price', $input['price'], SQLITE3_FLOAT);
        $stmt->bindValue(':description', $input['description'], SQLITE3_TEXT);
        $stmt->bindValue(':category', $input['category'], SQLITE3_TEXT);
        $stmt->bindValue(':image', $input['image'], SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Producte actualitzat correctament']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error en l\'actualització del producte']);
        }
    }

// Peticions PATCH
} else if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
    // ...

// Peticions DELETE
} else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

    $id = $_GET['id'] ?? null;

    if ($id === null) {
        parse_str(file_get_contents('php://input'), $params);
        $id = $params['id'] ?? null;
    }

    if ($id !== null) {
        $stmt = $db->prepare("DELETE FROM productes WHERE id = :id");
    }
}
$db->close();

?>