<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$msg = '';

// Lógica de subir imagen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    if ($role !== 'admin') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM images WHERE user_id = ?");
        $stmt->execute([$user_id]);
        if ($stmt->fetchColumn() >= 2) $msg = "<div class='alert alert-warning'>Has alcanzado el límite de 2 imágenes.</div>";
    }
    
    if (empty($msg) && !empty($_FILES['image']['tmp_name'])) {
        $file = $_FILES['image'];
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
        if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
        if (move_uploaded_file($file['tmp_name'], "uploads/" . $fileName)) {
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $sizeMB = $file['size'] / 1048576; 
            $stmt = $pdo->prepare("INSERT INTO images (user_id, filename, title, filesize_mb) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $fileName, $title, $sizeMB]);
            header("Location: dashboard.php?upload=success"); exit;
        }
    }
}
if (isset($_GET['upload'])) $msg = "<div class='alert alert-success alert-dismissible fade show'>¡Imagen publicada con éxito! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";

// Borrar
if (isset($_GET['delete'])) {
    $imgId = $_GET['delete'];
    $sql = ($role === 'admin') ? "SELECT * FROM images WHERE id = ?" : "SELECT * FROM images WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    if($role === 'admin') $stmt->execute([$imgId]); else $stmt->execute([$imgId, $user_id]);
    $img = $stmt->fetch();
    if ($img) {
        unlink("uploads/" . $img['filename']);
        $pdo->prepare("DELETE FROM images WHERE id = ?")->execute([$imgId]);
        header("Location: dashboard.php?deleted=true"); exit;
    }
}

// CONSULTAS
// 1. Galería Pública
$publicImages = $pdo->query("SELECT images.*, users.username FROM images JOIN users ON images.user_id = users.id ORDER BY uploaded_at DESC")->fetchAll();
// 2. Galería Personal
$myImages = $pdo->prepare("SELECT * FROM images WHERE user_id = ? ORDER BY uploaded_at DESC");
$myImages->execute([$user_id]);
$myGallery = $myImages->fetchAll();
// 3. LISTA DE USUARIOS (Para el panel izquierdo)
$allUsers = $pdo->query("SELECT username, role FROM users ORDER BY id DESC")->fetchAll();

// Obtener email del usuario actual para el navbar
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentUserEmail = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UDGallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --udg-blue: #0d47a1; --udg-red: #d32f2f; }
        body { background: #0f172a; color: #fff; font-family: 'Inter', sans-serif; }
        
        /* Navbar */
        .navbar { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .profile-link { text-decoration: none; font-size: 0.9rem; margin-right: 15px; }
        .user-highlight { color: #00e676; font-weight: bold; } /* Verde fosfo elegante */
        
        /* Layout Grid */
        .sidebar { background: rgba(30, 41, 59, 0.5); border-radius: 15px; padding: 20px; height: fit-content; }
        .user-item { padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; display: flex; align-items: center;}
        .user-icon { width: 30px; height: 30px; background: #334155; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 0.8rem; }
        
        /* Cards */
        .card-custom { background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; transition: all 0.3s; overflow: hidden;}
        .card-custom:hover { transform: translateY(-5px); border-color: var(--udg-blue); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .card-img-top { height: 200px; object-fit: cover; }
        .timestamp { font-size: 0.75rem; color: #94a3b8; }
        
        .btn-action-hero { background: linear-gradient(135deg, #0d47a1 0%, #b71c1c 100%); color: white; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="logo.png" height="30" alt="Logo" class="me-2"> <span class="fw-bold">UDGallery</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent"><span class="navbar-toggler-icon"></span></button>

    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
            <a href="profile.php" class="profile-link text-white">
                Perfil: <?php echo htmlspecialchars($currentUserEmail); ?> 
                <span class="user-highlight"><?php echo htmlspecialchars($username); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Salir</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid px-4" style="margin-top: 80px;">
    <?php echo $msg; ?>
    
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="sidebar">
                <h6 class="text-white mb-3 text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.8rem;">Usuarios Registrados</h6>
                <?php foreach($allUsers as $u): ?>
                    <div class="user-item">
                        <div class="user-icon">
                            <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                        </div>
                        <span class="<?php echo ($u['role']=='admin') ? 'text-danger fw-bold':'text-secondary'; ?>">
                            <?php echo htmlspecialchars($u['username']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                
                <hr class="border-secondary my-4">
                <button class="btn btn-action-hero w-100" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> Subir Imagen
                </button>
            </div>
        </div>

        <div class="col-lg-9">
            <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#public">🌎 Galería Global</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#personal">👤 Mis Fotos</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="public">
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php foreach($publicImages as $img): ?>
                        <div class="col">
                            <div class="card card-custom h-100">
                                <img src="uploads/<?php echo $img['filename']; ?>" class="card-img-top">
                                <div class="card-body">
                                    <h6 class="card-title text-white mb-1"><?php echo htmlspecialchars($img['title']); ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-secondary">@<?php echo htmlspecialchars($img['username']); ?></small>
                                        <small class="timestamp"><i class="bi bi-clock"></i> <?php echo date("d/m H:i", strtotime($img['uploaded_at'])); ?></small>
                                    </div>
                                    <?php if($role === 'admin'): ?>
                                        <a href="?delete=<?php echo $img['id']; ?>" class="text-danger text-decoration-none small" onclick="return confirm('¿Borrar?')"><i class="bi bi-trash"></i> Eliminar</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="personal">
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                         <?php foreach($myGallery as $img): ?>
                        <div class="col">
                            <div class="card card-custom">
                                <img src="uploads/<?php echo $img['filename']; ?>" class="card-img-top">
                                <div class="card-body">
                                    <h6 class="text-white"><?php echo htmlspecialchars($img['title']); ?></h6>
                                    <small class="timestamp d-block mb-2"><?php echo date("d/m Y H:i", strtotime($img['uploaded_at'])); ?></small>
                                    <a href="?delete=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger w-100 rounded-pill"><i class="bi bi-trash"></i> Borrar</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: #1e293b; color: white;">
      <div class="modal-header border-secondary"><h5 class="modal-title">Nueva Foto</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="text" name="title" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Título" required>
            <input type="file" name="image" class="form-control bg-dark text-white border-secondary" accept="image/*" required>
          </div>
          <div class="modal-footer border-secondary"><button type="submit" class="btn btn-action-hero w-100">Publicar</button></div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
