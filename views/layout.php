<!doctype html>
<html lang="et">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Miljonimäng</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<main class="shell">
    <header class="topbar">
        <a class="brand" href="/?action=reset">Miljonimäng</a>
        <span>AI-põhine ülesande valideerimine</span>
    </header>

    <?php if (!empty($error)): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/' . $view . '.php'; ?>
</main>
</body>
</html>
