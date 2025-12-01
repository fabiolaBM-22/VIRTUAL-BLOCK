<?php
session_start();
require 'db.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $code = trim($_POST['invite_code']);

    // 1. Validar Código de Invitación
    if ($code !== 'UDGallery') {
        $msg = "<div class='alert alert-danger'>⛔ Código de invitación incorrecto.</div>";
    } else {
        // 2. Validar límite (40 usuarios)
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() >= 40) {
            $msg = "<div class='alert alert-warning'>⛔ Aforo completo (40/40 usuarios).</div>";
        } else {
            // 3. Validar Correo Duplicado
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $msg = "<div class='alert alert-danger'>⛔ Este correo ya está registrado.</div>";
            } else {
                // 4. Crear Usuario
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                if ($stmt->execute([$username, $email, $passHash])) {
                    $msg = "<div class='alert alert-success'>✅ ¡Registro exitoso! <a href='index.php' class='fw-bold text-white'>INICIA SESIÓN AQUÍ</a></div>";
                } else {
                    $msg = "<div class='alert alert-danger'>❌ Error de base de datos.</div>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | UDGallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-gradient: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%); }
        body { background: var(--bg-gradient); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; color: white; }
        
        .glass-card { 
            background: rgba(20, 20, 20, 0.85); /* más oscuro para mejor contraste */
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 24px; 
            padding: 2.5rem; 
            width: 100%; 
            max-width: 450px; 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5); 
        }

        /* INPUTS MÁS CLAROS */
        .form-control { 
            background: rgba(255, 255, 255, 0.1) !important; 
            border: 1px solid rgba(255, 255, 255, 0.3) !important; 
            color: #fff !important; 
            border-radius: 12px; 
            padding: 12px; 
            margin-bottom: 15px; 
        }
        
        .form-control:focus { 
            background: rgba(255, 255, 255, 0.2) !important; 
            color: white !important; 
            border-color: #fff !important; 
            box-shadow: 0 0 10px rgba(255,255,255,0.1); 
        }
        
        /* COLOR DEL TEXTO "PLACEHOLDER"  */
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.8) !important; 
            opacity: 1;
        }

        /* AUTOFILL negro */
        input:-webkit-autofill, 
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 100px #3b3b3b inset !important; 
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
            border: 1px solid rgba(255,255,255,0.5) !important;
        }

        .btn-glow { background: linear-gradient(90deg, #0d47a1 0%, #b71c1c 100%); border: none; color: white; padding: 12px; border-radius: 12px; font-weight: 600; width: 100%; }
        .btn-glow:hover { transform: scale(1.02); }
    </style>
</head>
<body>
    <div class="glass-card text-center">
        <h3 class="mb-4">Registro UDGallery</h3>
        <?php echo $msg; ?>
        <form method="POST" action="" autocomplete="off">
            <input type="text" name="username" class="form-control" placeholder="Nombre de Usuario" required>
            <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            <input type="text" name="invite_code" class="form-control text-center fw-bold" placeholder="CÓDIGO DE INVITACIÓN" required>
            <button type="submit" name="register" class="btn btn-glow mt-3">CREAR CUENTA</button>
        </form>
        <div class="mt-4"><a href="index.php" class="text-secondary text-decoration-none small">← Volver al Login</a></div>
    </div>
</body>
</html>
