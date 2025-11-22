<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Mon Site'; ?></title>
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

</body>
</html>
