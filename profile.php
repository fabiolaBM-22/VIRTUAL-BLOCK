<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$msg = '';

// ACTUALIZAR DATOS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Si escribe password, actualizamos todo con hash. Si no, solo datos.
    if (!empty($password)) {
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
        $params = [$username, $email, $passHash, $user_id];
    } else {
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $params = [$username, $email, $user_id];
    }

    try {
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $_SESSION['username'] = $username; // Actualizar sesión
            $msg = "<div class='alert alert-success'>✅ Perfil actualizado correctamente.</div>";
        }
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger'>Error: El correo quizás ya existe.</div>";
    }
}

// OBTENER DATOS ACTUALES
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | UDGallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card-custom { background: #1e293b; border: 1px solid #334155; border-radius: 20px; padding: 2rem; max-width: 500px; width: 100%; }
        .form-control { background: #0f172a; border: 1px solid #334155; color: white; margin-bottom: 15px; }
        .form-control:focus { background: #0f172a; color: white; border-color: #0d47a1; box-shadow: none; }
        .btn-update { background: #0d47a1; color: white; width: 100%; border-radius: 10px; padding: 10px; border:none;}
    </style>
</head>
<body>
    <div class="card-custom">
        <h3 class="mb-4">Editar Perfil</h3>
        <?php echo $msg; ?>
        <form method="POST">
            <label class="text-secondary small">Nombre de Usuario</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            
            <label class="text-secondary small">Correo Electrónico</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label class="text-secondary small">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
            <input type="password" name="password" class="form-control" placeholder="*******">
            
            <button type="submit" class="btn btn-update mt-3">Guardar Cambios</button>
        </form>
        <div class="mt-3 text-center">
            <a href="dashboard.php" class="text-secondary text-decoration-none">← Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>
