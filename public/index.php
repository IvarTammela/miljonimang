<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/TaskRepository.php';
require_once __DIR__ . '/../src/OpenAiQuestionClient.php';
require_once __DIR__ . '/../src/QuestionGenerator.php';
require_once __DIR__ . '/../src/Game.php';
require_once __DIR__ . '/../src/Leaderboard.php';

$repository = new TaskRepository(__DIR__ . '/../input');
$generator = new QuestionGenerator(
    new OpenAiQuestionClient(),
    __DIR__ . '/../prompts/question-generation.md'
);
$leaderboard = new Leaderboard(__DIR__ . '/../data/leaderboard.json');

$action = $_POST['action'] ?? $_GET['action'] ?? 'home';
$error = null;

try {
    if ($action === 'start' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $taskId = (string)($_POST['task'] ?? '');
        $task = $repository->getTask($taskId);

        if ($task === null) {
            throw new RuntimeException('Valitud ülesannet ei leitud.');
        }

        $_SESSION['game'] = Game::start(
            $task['id'],
            $task['title'],
            $generator->generate($task),
            (string)($_POST['player'] ?? '')
        );
        header('Location: /?action=game');
        exit;
    }

    if ($action === 'answer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['game'])) {
            header('Location: /');
            exit;
        }

        $_SESSION['game'] = Game::answer($_SESSION['game'], (int)($_POST['answer'] ?? -1));
        header('Location: /?action=game');
        exit;
    }

    if ($action === 'lifeline' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['game'])) {
            header('Location: /');
            exit;
        }

        $type = (string)($_POST['type'] ?? '');
        $_SESSION['game'] = match ($type) {
            'fifty' => Game::useFifty($_SESSION['game']),
            'hint' => Game::useHint($_SESSION['game']),
            'audience' => Game::useAudience($_SESSION['game']),
            default => $_SESSION['game'],
        };
        header('Location: /?action=game');
        exit;
    }

    if ($action === 'quit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_SESSION['game'])) {
            $_SESSION['game'] = Game::quit($_SESSION['game']);
        }
        header('Location: /?action=game');
        exit;
    }

    if ($action === 'reset') {
        unset($_SESSION['game']);
        header('Location: /');
        exit;
    }
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}

function e(string|int|null $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function render(string $view, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../views/layout.php';
}

if ($action === 'game' && isset($_SESSION['game'])) {
    if ($_SESSION['game']['finished'] && empty($_SESSION['game']['leaderboardSaved'])) {
        $leaderboard->record($_SESSION['game']);
        $_SESSION['game']['leaderboardSaved'] = true;
    }

    render('game', ['game' => $_SESSION['game'], 'leaderboard' => $leaderboard->all(), 'error' => $error]);
    exit;
}

render('home', ['tasks' => $repository->listTasks(), 'leaderboard' => $leaderboard->all(), 'error' => $error]);
