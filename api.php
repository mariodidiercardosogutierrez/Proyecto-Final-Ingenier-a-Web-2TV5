<?php
// ACTIVAR ERROR REPORTING PARA DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";

// Log para debugging
error_log("API llamada con acción: " . ($_GET['action'] ?? 'none'));

// Obtener acción enviada por fetch()
$action = $_GET['action'] ?? '';

if ($action === "login") {
    login($conn);
}
else if ($action === "getAlbums") {
    getAlbums($conn);
}
else if ($action === "getGeneros") {
    getGeneros($conn);
}
else if ($action === "getArtistas") {
    getArtistas($conn);
}
else if ($action === "getAlbumsByArtist") {
    getAlbumsByArtist($conn);
}
else if ($action === "getCancionesByAlbum") {
    getCancionesByAlbum($conn);
}
else if ($action === "getRating") {
    getRating($conn);
}
else if ($action === "saveRating") {
    saveRating($conn);
}
else if ($action === "checkFavorite") {
    checkFavorite($conn);
}
else if ($action === "toggleFavorite") {
    toggleFavorite($conn);
}
else if ($action === "getUserId") {
    getUserId($conn);
}
else if ($action === "getRatingStats") {
    getRatingStats($conn);
}
// FUNCIONES DEL CARRITO
else if ($action === "getOrCreateCart") {
    getOrCreateCartAction($conn);
}
else if ($action === "addToCart") {
    addToCart($conn);
}
else if ($action === "getCartItems") {
    getCartItems($conn);
}
else if ($action === "removeFromCart") {
    removeFromCart($conn);
}
else if ($action === "updateCartItem") {
    updateCartItem($conn);
}
else if ($action === "clearCart") {
    clearCart($conn);
}
else if ($action === "test") {
    // Acción de prueba
    echo json_encode([
        "success" => true,
        "message" => "API funcionando correctamente",
        "timestamp" => date('Y-m-d H:i:s')
    ]);
}
// ========================
//  OBTENER COMPRAS DEL USUARIO
// ========================
else if ($action === "getUserCompras") {
    getUserCompras($conn);
}

// ========================
//  OBTENER ÁLBUMES POR IDs
// ========================
else if ($action === "getAlbumsByIds") {
    getAlbumsByIds($conn);
}
else if ($action === "getUserCards") {
    getUserCards($conn);
}
else if ($action === "addUserCard") {
    addUserCard($conn);
}
else if ($action === "deleteUserCard") {
    deleteUserCard($conn);
}
// ========================
//  OBTENER BIBLIOTECA DEL USUARIO (ALTERNATIVA)
// ========================
else if ($action === "getUserLibrary") {
    getUserLibrary($conn);
}
else if ($action === "getUserFavorites") {
    getUserFavorites($conn);
}
else if ($action === "getUserPurchases") {
    getUserPurchases($conn);
}
else if ($action === "cancelPurchase") {
    cancelPurchase($conn);
}
// ========================
//  PROCESAR PAGO
// ========================
else if ($action === "processPayment") {
    processPayment($conn);
}
// ========================
//  GENERAR TICKET PDF
// ========================
else if ($action === "generateTicket") {
    generateTicketPDF($conn);
}

else {
    echo json_encode(["success" => false, "message" => "Acción no válida: " . $action]);
}

$conn->close();

// =======================
//     FUNCIÓN LOGIN
// =======================
function login($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos JSON"]);
        return;
    }

    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }

    $email = $conn->real_escape_string($data['email']);
    $password = $data['password'];

    // Buscar usuario
    $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos"]);
        return;
    }

    $user = $result->fetch_assoc();

    // Comparar contraseña normal (sin hash)
    if ($password !== $user['password']) {
        echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos"]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "Login correcto",
        "user" => [
            "id"       => $user["id"],
            "nombre"   => $user["nombre"],
            "apellido" => $user["apellido"],
            "email"    => $user["email"],
            "role"     => $user["role"]
        ]
    ]);
}

// =======================
//  OBTENER ÁLBUMES
// =======================
function getAlbums($conn) {
    $sql = "SELECT al.id_album, al.titulo, al.anio, al.id_artista, al.duracion_total, 
                   al.descripcion_album, al.genero_album, al.cantidadtemas, 
                   al.precio, ar.nombre_artista
            FROM Album al
            JOIN Artista ar ON al.id_artista = ar.id_artista
            ORDER BY ar.nombre_artista, al.anio DESC";

    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }

    $albums = [];
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }

    echo json_encode([
        "success" => true,
        "albums" => $albums
    ]);
}

// =======================
//  OBTENER GÉNEROS
// =======================
function getGeneros($conn) {
    $sql = "SELECT DISTINCT genero_album FROM Album ORDER BY genero_album ASC";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta"]);
        return;
    }

    $generos = [];
    while ($row = $result->fetch_assoc()) {
        $generos[] = $row["genero_album"];
    }

    echo json_encode([
        "success" => true,
        "generos" => $generos
    ]);
}

// =======================
//  OBTENER ARTISTAS
// =======================
function getArtistas($conn) {
    $sql = "SELECT id_artista, nombre_artista, correo 
            FROM Artista 
            ORDER BY nombre_artista ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }

    $artistas = [];
    while ($row = $result->fetch_assoc()) {
        $artistas[] = [
            "id_artista" => $row["id_artista"],
            "nombre_artista" => $row["nombre_artista"],
            "nombre" => $row["nombre_artista"],
            "correo" => $row["correo"]
        ];
    }

    echo json_encode([
        "success" => true,
        "artists" => $artistas
    ]);
}

// =======================
//  OBTENER ÁLBUMES POR ARTISTA
// =======================
function getAlbumsByArtist($conn) {
    $artistId = $_GET['artistId'] ?? '';
    
    $sql = "SELECT al.id_album, al.titulo, al.anio, al.id_artista, al.duracion_total, 
                   al.descripcion_album, al.genero_album, al.cantidadtemas, al.precio,
                   ar.nombre_artista
            FROM Album al
            JOIN Artista ar ON al.id_artista = ar.id_artista";
    
    if (!empty($artistId) && $artistId !== 'all') {
        $sql .= " WHERE al.id_artista = " . intval($artistId);
    }
    
    $sql .= " ORDER BY al.anio DESC";
    
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }

    $albums = [];
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }

    echo json_encode([
        "success" => true,
        "albums" => $albums
    ]);
}

