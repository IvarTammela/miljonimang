<section class="panel leaderboard-panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">Edetabel</p>
            <h2>Parimad tulemused</h2>
        </div>
    </div>

    <?php if (empty($leaderboard)): ?>
        <p class="muted">Edetabel on veel tühi.</p>
    <?php else: ?>
        <div class="leaderboard">
            <?php foreach ($leaderboard as $index => $row): ?>
                <div class="leaderboard-row">
                    <span class="rank"><?= $index + 1 ?></span>
                    <strong><?= e($row['player']) ?></strong>
                    <span><?= e($row['taskId']) ?> - <?= e($row['taskTitle']) ?></span>
                    <span><?= Game::formatPoints((int)$row['points']) ?> punkti</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
