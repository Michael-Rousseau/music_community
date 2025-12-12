<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php"); exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM musics WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$music = $stmt->fetch();

if (!$music) die("Accès refusé ou musique introuvable.");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $viz = $_POST['visibility'];

    $upd = $pdo->prepare("UPDATE musics SET title=?, description=?, visibility=? WHERE id=?");
    $upd->execute([$title, $desc, $viz, $id]);
    $message = "Modifications enregistrées !";
    $music['title'] = $title; $music['description'] = $desc; $music['visibility'] = $viz;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; background: #f4f4f9; }
        form { background: white; padding: 30px; border-radius: 10px; width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; }
        .alert { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Modifier la musique</h2>
        <?php if($message) echo "<div class='alert'>$message</div>"; ?>
        
        <label>Titre</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($music['title']); ?>" required>
        
        <label>Description</label>
        <textarea name="description" rows="4"><?php echo htmlspecialchars($music['description']); ?></textarea>
        
        <label>Visibilité</label>
        <select name="visibility">
            <option value="public" <?php if($music['visibility']=='public') echo 'selected'; ?>>Public</option>
            <option value="private" <?php if($music['visibility']=='private') echo 'selected'; ?>>Privé</option>
        </select>
        
        <button type="submit">Sauvegarder</button>
        <p style="text-align:center;"><a href="dashboard.php">Annuler</a></p>
    </form>
</body>
</html>
