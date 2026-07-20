<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FUK Virtual Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --gsu-green: #006837; --gsu-gold: #f1c40f; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .bg-gsu { background-color: var(--gsu-green) !important; color: white; }
        .btn-gsu { background-color: var(--gsu-green); color: white; border: none; }
        .btn-gsu:hover { background-color: #004d29; color: white; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 10px; }
        .navbar-brand { font-weight: bold; letter-spacing: 1px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-gsu mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">FUK VLE</a>
    <div class="d-flex">
        <?php if(isset($_SESSION['user_id'])): ?>
            <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container">