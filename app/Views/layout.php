<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Music sharing'; ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main class="wrapper">
        <?= $content ?? '' ?>
    </main>

</body>
</html>
