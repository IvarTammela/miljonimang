<?php

class Leaderboard
{
    public function __construct(private string $path)
    {
    }

    /**
     * @return array<int, array{player:string,taskId:string,taskTitle:string,points:int,status:string,createdAt:string}>
     */
    public function all(int $limit = 10): array
    {
        $rows = $this->read();
        usort($rows, function (array $a, array $b): int {
            $points = ($b['points'] ?? 0) <=> ($a['points'] ?? 0);
            if ($points !== 0) {
                return $points;
            }

            return strcmp((string)($b['createdAt'] ?? ''), (string)($a['createdAt'] ?? ''));
        });

        return array_slice($rows, 0, $limit);
    }

    public function record(array $game): void
    {
        $rows = $this->read();
        $rows[] = [
            'player' => $game['player'] ?: 'Anonüümne',
            'taskId' => $game['taskId'],
            'taskTitle' => $game['taskTitle'],
            'points' => (int)$game['earned'],
            'status' => $game['status'],
            'createdAt' => date('Y-m-d H:i:s'),
        ];

        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($this->path, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function read(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $contents = file_get_contents($this->path);
        $rows = $contents === false ? [] : json_decode($contents, true);

        return is_array($rows) ? $rows : [];
    }
}