// =======================
//  OBTENER CANCIONES POR ÁLBUM
// =======================
function getCancionesByAlbum($conn) {
    $albumId = $_GET['albumId'] ?? '';
    
    if (empty($albumId)) {
        echo json_encode(["success" => false, "message" => "ID de álbum requerido"]);
        return;
    }
    
    $albumId = intval($albumId);
    
    // Primero obtener información del álbum
    $sqlAlbum = "SELECT al.*, ar.nombre_artista 
                 FROM Album al 
                 JOIN Artista ar ON al.id_artista = ar.id_artista 
                 WHERE al.id_album = $albumId";
    
    $resultAlbum = $conn->query($sqlAlbum);
    
    if (!$resultAlbum || $resultAlbum->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Álbum no encontrado"]);
        return;
    }
    
    $albumData = $resultAlbum->fetch_assoc();
    
    // Obtener canciones REALES de la base de datos (tabla Cancion)
    $sqlCanciones = "SELECT c.id_cancion, c.titulo, c.duracion, c.anio, 
                            c.precio, c.genero, c.id_artista, c.id_album,
                            ar.nombre_artista
                     FROM Cancion c
                     JOIN Artista ar ON c.id_artista = ar.id_artista
                     WHERE c.id_album = $albumId 
                     ORDER BY c.id_cancion ASC";
    
    $resultCanciones = $conn->query($sqlCanciones);
    
    $canciones = [];
    if ($resultCanciones && $resultCanciones->num_rows > 0) {
        while ($row = $resultCanciones->fetch_assoc()) {
            $duracion = $row['duracion'];
            if (strpos($duracion, ':') === false && is_numeric($duracion)) {
                $minutos = floor($duracion / 60);
                $segundos = $duracion % 60;
                $duracion = sprintf("%d:%02d", $minutos, $segundos);
            }
            
            $canciones[] = [
                "id_cancion" => $row['id_cancion'],
                "titulo" => $row['titulo'],
                "duracion" => $duracion,
                "anio" => $row['anio'],
                "precio" => $row['precio'],
                "genero" => $row['genero'],
                "nombre_artista" => $row['nombre_artista'],
                "numero_track" => count($canciones) + 1
            ];
        }
    }
    
    if (empty($canciones)) {
        echo json_encode([
            "success" => true,
            "album" => $albumData,
            "canciones" => [],
            "message" => "Este álbum no tiene canciones registradas"
        ]);
        return;
    }
    
    echo json_encode([
        "success" => true,
        "album" => $albumData,
        "canciones" => $canciones
    ]);
}

// =======================
//  OBTENER CALIFICACIÓN DEL USUARIO
// =======================
function getRating($conn) {
    if (!isset($_GET['userId']) || !isset($_GET['albumId'])) {
        echo json_encode([
            "success" => false, 
            "calificacion" => 0,
            "message" => "Datos incompletos"
        ]);
        return;
    }
    
    $userId = intval($_GET['userId']);
    $albumId = intval($_GET['albumId']);
    
    if ($userId <= 0 || $albumId <= 0) {
        echo json_encode([
            "success" => false,
            "calificacion" => 0,
            "message" => "IDs inválidos"
        ]);
        return;
    }
    
    $sql = "SELECT calificacion FROM AlbumCalificacion 
            WHERE id_usuario = $userId AND id_album = $albumId 
            LIMIT 1";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $calificacion = intval($row['calificacion']);
        
        if ($calificacion < 1 || $calificacion > 5) {
            $calificacion = 0;
        }
        
        echo json_encode([
            "success" => true,
            "calificacion" => $calificacion,
            "message" => "Calificación encontrada"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "calificacion" => 0,
            "message" => "No hay calificación"
        ]);
    }
}

// =======================
//  GUARDAR/ACTUALIZAR CALIFICACIÓN
// =======================
function saveRating($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['userId']) || !isset($data['albumId']) || !isset($data['rating'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $albumId = intval($data['albumId']);
    $rating = intval($data['rating']);
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(["success" => false, "message" => "Calificación inválida"]);
        return;
    }
    
    $checkSql = "SELECT id_album_calificacion FROM AlbumCalificacion 
                 WHERE id_usuario = $userId AND id_album = $albumId";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $sql = "UPDATE AlbumCalificacion 
                SET calificacion = $rating, fecha_calificacion = NOW() 
                WHERE id_usuario = $userId AND id_album = $albumId";
    } else {
        $sql = "INSERT INTO AlbumCalificacion (id_usuario, id_album, calificacion) 
                VALUES ($userId, $albumId, $rating)";
    }
    
    if ($conn->query($sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Calificación guardada exitosamente"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error al guardar calificación: " . $conn->error
        ]);
    }
}

// =======================
//  VERIFICAR FAVORITO
// =======================
function checkFavorite($conn) {
    if (!isset($_GET['userId']) || !isset($_GET['albumId'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($_GET['userId']);
    $albumId = intval($_GET['albumId']);
    
    $sql = "SELECT id_album_favorito FROM AlbumFavorito 
            WHERE id_usuario = $userId AND id_album = $albumId 
            LIMIT 1";
    
    $result = $conn->query($sql);
    
    echo json_encode([
        "success" => true,
        "isFavorite" => ($result && $result->num_rows > 0)
    ]);
}

// =======================
//  TOGGLE FAVORITO
// =======================
function toggleFavorite($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['userId']) || !isset($data['albumId'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $albumId = intval($data['albumId']);
    
    $checkSql = "SELECT id_album_favorito FROM AlbumFavorito 
                 WHERE id_usuario = $userId AND id_album = $albumId 
                 LIMIT 1";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $sql = "DELETE FROM AlbumFavorito 
                WHERE id_usuario = $userId AND id_album = $albumId";
        $message = "Álbum eliminado de favoritos";
    } else {
        $sql = "INSERT INTO AlbumFavorito (id_usuario, id_album) 
                VALUES ($userId, $albumId)";
        $message = "Álbum agregado a favoritos";
    }
    
    if ($conn->query($sql)) {
        echo json_encode([
            "success" => true,
            "message" => $message,
            "isFavorite" => ($checkResult && $checkResult->num_rows > 0) ? false : true
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $conn->error
        ]);
    }
}

// =======================
//  OBTENER ID DE USUARIO
// =======================
function getUserId($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['email'])) {
        echo json_encode(["success" => false, "message" => "Email requerido"]);
        return;
    }
    
    $email = $conn->real_escape_string($data['email']);
    
    $sql = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "userId" => $row['id'],
            "message" => "Usuario encontrado"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado"
        ]);
    }
}

// =======================
//  OBTENER ESTADÍSTICAS DE CALIFICACIÓN
// =======================
function getRatingStats($conn) {
    $albumId = $_GET['albumId'] ?? '';
    
    if (empty($albumId)) {
        echo json_encode(["success" => false, "message" => "ID de álbum requerido"]);
        return;
    }
    
    $albumId = intval($albumId);
    
    $sql = "SELECT COUNT(*) as total, AVG(calificacion) as promedio 
            FROM AlbumCalificacion 
            WHERE id_album = $albumId";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "count" => $row['total'],
            "average" => $row['promedio']
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "count" => 0,
            "average" => 0
        ]);
    }
}

// ========================
//  FUNCIONES DEL CARRITO
// ========================

