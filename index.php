<?php
session_start();
require 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BLOQUEO-VIRTUAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.05);
            --udg-blue: #0d47a1;
            --udg-red: #d32f2f;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
        }

        body {
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            color: white;
        }

        /* Orbes de fondo */
        .orb { position: absolute; border-radius: 50%; filter: blur(80px); z-index: -1; animation: float 10s infinite ease-in-out; }
        .orb-1 { width: 300px; height: 300px; background: #0d47a1; top: 10%; left: 20%; opacity: 0.4; }
        .orb-2 { width: 250px; height: 250px; background: #b71c1c; bottom: 10%; right: 20%; opacity: 0.3; animation-delay: -5s; }
        @keyframes float { 0% { transform: translate(0, 0); } 50% { transform: translate(30px, -30px); } 100% { transform: translate(0, 0); } }

        /* Tarjeta Glass (Igual que Registro) */
        .login-card {
            background: rgba(20, 20, 20, 0.85); /* Fondo ocsuro */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .logo-container img { height: 80px; margin-bottom: 1.5rem; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); }
        h2 { font-weight: 700; letter-spacing: -0.5px; margin-bottom: 0.5rem; }
        p.subtitle { color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 2rem; }

        /* INPUTS Mismo estilo que Registro) */
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
            box-shadow: 0 0 10px rgba(255,255,255,0.1);
            border-color: #fff !important;
            color: #fff !important;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.8) !important;
            opacity: 1;
        }

        /* FIX AUTOFILL */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 100px #3b3b3b inset !important; 
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
            border: 1px solid rgba(255,255,255,0.5) !important;
        }

        /* Botón */
        .btn-glow {
            background: linear-gradient(90deg, #0d47a1 0%, #b71c1c 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        .btn-glow:hover { transform: scale(1.02); box-shadow: 0 0 20px rgba(13, 71, 161, 0.4); }
        
        .alert-error { background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #ff8b94; border-radius: 12px; font-size: 0.9rem; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-card">
        <div class="logo-container">
            <img src="logo.png" alt="BLOQUEO-VIRTUAL">
        </div>
        
        <h2>Bienvenido</h2>
        <p class="subtitle">Inicia sesión en BLOQUEO-VIRTUAL</p>

        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <input type="email" name="email" class="form-control" placeholder="Correo Institucional / Usuario" required>
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            
            <button type="submit" name="login" class="btn btn-glow">ACCEDER</button>
        </form>

        <div class="mt-4 text-grey small">
            ¿No tienes acceso? <a href="register.php" class="text-decoration-none fw-bold text-blue">Regístrate</a>
        </div>
    </div>

</body>
</html>
