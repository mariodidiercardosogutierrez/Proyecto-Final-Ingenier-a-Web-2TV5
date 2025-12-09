<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

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
else if ($action === "getCancionesByAlbum") {  // ← AÑADIR ESTO
    getCancionesByAlbum($conn);
}
// ... después de las otras acciones ...

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
else {
    echo json_encode(["success" => false, "message" => "Acción no válida"]);
}


$conn->close();

// =======================
//     FUNCIÓN LOGIN
// =======================
function login($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }

    $email = $conn->real_escape_string($data['email']);
    $password = $data['password'];

    // Buscar usuario
    $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

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
                   ar.nombre_artista
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
//  OBTENER ARTISTAS (NUEVO)
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
            "nombre" => $row["nombre_artista"], // Alias para compatibilidad
            "correo" => $row["correo"]
        ];
    }

    echo json_encode([
        "success" => true,
        "artists" => $artistas  // Usamos "artists" para mantener compatibilidad con el JavaScript
    ]);
}

// =======================
//  OBTENER ÁLBUMES POR ARTISTA (NUEVO)
// =======================
function getAlbumsByArtist($conn) {
    $artistId = $_GET['artistId'] ?? '';
    
    $sql = "SELECT al.id_album, al.titulo, al.anio, al.id_artista, al.duracion_total, 
                   al.descripcion_album, al.genero_album, al.cantidadtemas, 
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
//  OBTENER CANCIONES POR ÁLBUM (ACTUALIZADA PARA TU DB)
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
            // Formatear la duración si es necesario
            $duracion = $row['duracion'];
            // Si la duración está en formato TIME, asegurarse de que se muestre bien
            if (strpos($duracion, ':') === false && is_numeric($duracion)) {
                // Convertir segundos a formato mm:ss
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
                "numero_track" => count($canciones) + 1 // Número secuencial
            ];
        }
    }
    
    // Si no hay canciones, mostrar mensaje (no generar simuladas)
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
// =======================
//  OBTENER CALIFICACIÓN DEL USUARIO (VERSIÓN ROBUSTA)
// =======================
function getRating($conn) {
    // Verificar parámetros
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
    
    // Validar que sean números positivos
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
        
        // Asegurar que la calificación esté entre 1 y 5
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
    
    if (!isset($data['userId']) || !isset($data['albumId']) || !isset($data['rating'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $albumId = intval($data['albumId']);
    $rating = intval($data['rating']);
    
    // Validar que la calificación esté entre 1 y 5
    if ($rating < 1 || $rating > 5) {
        echo json_encode(["success" => false, "message" => "Calificación inválida"]);
        return;
    }
    
    // Verificar si ya existe una calificación
    $checkSql = "SELECT id_album_calificacion FROM AlbumCalificacion 
                 WHERE id_usuario = $userId AND id_album = $albumId";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        // Actualizar calificación existente
        $sql = "UPDATE AlbumCalificacion 
                SET calificacion = $rating, fecha_calificacion = NOW() 
                WHERE id_usuario = $userId AND id_album = $albumId";
    } else {
        // Insertar nueva calificación
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
    
    if (!isset($data['userId']) || !isset($data['albumId'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }
    
    $userId = intval($data['userId']);
    $albumId = intval($data['albumId']);
    
    // Verificar si ya es favorito
    $checkSql = "SELECT id_album_favorito FROM AlbumFavorito 
                 WHERE id_usuario = $userId AND id_album = $albumId 
                 LIMIT 1";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        // Eliminar de favoritos
        $sql = "DELETE FROM AlbumFavorito 
                WHERE id_usuario = $userId AND id_album = $albumId";
        $message = "Álbum eliminado de favoritos";
    } else {
        // Agregar a favoritos
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
    
    // Contar número de calificaciones
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


?>