// Función auxiliar para crear/obtener carrito
function getOrCreateCartInternal($conn, $userId) {
    $sql = "SELECT id_carrito FROM Carrito WHERE id_usuario = $userId";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return ["success" => true, "cartId" => $row['id_carrito']];
    } else {
        $sql = "INSERT INTO Carrito (id_usuario) VALUES ($userId)";
        if ($conn->query($sql)) {
            return ["success" => true, "cartId" => $conn->insert_id];
        } else {
            return ["success" => false, "message" => "Error creando carrito: " . $conn->error];
        }
    }
}

// Acción para obtener o crear carrito
function getOrCreateCartAction($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['userId'])) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $result = getOrCreateCartInternal($conn, $userId);
    
    echo json_encode($result);
}

// ========================
//  AGREGAR AL CARRITO
// ========================
function addToCart($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    $required = ['userId', 'tipo', 'id_producto', 'precio'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            echo json_encode(["success" => false, "message" => "Campo $field requerido"]);
            return;
        }
    }
    
    $userId = intval($data['userId']);
    $tipo = $conn->real_escape_string($data['tipo']);
    $id_producto = intval($data['id_producto']);
    $precio = floatval($data['precio']);
    $cantidad = isset($data['cantidad']) ? intval($data['cantidad']) : 1;
    
    if ($tipo !== 'album' && $tipo !== 'cancion') {
        echo json_encode(["success" => false, "message" => "Tipo inválido"]);
        return;
    }
    
    // Obtener o crear carrito
    $cartResult = getOrCreateCartInternal($conn, $userId);
    if (!$cartResult['success']) {
        echo json_encode($cartResult);
        return;
    }
    
    $cartId = $cartResult['cartId'];
    
    // Verificar si YA EXISTE en el carrito
    $checkSql = "SELECT id_carritoItem, cantidad FROM CarritoItem 
                 WHERE id_carrito = $cartId 
                 AND tipo = '$tipo' 
                 AND id_producto = $id_producto";
    
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        // Si ya existe, AUMENTAR la cantidad
        $row = $checkResult->fetch_assoc();
        $newQuantity = intval($row['cantidad']) + $cantidad;
        $itemId = $row['id_carritoItem'];
        
        $updateSql = "UPDATE CarritoItem 
                      SET cantidad = $newQuantity 
                      WHERE id_carritoItem = $itemId";
        
        if ($conn->query($updateSql)) {
            echo json_encode([
                "success" => true,
                "message" => "Cantidad actualizada en el carrito",
                "action" => "updated",
                "quantity" => $newQuantity
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Error actualizando: " . $conn->error]);
        }
    } else {
        // Si no existe, agregar nuevo
        $insertSql = "INSERT INTO CarritoItem (id_carrito, tipo, id_producto, cantidad, precio) 
                      VALUES ($cartId, '$tipo', $id_producto, $cantidad, $precio)";
        
        if ($conn->query($insertSql)) {
            echo json_encode([
                "success" => true,
                "message" => "Producto agregado al carrito",
                "action" => "added"
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Error agregando: " . $conn->error]);
        }
    }
}

// ========================
//  OBTENER ITEMS DEL CARRITO
// ========================
function getCartItems($conn) {
    $userId = $_GET['userId'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($userId);
    
    // Obtener carrito del usuario
    $cartSql = "SELECT id_carrito FROM Carrito WHERE id_usuario = $userId";
    $cartResult = $conn->query($cartSql);
    
    if (!$cartResult || $cartResult->num_rows === 0) {
        echo json_encode(["success" => true, "items" => [], "total" => 0, "count" => 0]);
        return;
    }
    
    $cart = $cartResult->fetch_assoc();
    $cartId = $cart['id_carrito'];
    
    // Obtener items del carrito con información detallada
    $sql = "SELECT ci.*, 
                   CASE 
                       WHEN ci.tipo = 'album' THEN al.titulo
                       WHEN ci.tipo = 'cancion' THEN ca.titulo
                       ELSE 'Producto desconocido'
                   END as nombre_producto,
                   COALESCE(arta.nombre_artista, arta2.nombre_artista, 'Artista desconocido') as artista
            FROM CarritoItem ci
            LEFT JOIN Album al ON ci.tipo = 'album' AND ci.id_producto = al.id_album
            LEFT JOIN Cancion ca ON ci.tipo = 'cancion' AND ci.id_producto = ca.id_cancion
            LEFT JOIN Artista arta ON al.id_artista = arta.id_artista
            LEFT JOIN Artista arta2 ON ca.id_artista = arta2.id_artista
            WHERE ci.id_carrito = $cartId
            ORDER BY ci.id_carritoItem DESC";
    
    $result = $conn->query($sql);
    
    $items = [];
    $total = 0;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $precio = floatval($row['precio']);
            $cantidad = intval($row['cantidad']);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            
            $items[] = [
                "id" => intval($row['id_carritoItem']),
                "tipo" => $row['tipo'],
                "id_producto" => intval($row['id_producto']),
                "nombre" => $row['nombre_producto'],
                "artista" => $row['artista'],
                "cantidad" => $cantidad,
                "precio_unitario" => $precio,
                "subtotal" => $subtotal,
                "fecha_agregado" => $row['fecha_agregado'] ?? date('Y-m-d H:i:s')
            ];
        }
    }
    
    echo json_encode([
        "success" => true,
        "cartId" => $cartId,
        "items" => $items,
        "total" => $total,
        "count" => count($items)
    ]);
}

// ========================
//  ELIMINAR DEL CARRITO
// ========================
function removeFromCart($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['itemId'])) {
        echo json_encode(["success" => false, "message" => "ID de item requerido"]);
        return;
    }
    
    $itemId = intval($data['itemId']);
    
    $sql = "DELETE FROM CarritoItem WHERE id_carritoItem = $itemId";
    
    if ($conn->query($sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Producto eliminado del carrito"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error eliminando: " . $conn->error]);
    }
}

