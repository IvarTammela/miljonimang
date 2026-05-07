<?php

class Game
{
    public const POINTS = [100, 200, 300, 500, 1000, 2000, 4000, 8000, 16000, 32000, 64000, 125000, 250000, 500000, 1000000];
    public const SAFE_LEVELS = [5 => 1000, 10 => 32000, 15 => 1000000];

    public static function start(string $taskId, string $taskTitle, array $questions, string $player = ''): array
    {
        return [
            'taskId' => $taskId,
            'taskTitle' => $taskTitle,
            'player' => trim($player) ?: 'Anonüümne',
            'questions' => $questions,
            'current' => 0,
            'earned' => 0,
            'safe' => 0,
            'finished' => false,
            'status' => 'playing',
            'lifelines' => [
                'fifty' => true,
                'hint' => true,
                'audience' => true,
            ],
            'removedOptions' => [],
            'hint' => null,
            'audience' => null,
            'lastExplanation' => null,
            'leaderboardSaved' => false,
        ];
    }

    public static function answer(array $game, int $selectedIndex): array
    {
        if (($game['finished'] ?? false) || !isset($game['questions'][$game['current']])) {
            return $game;
        }

        $question = $game['questions'][$game['current']];
        $correct = $selectedIndex === $question['correctIndex'];
        $game['lastExplanation'] = [
            'correct' => $correct,
            'selectedIndex' => $selectedIndex,
            'correctIndex' => $question['correctIndex'],
            'text' => $question['explanation'],
        ];

        if (!$correct) {
            $game['earned'] = $game['safe'];
            $game['finished'] = true;
            $game['status'] = 'wrong';
            return $game;
        }

        $level = $game['current'] + 1;
        $game['earned'] = self::POINTS[$game['current']];
        if (isset(self::SAFE_LEVELS[$level])) {
            $game['safe'] = self::SAFE_LEVELS[$level];
        }

        if ($level >= 15) {
            $game['finished'] = true;
            $game['status'] = 'won';
            return $game;
        }

        $game['current']++;
        $game['removedOptions'] = [];
        $game['hint'] = null;
        $game['audience'] = null;

        return $game;
    }

    public static function quit(array $game): array
    {
        $game['finished'] = true;
        $game['status'] = 'quit';

        return $game;
    }

    public static function useFifty(array $game): array
    {
        if (!$game['lifelines']['fifty'] || $game['finished']) {
            return $game;
        }

        $question = $game['questions'][$game['current']];
        $wrongIndexes = array_values(array_filter([0, 1, 2, 3], fn (int $index): bool => $index !== $question['correctIndex']));
        shuffle($wrongIndexes);

        $game['removedOptions'] = array_slice($wrongIndexes, 0, 2);
        $game['lifelines']['fifty'] = false;

        return $game;
    }

    public static function useHint(array $game): array
    {
        if (!$game['lifelines']['hint'] || $game['finished']) {
            return $game;
        }

        $game['hint'] = $game['questions'][$game['current']]['hint'] ?? 'Mõtle, milline valik põhjendab lahenduse loogikat kõige täpsemalt.';
        $game['lifelines']['hint'] = false;

        return $game;
    }

    public static function useAudience(array $game): array
    {
        if (!$game['lifelines']['audience'] || $game['finished']) {
            return $game;
        }

        $question = $game['questions'][$game['current']];
        $correctIndex = $question['correctIndex'];
        $level = $game['current'] + 1;
        $correctShare = $level <= 5 ? random_int(55, 78) : ($level <= 10 ? random_int(42, 66) : random_int(30, 56));
        $remaining = 100 - $correctShare;
        $shares = array_fill(0, 4, 0);
        $shares[$correctIndex] = $correctShare;

        $wrongIndexes = array_values(array_filter([0, 1, 2, 3], fn (int $index): bool => $index !== $correctIndex));
        $first = random_int(5, max(5, $remaining - 10));
        $second = random_int(3, max(3, $remaining - $first - 3));
        $third = $remaining - $first - $second;
        shuffle($wrongIndexes);
        foreach ([$first, $second, $third] as $i => $share) {
            $shares[$wrongIndexes[$i]] = max(0, $share);
        }

        $game['audience'] = $shares;
        $game['lifelines']['audience'] = false;

        return $game;
    }

    public static function formatPoints(int $points): string
    {
        return number_format($points, 0, ',', ' ');
    }
}
