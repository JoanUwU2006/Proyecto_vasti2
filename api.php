<?php
header('Content-Type: application/json');

// --- 1. Configuración de la Conexión ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cinelumiere_db";

// --- 2. Crear la Conexión ---
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
}

// --- 3. Determinar la Acción Solicitada ---
$action = isset($_GET['action']) ? $_GET['action'] : 'peliculas'; // Por defecto, obtiene películas

switch ($action) {
    case 'dulceria':
        $sql = "SELECT id, nombre, descripcion, precio, imagen FROM dulceria";
        $result = $conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'guardar_compra':
        $datosCompra = json_decode(file_get_contents('php://input'), true);
        $pelicula_id = isset($datosCompra['pelicula']['id']) ? (int)$datosCompra['pelicula']['id'] : 0;
        $total = isset($datosCompra['total']) ? (float)$datosCompra['total'] : 0;
        $usuario_id = 1; // Simulación de usuario_id

        if ($pelicula_id > 0 && $total > 0) {
            $stmt = $conn->prepare("INSERT INTO compras (pelicula_id, usuario_id, fecha_compra, total) VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("iid", $pelicula_id, $usuario_id, $total);
            
            if ($stmt->execute()) {
                $id_compra = $stmt->insert_id;
                echo json_encode(['success' => true, 'id_compra' => $id_compra]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Datos de compra incompletos.']);
        }
        exit;

    case 'login':
        $credenciales = json_decode(file_get_contents('php://input'), true);
        $email = isset($credenciales['email']) ? $credenciales['email'] : '';
        $password = isset($credenciales['password']) ? $credenciales['password'] : '';

        if (!empty($email) && !empty($password)) {
            $stmt = $conn->prepare("SELECT id, email, password FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                if (password_verify($password, $usuario['password'])) {
                    echo json_encode(['success' => true, 'email' => $usuario['email']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'La contraseña es incorrecta.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró un usuario con ese email.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Email y contraseña son requeridos.']);
        }
        exit;

    case 'peliculas':
    default:
        $sql = "SELECT id, title, image, synopsis, time, price FROM peliculas";
        $result = $conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;
}

$conn->close();
?>