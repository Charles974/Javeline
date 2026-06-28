<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titrePage ?? APP_NAME) ?></title>

    <!-- Bootstrap 5 -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
</head>
<body>

    <?php require APP_ROOT . '/app/views/partials/navbar.php'; ?>

    <main class="container my-4" id="contenu-principal" role="main">
        <?= $contenu ?? '' ?>
    </main>

    <?php require APP_ROOT . '/app/views/partials/footer.php'; ?>

    <!-- jQuery -->
    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"
    ></script>

    <!-- Bootstrap 5 JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmxbqEEWbGpWTrH1U38Zr0oO2xkMl"
        crossorigin="anonymous"
    ></script>

    <!-- Script principal -->
    <script src="<?= APP_URL ?>/js/main.js"></script>
</body>
</html>