// ========================
//  ACTUALIZAR CANTIDAD
// ========================
function updateCartItem($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['itemId']) || !isset($data['cantidad'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $itemId = intval($data['itemId']);
    $cantidad = intval($data['cantidad']);
    
    if ($cantidad < 1) {
        // Si la cantidad es 0, eliminar el item
        $sql = "DELETE FROM CarritoItem WHERE id_carritoItem = $itemId";
        if ($conn->query($sql)) {
            echo json_encode([
                "success" => true,
                "message" => "Producto eliminado del carrito"
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Error eliminando: " . $conn->error]);
        }
        return;
    }
    
    $sql = "UPDATE CarritoItem SET cantidad = $cantidad WHERE id_carritoItem = $itemId";
    
    if ($conn->query($sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Cantidad actualizada",
            "quantity" => $cantidad
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error actualizando: " . $conn->error]);
    }
}

// ========================
//  VACIAR CARRITO
// ========================
function clearCart($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['cartId'])) {
        echo json_encode(["success" => false, "message" => "ID de carrito requerido"]);
        return;
    }
    
    $cartId = intval($data['cartId']);
    
    $sql = "DELETE FROM CarritoItem WHERE id_carrito = $cartId";
    
    if ($conn->query($sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Carrito vaciado correctamente"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error vaciando carrito: " . $conn->error]);
    }
}

// OBTENER COMPRAS DEL USUARIO
function getUserCompras($conn) {
    $userId = $_GET['userId'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($userId);
    
    // Primero obtener las compras del usuario
    $sqlCompras = "SELECT c.id_compra, c.fecha, c.total, ec.estatus, t.numero_tarjeta
                   FROM Compra c
                   JOIN Estatus_Compra ec ON c.id_estatus = ec.id_estatus
                   JOIN Tarjeta t ON c.id_tarjeta = t.id_tarjeta
                   WHERE c.id_usuario = $userId
                   ORDER BY c.fecha DESC";
    
    $resultCompras = $conn->query($sqlCompras);
    
    if (!$resultCompras) {
        echo json_encode(["success" => false, "message" => "Error en consulta de compras: " . $conn->error]);
        return;
    }
    
    $compras = [];
    
    if ($resultCompras->num_rows > 0) {
        while ($compra = $resultCompras->fetch_assoc()) {
            $compraId = intval($compra['id_compra']);
            
            // Obtener items de esta compra
            $sqlItems = "SELECT ci.tipo, ci.id_producto, ci.cantidad, ci.precio
                        FROM CompraItem ci
                        WHERE ci.id_compra = $compraId
                        ORDER BY ci.id_compraItem";
            
            $resultItems = $conn->query($sqlItems);
            $items = [];
            
            if ($resultItems && $resultItems->num_rows > 0) {
                while ($item = $resultItems->fetch_assoc()) {
                    $items[] = [
                        "tipo" => $item['tipo'],
                        "id_producto" => intval($item['id_producto']),
                        "cantidad" => intval($item['cantidad']),
                        "precio" => floatval($item['precio']),
                        "subtotal" => floatval($item['precio']) * intval($item['cantidad'])
                    ];
                }
            }
            
            // Formatear número de tarjeta para mostrar solo los últimos 4 dígitos
            $tarjeta = $compra['numero_tarjeta'];
            if (strlen($tarjeta) >= 4) {
                $tarjeta = "**** **** **** " . substr($tarjeta, -4);
            }
            
            $compras[] = [
                "id_compra" => $compraId,
                "fecha" => $compra['fecha'],
                "total" => floatval($compra['total']),
                "estatus" => $compra['estatus'],
                "tarjeta" => $tarjeta,
                "items" => $items
            ];
        }
    }
    
    echo json_encode([
        "success" => true,
        "compras" => $compras,
        "count" => count($compras)
    ]);
}

// OBTENER ÁLBUMES POR IDs
function getAlbumsByIds($conn) {
    $albumIdsParam = $_GET['albumIds'] ?? '';
    
    if (empty($albumIdsParam)) {
        echo json_encode(["success" => false, "message" => "IDs de álbumes requeridos"]);
        return;
    }
    
    // Convertir string de IDs a array
    $albumIds = explode(',', $albumIdsParam);
    
    if (empty($albumIds)) {
        echo json_encode(["success" => true, "albums" => []]);
        return;
    }
    
    // Filtrar y validar IDs
    $validIds = [];
    foreach ($albumIds as $id) {
        $id = intval(trim($id));
        if ($id > 0) {
            $validIds[] = $id;
        }
    }
    
    if (empty($validIds)) {
        echo json_encode(["success" => true, "albums" => []]);
        return;
    }
    
    $idsString = implode(',', $validIds);
    
    // Obtener información de los álbumes
    $sql = "SELECT al.id_album, al.titulo, al.anio, al.id_artista, al.duracion_total, 
                   al.descripcion_album, al.genero_album, al.cantidadtemas, 
                   al.precio, ar.nombre_artista
            FROM Album al
            JOIN Artista ar ON al.id_artista = ar.id_artista
            WHERE al.id_album IN ($idsString)
            ORDER BY FIELD(al.id_album, $idsString)";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }
    
    $albums = [];
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "albums" => $albums
    ]);
}


// FUNCIÓN ALTERNATIVA PARA BIBLIOTECA
function getUserLibrary($conn) {
    $userId = $_GET['userId'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($userId);
    
    // OPCIÓN 1: Buscar en CompraItem (si existen compras)
    $sql = "SELECT DISTINCT ci.id_producto 
            FROM CompraItem ci
            JOIN Compra c ON ci.id_compra = c.id_compra
            WHERE c.id_usuario = $userId AND ci.tipo = 'album'";
    
    $result = $conn->query($sql);
    
    $albumIds = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $albumIds[] = intval($row['id_producto']);
        }
    }
    
    // OPCIÓN 2: Si no hay compras, buscar en CarritoItem (para testing)
    if (empty($albumIds)) {
        $sqlCart = "SELECT DISTINCT ci.id_producto 
                   FROM CarritoItem ci
                   JOIN Carrito c ON ci.id_carrito = c.id_carrito
                   WHERE c.id_usuario = $userId AND ci.tipo = 'album'";
        
        $resultCart = $conn->query($sqlCart);
        
        if ($resultCart && $resultCart->num_rows > 0) {
            while ($row = $resultCart->fetch_assoc()) {
                $albumIds[] = intval($row['id_producto']);
            }
        }
    }
    
    // Si aún no hay álbumes, mostrar algunos álbumes de ejemplo
    if (empty($albumIds)) {
        echo json_encode([
            "success" => true,
            "albums" => [],
            "message" => "No hay álbumes en tu biblioteca"
        ]);
        return;
    }
    
    $idsString = implode(',', $albumIds);
    
    // Obtener información de los álbumes
    $sqlAlbums = "SELECT al.id_album, al.titulo, al.anio, al.id_artista, al.duracion_total, 
                         al.descripcion_album, al.genero_album, al.cantidadtemas, 
                         al.precio, ar.nombre_artista
                  FROM Album al
                  JOIN Artista ar ON al.id_artista = ar.id_artista
                  WHERE al.id_album IN ($idsString)
                  ORDER BY al.titulo";
    
    $resultAlbums = $conn->query($sqlAlbums);
    
    $albums = [];
    if ($resultAlbums && $resultAlbums->num_rows > 0) {
        while ($row = $resultAlbums->fetch_assoc()) {
            $albums[] = $row;
        }
    }
    
    echo json_encode([
        "success" => true,
        "albums" => $albums,
        "count" => count($albums)
    ]);
}
// Agrega esto después de las funciones existentes en api.php, antes del cierre $conn->close();

// FUNCIÓN PARA OBTENER FAVORITOS
function getUserFavorites($conn) {
    $userId = $_GET['userId'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($userId);
    
    // Obtener IDs de álbumes favoritos
    $sql = "SELECT id_album, fecha_registro 
            FROM AlbumFavorito 
            WHERE id_usuario = $userId 
            ORDER BY fecha_registro DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }
    
    $albumIds = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $albumIds[] = intval($row['id_album']);
        }
    }
    
    echo json_encode([
        "success" => true,
        "albumIds" => $albumIds,
        "count" => count($albumIds)
    ]);
}



