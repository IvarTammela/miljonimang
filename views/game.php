<?php
$current = (int)$game['current'];
$question = $game['questions'][$current] ?? null;
$finished = (bool)$game['finished'];
$letters = ['A', 'B', 'C', 'D'];
?>

<section class="game-grid">
    <aside class="ladder" aria-label="Punktiredel">
        <?php foreach (array_reverse(Game::POINTS, true) as $index => $points): ?>
            <?php $level = $index + 1; ?>
            <div class="ladder-row <?= $index === $current && !$finished ? 'active' : '' ?> <?= isset(Game::SAFE_LEVELS[$level]) ? 'safe' : '' ?>">
                <span><?= $level ?></span>
                <strong><?= Game::formatPoints($points) ?></strong>
            </div>
        <?php endforeach; ?>
    </aside>

    <div class="panel game-panel">
        <div class="game-head">
            <div>
                <p class="eyebrow"><?= e($game['taskId']) ?> - <?= e($game['taskTitle']) ?></p>
                <h1><?= $finished ? 'Mäng lõppes' : 'Küsimus ' . ($current + 1) . ' / 15' ?></h1>
            </div>
            <div class="score">
                <span>Hetkeseis</span>
                <strong><?= Game::formatPoints((int)$game['earned']) ?> punkti</strong>
            </div>
        </div>

        <?php if ($finished): ?>
            <div class="result">
                <p class="result-status">
                    <?php if ($game['status'] === 'won'): ?>
                        Sa võitsid miljoni.
                    <?php elseif ($game['status'] === 'quit'): ?>
                        Jätsid mängu pooleli.
                    <?php else: ?>
                        Vastus oli vale.
                    <?php endif; ?>
                </p>
                <p>Lõpptulemus: <strong><?= Game::formatPoints((int)$game['earned']) ?> punkti</strong></p>
                <p>Mängija: <strong><?= e($game['player']) ?></strong></p>
                <?php if (!empty($game['lastExplanation'])): ?>
                    <div class="notice">
                        Õige vastus oli <?= e($letters[$game['lastExplanation']['correctIndex']]) ?>.
                        <?= e($game['lastExplanation']['text']) ?>
                    </div>
                <?php endif; ?>
                <a class="primary link-button" href="/?action=reset">Tagasi ülesannete valikusse</a>
            </div>
        <?php elseif ($question): ?>
            <?php if (!empty($game['lastExplanation'])): ?>
                <div class="notice <?= $game['lastExplanation']['correct'] ? 'success' : 'error' ?>">
                    <?= $game['lastExplanation']['correct'] ? 'Õige.' : 'Vale.' ?>
                    <?= e($game['lastExplanation']['text']) ?>
                </div>
            <?php endif; ?>

            <div class="question-card">
                <div class="difficulty"><?= e($question['difficulty']) ?> - <?= Game::formatPoints(Game::POINTS[$current]) ?> punkti</div>
                <h2><?= e($question['question']) ?></h2>
            </div>

            <form method="post" action="/" class="answers">
                <input type="hidden" name="action" value="answer">
                <?php foreach ($question['options'] as $index => $option): ?>
                    <?php $removed = in_array($index, $game['removedOptions'] ?? [], true); ?>
                    <button class="answer" type="submit" name="answer" value="<?= $index ?>" <?= $removed ? 'disabled' : '' ?>>
                        <span><?= $letters[$index] ?></span>
                        <?= e($option) ?>
                    </button>
                <?php endforeach; ?>
            </form>

            <div class="lifelines">
                <form method="post" action="/">
                    <input type="hidden" name="action" value="lifeline">
                    <input type="hidden" name="type" value="fifty">
                    <button type="submit" <?= $game['lifelines']['fifty'] ? '' : 'disabled' ?>>50:50</button>
                </form>
                <form method="post" action="/">
                    <input type="hidden" name="action" value="lifeline">
                    <input type="hidden" name="type" value="hint">
                    <button type="submit" <?= $game['lifelines']['hint'] ? '' : 'disabled' ?>>AI vihje</button>
                </form>
                <form method="post" action="/">
                    <input type="hidden" name="action" value="lifeline">
                    <input type="hidden" name="type" value="audience">
                    <button type="submit" <?= $game['lifelines']['audience'] ? '' : 'disabled' ?>>Publik</button>
                </form>
                <form method="post" action="/">
                    <input type="hidden" name="action" value="quit">
                    <button class="secondary" type="submit">Jäta pooleli</button>
                </form>
            </div>

            <?php if (!empty($game['hint'])): ?>
                <div class="notice">Vihje: <?= e($game['hint']) ?></div>
            <?php endif; ?>

            <?php if (!empty($game['audience'])): ?>
                <div class="audience">
                    <?php foreach ($game['audience'] as $index => $share): ?>
                        <div>
                            <span><?= $letters[$index] ?> - <?= e($share) ?>%</span>
                            <meter min="0" max="100" value="<?= e($share) ?>"></meter>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php if ($finished): ?>
    <?php require __DIR__ . '/leaderboard.php'; ?>
<?php endif; ?>
