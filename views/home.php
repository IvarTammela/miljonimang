<section class="panel">
    <div>
        <p class="eyebrow">Üks mäng, mitu ülesannet</p>
        <h1>Kontrolli, kas mõistad lahendust</h1>
        <p class="lead">Vali kontrollitav ülesanne. Miljonimäng on sama, aga küsimused koostatakse valitud ülesande kirjelduse ja lahendusfailide põhjal.</p>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="notice">Kaustas <code>input/</code> ei ole veel numbrilisi ülesandeid koos <code>assignment.md</code> failiga.</div>
    <?php else: ?>
        <form method="post" action="/" class="task-list">
            <input type="hidden" name="action" value="start">
            <label class="field">
                <span>Mängija nimi</span>
                <input type="text" name="player" maxlength="40" placeholder="Anonüümne">
            </label>
            <?php foreach ($tasks as $task): ?>
                <label class="task-card">
                    <input type="radio" name="task" value="<?= e($task['id']) ?>" required>
                    <span class="task-id"><?= e($task['id']) ?></span>
                    <span class="task-title"><?= e($task['title']) ?></span>
                </label>
            <?php endforeach; ?>
            <button class="primary" type="submit">Alusta mängu</button>
        </form>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/leaderboard.php'; ?>