// Agrega esto después de las funciones existentes en api.php, antes del cierre $conn->close();

// FUNCIÓN PARA OBTENER TARJETAS DEL USUARIO
function getUserCards($conn) {
    $userId = $_GET['userId'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($userId);
    
    // Obtener tarjetas del usuario (a través de Usuario_Tarjeta)
    $sql = "SELECT t.*, ut.fecha_registro 
            FROM Tarjeta t
            JOIN Usuario_Tarjeta ut ON t.id_tarjeta = ut.id_tarjeta
            WHERE ut.id_usuario = $userId 
            ORDER BY ut.fecha_registro DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }
    
    $cards = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cards[] = [
                "id_tarjeta" => intval($row['id_tarjeta']),
                "nombre_titular" => $row['nombre_titular'],
                "numero_tarjeta" => $row['numero_tarjeta'],
                "mes_exp" => $row['mes_exp'],
                "anio_exp" => $row['anio_exp'],
                "cvv" => $row['cvv'],
                "fecha_registro" => $row['fecha_registro']
            ];
        }
    }
    
    echo json_encode([
        "success" => true,
        "cards" => $cards,
        "count" => count($cards)
    ]);
}

// FUNCIÓN PARA AGREGAR TARJETA DE USUARIO
// FUNCIÓN PARA AGREGAR TARJETA DE USUARIO
// FUNCIÓN PARA AGREGAR TARJETA DE USUARIO
function addUserCard($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    $required = ['userId', 'nombre_titular', 'numero_tarjeta', 'mes_exp', 'anio_exp', 'cvv'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            echo json_encode(["success" => false, "message" => "Campo $field requerido"]);
            return;
        }
    }
    
    $userId = intval($data['userId']);
    $nombre_titular = $conn->real_escape_string($data['nombre_titular']);
    $numero_tarjeta = $conn->real_escape_string($data['numero_tarjeta']);
    $mes_exp = $conn->real_escape_string($data['mes_exp']);
    $anio_exp = $conn->real_escape_string($data['anio_exp']);
    $cvv = $conn->real_escape_string($data['cvv']);
    
    // VALIDACIÓN: Verificar que el usuario existe
    $checkUserSql = "SELECT id FROM usuarios WHERE id = $userId LIMIT 1";
    $userResult = $conn->query($checkUserSql);
    
    if (!$userResult || $userResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Usuario no válido. Por favor inicia sesión nuevamente."]);
        return;
    }
    
    // VALIDACIÓN: Verificar longitud del número de tarjeta (13-19 dígitos)
    $cleanNumber = preg_replace('/\D/', '', $numero_tarjeta);
    if (strlen($cleanNumber) < 13 || strlen($cleanNumber) > 19) {
        echo json_encode(["success" => false, "message" => "Número de tarjeta inválido (debe tener 13-19 dígitos)"]);
        return;
    }
    
    // VALIDACIÓN: Verificar fecha de expiración
    $currentYear = date('Y');
    $currentMonth = date('m');
    if ($anio_exp < $currentYear || ($anio_exp == $currentYear && $mes_exp < $currentMonth)) {
        echo json_encode(["success" => false, "message" => "La tarjeta está expirada"]);
        return;
    }
    
    // VALIDACIÓN: Verificar CVV (3-4 dígitos)
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        echo json_encode(["success" => false, "message" => "CVV inválido (debe tener 3 o 4 dígitos)"]);
        return;
    }
    
    // Usar transacción para asegurar consistencia
    $conn->begin_transaction();
    
    try {
        // Verificar si la tarjeta ya existe en la base de datos general
        $checkCardSql = "SELECT id_tarjeta FROM Tarjeta 
                         WHERE numero_tarjeta = '$numero_tarjeta' 
                         AND mes_exp = '$mes_exp' 
                         AND anio_exp = '$anio_exp' 
                         AND cvv = '$cvv' 
                         LIMIT 1";
        
        $checkResult = $conn->query($checkCardSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            // La tarjeta ya existe, obtener su ID
            $cardRow = $checkResult->fetch_assoc();
            $cardId = $cardRow['id_tarjeta'];
        } else {
            // La tarjeta no existe, insertarla en Tarjeta
            $insertCardSql = "INSERT INTO Tarjeta (nombre_titular, numero_tarjeta, mes_exp, anio_exp, cvv) 
                              VALUES ('$nombre_titular', '$numero_tarjeta', '$mes_exp', '$anio_exp', '$cvv')";
            
            if (!$conn->query($insertCardSql)) {
                // ERROR ESPECÍFICO: Puede ser por duplicados o restricciones
                if ($conn->errno == 1062) { // Código de error para duplicados
                    throw new Exception("Esta tarjeta ya existe en el sistema");
                }
                throw new Exception("Error al crear tarjeta: " . $conn->error);
            }
            
            $cardId = $conn->insert_id;
        }
        
        // Verificar si el usuario ya tiene asociada esta tarjeta
        $checkUserCardSql = "SELECT id_usuario_tarjeta FROM Usuario_Tarjeta 
                             WHERE id_usuario = $userId AND id_tarjeta = $cardId 
                             LIMIT 1";
        
        $checkUserResult = $conn->query($checkUserCardSql);
        
        if ($checkUserResult && $checkUserResult->num_rows > 0) {
            throw new Exception("Esta tarjeta ya está asociada a tu cuenta");
        }
        
        // Asociar tarjeta al usuario
        $insertUserCardSql = "INSERT INTO Usuario_Tarjeta (id_usuario, id_tarjeta) 
                              VALUES ($userId, $cardId)";
        
        if (!$conn->query($insertUserCardSql)) {
            throw new Exception("Error al asociar tarjeta: " . $conn->error);
        }
        
        // Confirmar transacción
        $conn->commit();
        
        echo json_encode([
            "success" => true,
            "message" => "Tarjeta agregada correctamente",
            "cardId" => $cardId
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

// FUNCIÓN PARA ELIMINAR TARJETA DE USUARIO
function deleteUserCard($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['userId']) || !isset($data['cardId'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $cardId = intval($data['cardId']);
    
    // Verificar que el usuario sea el dueño de la tarjeta
    $checkSql = "SELECT id_usuario_tarjeta FROM Usuario_Tarjeta 
                 WHERE id_usuario = $userId AND id_tarjeta = $cardId 
                 LIMIT 1";
    
    $checkResult = $conn->query($checkSql);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Tarjeta no encontrada o no pertenece al usuario"]);
        return;
    }
    
    // Eliminar la relación usuario-tarjeta
    $deleteSql = "DELETE FROM Usuario_Tarjeta 
                  WHERE id_usuario = $userId AND id_tarjeta = $cardId";
    
    if ($conn->query($deleteSql)) {
        echo json_encode([
            "success" => true,
            "message" => "Tarjeta eliminada correctamente"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar tarjeta: " . $conn->error]);
    }
}


// Agrega esto después de las funciones existentes en api.php, antes del cierre $conn->close();


// FUNCIÓN PARA OBTENER COMPRAS DEL USUARIO
function getUserPurchases($conn) {
    $userId = $_GET['userId'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Usuario no especificado"]);
        return;
    }
    
    $userId = intval($userId);
    
    // Obtener compras del usuario
    $sql = "SELECT c.*, ec.estatus, t.numero_tarjeta
            FROM Compra c
            JOIN Estatus_Compra ec ON c.id_estatus = ec.id_estatus
            JOIN Tarjeta t ON c.id_tarjeta = t.id_tarjeta
            WHERE c.id_usuario = $userId 
            ORDER BY c.fecha DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $conn->error]);
        return;
    }
    
    $purchases = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $purchaseId = intval($row['id_compra']);
            
            // Obtener items de esta compra
            $sqlItems = "SELECT ci.*, 
                                CASE 
                                    WHEN ci.tipo = 'album' THEN al.titulo
                                    WHEN ci.tipo = 'cancion' THEN ca.titulo
                                    ELSE 'Producto desconocido'
                                END as nombre_producto,
                                COALESCE(arta.nombre_artista, arta2.nombre_artista, 'Artista desconocido') as artista
                         FROM CompraItem ci
                         LEFT JOIN Album al ON ci.tipo = 'album' AND ci.id_producto = al.id_album
                         LEFT JOIN Cancion ca ON ci.tipo = 'cancion' AND ci.id_producto = ca.id_cancion
                         LEFT JOIN Artista arta ON al.id_artista = arta.id_artista
                         LEFT JOIN Artista arta2 ON ca.id_artista = arta2.id_artista
                         WHERE ci.id_compra = $purchaseId
                         ORDER BY ci.id_compraItem";
            
            $resultItems = $conn->query($sqlItems);
            $items = [];
            
            if ($resultItems && $resultItems->num_rows > 0) {
                while ($item = $resultItems->fetch_assoc()) {
                    $items[] = [
                        "tipo" => $item['tipo'],
                        "id_producto" => intval($item['id_producto']),
                        "nombre_producto" => $item['nombre_producto'],
                        "artista" => $item['artista'],
                        "cantidad" => intval($item['cantidad']),
                        "precio" => floatval($item['precio']),
                        "subtotal" => floatval($item['precio']) * intval($item['cantidad'])
                    ];
                }
            }
            
            // Formatear número de tarjeta
            $tarjeta = $row['numero_tarjeta'];
            if (strlen($tarjeta) >= 4) {
                $tarjeta = "**** **** **** " . substr($tarjeta, -4);
            }
            
            $purchases[] = [
                "id_compra" => $purchaseId,
                "fecha" => $row['fecha'],
                "total" => floatval($row['total']),
                "estatus" => $row['estatus'],
                "tarjeta" => $tarjeta,
                "items" => $items
            ];
        }
    }
    
    echo json_encode([
        "success" => true,
        "purchases" => $purchases,
        "count" => count($purchases)
    ]);
}

// FUNCIÓN PARA CANCELAR COMPRA
function cancelPurchase($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['userId']) || !isset($data['purchaseId'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $purchaseId = intval($data['purchaseId']);
    
    // Verificar que la compra pertenece al usuario y está pendiente
    $checkSql = "SELECT c.id_compra, ec.estatus 
                 FROM Compra c
                 JOIN Estatus_Compra ec ON c.id_estatus = ec.id_estatus
                 WHERE c.id_compra = $purchaseId 
                 AND c.id_usuario = $userId 
                 AND ec.estatus = 'Pendiente'";
    
    $checkResult = $conn->query($checkSql);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Compra no encontrada, no te pertenece o ya no está pendiente"]);
        return;
    }
    
    // Obtener ID del estatus "Cancelado"
    $statusSql = "SELECT id_estatus FROM Estatus_Compra WHERE estatus = 'Cancelado' LIMIT 1";
    $statusResult = $conn->query($statusSql);
    
    if (!$statusResult || $statusResult->num_rows === 0) {
        // Si no existe el estatus, crearlo
        $conn->query("INSERT INTO Estatus_Compra (estatus) VALUES ('Cancelado')");
        $cancelStatusId = $conn->insert_id;
    } else {
        $statusRow = $statusResult->fetch_assoc();
        $cancelStatusId = $statusRow['id_estatus'];
    }
    
    // Actualizar el estatus de la compra
    $updateSql = "UPDATE Compra SET id_estatus = $cancelStatusId WHERE id_compra = $purchaseId";
    
    if ($conn->query($updateSql)) {
        echo json_encode([
            "success" => true,
            "message" => "Compra cancelada correctamente"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al cancelar compra: " . $conn->error]);
    }
}

// ========================
//  FUNCIÓN PARA PROCESAR PAGO
// ========================
// ========================
//  PROCESAR PAGO
// ========================
function processPayment($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    if (!isset($data['userId']) || !isset($data['cardId'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $cardId = intval($data['cardId']);
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // 1. Obtener carrito del usuario
        $cartSql = "SELECT id_carrito FROM Carrito WHERE id_usuario = $userId";
        $cartResult = $conn->query($cartSql);
        
        if (!$cartResult || $cartResult->num_rows === 0) {
            throw new Exception("Carrito vacío");
        }
        
        $cart = $cartResult->fetch_assoc();
        $cartId = $cart['id_carrito'];
        
        // 2. Obtener items del carrito
        $itemsSql = "SELECT * FROM CarritoItem WHERE id_carrito = $cartId";
        $itemsResult = $conn->query($itemsSql);
        
        if (!$itemsResult || $itemsResult->num_rows === 0) {
            throw new Exception("No hay productos en el carrito");
        }
        
        $items = [];
        $total = 0;
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
            $subtotal = floatval($item['precio']) * intval($item['cantidad']);
            $total += $subtotal;
        }
        
        // 3. Verificar que la tarjeta pertenece al usuario
        $cardSql = "SELECT ut.id_usuario_tarjeta 
                    FROM Usuario_Tarjeta ut 
                    WHERE ut.id_usuario = $userId AND ut.id_tarjeta = $cardId";
        $cardResult = $conn->query($cardSql);
        
        if (!$cardResult || $cardResult->num_rows === 0) {
            throw new Exception("Tarjeta no válida o no pertenece al usuario");
        }
        
        // 4. Obtener o crear estatus "Completado"
        $statusSql = "SELECT id_estatus FROM Estatus_Compra WHERE estatus = 'Completado' LIMIT 1";
        $statusResult = $conn->query($statusSql);
        
        if (!$statusResult || $statusResult->num_rows === 0) {
            // Crear estatus si no existe
            $conn->query("INSERT INTO Estatus_Compra (estatus) VALUES ('Completado')");
            $statusId = $conn->insert_id;
        } else {
            $statusRow = $statusResult->fetch_assoc();
            $statusId = $statusRow['id_estatus'];
        }
        
        // 5. Crear registro de compra
        $compraSql = "INSERT INTO Compra (id_usuario, fecha, total, id_tarjeta, id_estatus) 
                      VALUES ($userId, NOW(), $total, $cardId, $statusId)";
        
        if (!$conn->query($compraSql)) {
            throw new Exception("Error al crear compra: " . $conn->error);
        }
        
        $compraId = $conn->insert_id;
        
        // 6. Mover items del carrito a CompraItem
        foreach ($items as $item) {
            $compraItemSql = "INSERT INTO CompraItem (id_compra, tipo, id_producto, cantidad, precio) 
                              VALUES ($compraId, '{$item['tipo']}', {$item['id_producto']}, 
                                      {$item['cantidad']}, {$item['precio']})";
            
            if (!$conn->query($compraItemSql)) {
                throw new Exception("Error al crear item de compra: " . $conn->error);
            }
        }
        
        // 7. Vaciar carrito
        $clearCartSql = "DELETE FROM CarritoItem WHERE id_carrito = $cartId";
        if (!$conn->query($clearCartSql)) {
            throw new Exception("Error al vaciar carrito: " . $conn->error);
        }
        
        // 8. Confirmar transacción
        $conn->commit();
        
        // 9. Devolver respuesta exitosa
        echo json_encode([
            "success" => true,
            "message" => "Pago procesado exitosamente",
            "compraId" => $compraId,
            "total" => $total,
            "ticket_url" => null // Puedes agregar URL del ticket si lo generas
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
// ========================
//  GENERAR DATOS DEL TICKET
// ========================
function generateTicketData($conn, $compraId, $userId, $total, $items) {
    // Obtener información del usuario
    $userSql = "SELECT nombre, apellido, email FROM usuarios WHERE id = $userId";
    $userResult = $conn->query($userSql);
    $user = $userResult->fetch_assoc();
    
    // Obtener información de productos
    $productos = [];
    foreach ($items as $item) {
        if ($item['tipo'] === 'album') {
            $prodSql = "SELECT a.titulo, ar.nombre_artista 
                       FROM Album a 
                       JOIN Artista ar ON a.id_artista = ar.id_artista 
                       WHERE a.id_album = {$item['id_producto']}";
        } else {
            $prodSql = "SELECT c.titulo, ar.nombre_artista 
                       FROM Cancion c 
                       JOIN Artista ar ON c.id_artista = ar.id_artista 
                       WHERE c.id_cancion = {$item['id_producto']}";
        }
        
        $prodResult = $conn->query($prodSql);
        if ($prodResult && $prodResult->num_rows > 0) {
            $prod = $prodResult->fetch_assoc();
            $productos[] = [
                'tipo' => $item['tipo'],
                'nombre' => $prod['titulo'],
                'artista' => $prod['nombre_artista'],
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'subtotal' => $item['cantidad'] * $item['precio']
            ];
        }
    }
    
    // Información de la empresa
    $empresa = [
        'nombre' => 'SoundSpace',
        'direccion' => 'Av. Música 123, Ciudad Digital',
        'telefono' => '(55) 1234-5678',
        'email' => 'ventas@soundspace.com',
        'sitio_web' => 'www.soundspace.com'
    ];
    
    return [
        'compra_id' => $compraId,
        'fecha' => date('d/m/Y H:i:s'),
        'usuario' => [
            'nombre' => $user['nombre'] . ' ' . $user['apellido'],
            'email' => $user['email']
        ],
        'empresa' => $empresa,
        'productos' => $productos,
        'total' => $total,
        'iva' => $total * 0.16, // Ejemplo: 16% de IVA
        'total_con_iva' => $total * 1.16
    ];
}

// ========================
//  GENERAR PDF DEL TICKET
// ========================
function generatePDF($ticketData) {
    require_once 'vendor/autoload.php'; // Requiere composer y TCPDF
    
    try {
        // Crear nuevo PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurar documento
        $pdf->SetCreator('SoundSpace');
        $pdf->SetAuthor('SoundSpace');
        $pdf->SetTitle('Ticket de Compra #' . $ticketData['compra_id']);
        $pdf->SetSubject('Ticket de Compra');
        
        // Agregar página
        $pdf->AddPage();
        
        // Logo de la empresa
        $logo = 'images/logo.png'; // Asegúrate de tener un logo
        if (file_exists($logo)) {
            $pdf->Image($logo, 10, 10, 30, 0, 'PNG');
        }
        
        // Encabezado
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'SOUNDSPACE - TICKET DE COMPRA', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Ticket #' . $ticketData['compra_id'], 0, 1, 'C');
        $pdf->Cell(0, 5, 'Fecha: ' . $ticketData['fecha'], 0, 1, 'C');
        
        // Línea separadora
        $pdf->Ln(5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);
        
        // Información de la empresa
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, $ticketData['empresa']['nombre'], 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, $ticketData['empresa']['direccion'], 0, 1);
        $pdf->Cell(0, 5, 'Tel: ' . $ticketData['empresa']['telefono'], 0, 1);
        $pdf->Cell(0, 5, 'Email: ' . $ticketData['empresa']['email'], 0, 1);
        
        // Información del cliente
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'INFORMACIÓN DEL CLIENTE', 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Nombre: ' . $ticketData['usuario']['nombre'], 0, 1);
        $pdf->Cell(0, 5, 'Email: ' . $ticketData['usuario']['email'], 0, 1);
        
        // Tabla de productos
        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'DETALLE DE LA COMPRA', 0, 1);
        
        // Cabecera de la tabla
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(60, 6, 'Producto', 1, 0, 'L', 1);
        $pdf->Cell(40, 6, 'Artista', 1, 0, 'L', 1);
        $pdf->Cell(25, 6, 'Cantidad', 1, 0, 'C', 1);
        $pdf->Cell(30, 6, 'Precio Unit.', 1, 0, 'R', 1);
        $pdf->Cell(35, 6, 'Subtotal', 1, 1, 'R', 1);
        
        // Productos
        $pdf->SetFont('helvetica', '', 9);
        foreach ($ticketData['productos'] as $producto) {
            $pdf->Cell(60, 6, substr($producto['nombre'], 0, 30), 1, 0, 'L');
            $pdf->Cell(40, 6, substr($producto['artista'], 0, 20), 1, 0, 'L');
            $pdf->Cell(25, 6, $producto['cantidad'], 1, 0, 'C');
            $pdf->Cell(30, 6, '$' . number_format($producto['precio'], 2), 1, 0, 'R');
            $pdf->Cell(35, 6, '$' . number_format($producto['subtotal'], 2), 1, 1, 'R');
        }
        
        // Totales
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(140, 6, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell(50, 6, '$' . number_format($ticketData['total'], 2), 0, 1, 'R');
        
        $pdf->Cell(140, 6, 'IVA (16%):', 0, 0, 'R');
        $pdf->Cell(50, 6, '$' . number_format($ticketData['iva'], 2), 0, 1, 'R');
        
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(140, 8, 'TOTAL:', 0, 0, 'R');
        $pdf->Cell(50, 8, '$' . number_format($ticketData['total_con_iva'], 2), 0, 1, 'R');
        
        // Pie de página
        $pdf->Ln(15);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->MultiCell(0, 4, 
            "Gracias por su compra.\n" .
            "Este ticket es su comprobante de compra.\n" .
            "Para cualquier aclaración, contacte a soporte: " . $ticketData['empresa']['email'] . "\n" .
            "Fecha de emisión: " . $ticketData['fecha'], 0, 'C');
        
        // Guardar PDF
        $filename = 'tickets/ticket_' . $ticketData['compra_id'] . '_' . time() . '.pdf';
        if (!is_dir('tickets')) {
            mkdir('tickets', 0777, true);
        }
        
        $pdf->Output(__DIR__ . '/' . $filename, 'F');
        
        return $filename;
        
    } catch (Exception $e) {
        error_log("Error generando PDF: " . $e->getMessage());
        return null;
    }
}

// ========================
//  FUNCIÓN ALTERNATIVA SIMPLE (HTML to PDF)
// ========================
function generateTicketPDF($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
        return;
    }
    
    $compraId = intval($data['compraId']);
    $userId = intval($data['userId']);
    
    // Obtener información de la compra
    $compraSql = "SELECT c.*, u.nombre, u.apellido, u.email, 
                         t.numero_tarjeta, ec.estatus
                  FROM Compra c
                  JOIN usuarios u ON c.id_usuario = u.id
                  JOIN Tarjeta t ON c.id_tarjeta = t.id_tarjeta
                  JOIN Estatus_Compra ec ON c.id_estatus = ec.id_estatus
                  WHERE c.id_compra = $compraId AND c.id_usuario = $userId";
    
    $compraResult = $conn->query($compraSql);
    
    if (!$compraResult || $compraResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Compra no encontrada"]);
        return;
    }
    
    $compra = $compraResult->fetch_assoc();
    
    // Obtener items de la compra
    $itemsSql = "SELECT ci.*, 
                        CASE 
                            WHEN ci.tipo = 'album' THEN al.titulo
                            WHEN ci.tipo = 'cancion' THEN ca.titulo
                        END as nombre_producto,
                        CASE 
                            WHEN ci.tipo = 'album' THEN ar1.nombre_artista
                            WHEN ci.tipo = 'cancion' THEN ar2.nombre_artista
                        END as artista
                 FROM CompraItem ci
                 LEFT JOIN Album al ON ci.tipo = 'album' AND ci.id_producto = al.id_album
                 LEFT JOIN Cancion ca ON ci.tipo = 'cancion' AND ci.id_producto = ca.id_cancion
                 LEFT JOIN Artista ar1 ON al.id_artista = ar1.id_artista
                 LEFT JOIN Artista ar2 ON ca.id_artista = ar2.id_artista
                 WHERE ci.id_compra = $compraId";
    
    $itemsResult = $conn->query($itemsSql);
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
    
    // Generar HTML del ticket
    $html = generateTicketHTML($compra, $items);
    
    // Guardar como archivo HTML (alternativa si no hay TCPDF)
    $filename = 'tickets/ticket_' . $compraId . '.html';
    if (!is_dir('tickets')) {
        mkdir('tickets', 0777, true);
    }
    
    file_put_contents($filename, $html);
    
    echo json_encode([
        "success" => true,
        "ticket_url" => $filename,
        "html" => $html // También devolver HTML para mostrar en navegador
    ]);
}

// ========================
//  GENERAR HTML DEL TICKET
// ========================
function generateTicketHTML($compra, $items) {
    $total = floatval($compra['total']);
    $iva = $total * 0.16;
    $totalConIva = $total + $iva;
    
    // Formatear número de tarjeta
    $tarjeta = $compra['numero_tarjeta'];
    if (strlen($tarjeta) >= 4) {
        $tarjeta = "**** **** **** " . substr($tarjeta, -4);
    }
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Compra #{$compra['id_compra']}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ticket { border: 1px solid #000; padding: 20px; max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .company { margin-bottom: 15px; }
        .company h2 { color: #8b0000; margin: 5px 0; }
        .info { margin: 15px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .totals { text-align: right; margin-top: 20px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        .print-btn { display: block; margin: 20px auto; padding: 10px 20px; 
                    background: #8b0000; color: white; border: none; 
                    cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <div class="company">
                <h2>SOUNDSPACE</h2>
                <p>Av. Música 123, Ciudad Digital</p>
                <p>Tel: (55) 1234-5678 | ventas@soundspace.com</p>
            </div>
            
            <div class="info">
                <h3>TICKET DE COMPRA</h3>
                <p><strong>Ticket #:</strong> {$compra['id_compra']}</p>
                <p><strong>Fecha:</strong> {$compra['fecha']}</p>
                <p><strong>Estatus:</strong> {$compra['estatus']}</p>
            </div>
        </div>
        
        <div class="info">
            <h4>INFORMACIÓN DEL CLIENTE</h4>
            <p><strong>Nombre:</strong> {$compra['nombre']} {$compra['apellido']}</p>
            <p><strong>Email:</strong> {$compra['email']}</p>
            <p><strong>Tarjeta:</strong> {$tarjeta}</p>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Artista</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
HTML;

    foreach ($items as $item) {
        $subtotal = floatval($item['precio']) * intval($item['cantidad']);
        $html .= <<<HTML
                <tr>
                    <td>{$item['nombre_producto']} ({$item['tipo']})</td>
                    <td>{$item['artista']}</td>
                    <td>{$item['cantidad']}</td>
                    <td>\${$item['precio']}</td>
                    <td>\${$subtotal}</td>
                </tr>
HTML;
    }

    $html .= <<<HTML
            </tbody>
        </table>
        
        <div class="totals">
            <p><strong>Subtotal:</strong> \${$total}</p>
            <p><strong>IVA (16%):</strong> \${$iva}</p>
            <h3><strong>TOTAL:</strong> \${$totalConIva}</h3>
        </div>
        
        <div class="footer">
            <p>Gracias por su compra en SoundSpace</p>
            <p>Este documento es su comprobante de compra</p>
            <p>Para cualquier aclaración, contacte a soporte: ventas@soundspace.com</p>
        </div>
    </div>
    
    <button class="print-btn" onclick="window.print()">🖨️ Imprimir Ticket</button>
    <button class="print-btn" onclick="window.close()" style="background: #666;">Cerrar</button>
    
    <script>
        // Auto-imprimir al cargar
        window.onload = function() {
            // Descomenta la siguiente línea para auto-imprimir
            // window.print();
        };
    </script>
</body>
</html>
HTML;

    return $html;
}

?>