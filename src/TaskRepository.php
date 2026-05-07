<?php

class TaskRepository
{
    private string $inputPath;

    /** @var string[] */
    private array $ignoredDirectories = ['.git', 'node_modules', 'vendor', '.cache'];

    /** @var string[] */
    private array $ignoredExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'zip', 'pdf', 'exe'];

    public function __construct(string $inputPath)
    {
        $this->inputPath = rtrim($inputPath, DIRECTORY_SEPARATOR);
    }

    /**
     * @return array<int, array{id:string,title:string,path:string}>
     */
    public function listTasks(): array
    {
        if (!is_dir($this->inputPath)) {
            return [];
        }

        $tasks = [];
        foreach (scandir($this->inputPath) ?: [] as $entry) {
            if (!preg_match('/^\d+$/', $entry)) {
                continue;
            }

            $path = $this->inputPath . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($path) || !is_file($path . DIRECTORY_SEPARATOR . 'assignment.md')) {
                continue;
            }

            $tasks[] = [
                'id' => $entry,
                'title' => $this->readTitle($path . DIRECTORY_SEPARATOR . 'assignment.md') ?: $entry,
                'path' => $path,
            ];
        }

        usort($tasks, fn (array $a, array $b): int => strcmp($a['id'], $b['id']));

        return $tasks;
    }

    /**
     * @return array{id:string,title:string,assignment:string,files:array<int,array{path:string,content:string}>}|null
     */
    public function getTask(string $id): ?array
    {
        if (!preg_match('/^\d+$/', $id)) {
            return null;
        }

        $path = realpath($this->inputPath . DIRECTORY_SEPARATOR . $id);
        $inputRoot = realpath($this->inputPath);
        if ($path === false || $inputRoot === false || !str_starts_with($path, $inputRoot)) {
            return null;
        }

        $assignmentPath = $path . DIRECTORY_SEPARATOR . 'assignment.md';
        if (!is_file($assignmentPath)) {
            return null;
        }

        $assignment = file_get_contents($assignmentPath) ?: '';

        return [
            'id' => $id,
            'title' => $this->readTitle($assignmentPath) ?: $id,
            'assignment' => $assignment,
            'files' => $this->readSolutionFiles($path),
        ];
    }

    private function readTitle(string $assignmentPath): ?string
    {
        $contents = file_get_contents($assignmentPath);
        if ($contents === false) {
            return null;
        }

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            if (preg_match('/^#\s+(.+)$/', trim($line), $match)) {
                return trim($match[1]);
            }
        }

        return null;
    }

    /**
     * @return array<int, array{path:string,content:string}>
     */
    private function readSolutionFiles(string $taskPath): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($taskPath, FilesystemIterator::SKIP_DOTS),
                function (SplFileInfo $current): bool {
                    if ($current->isDir()) {
                        return !in_array($current->getFilename(), $this->ignoredDirectories, true);
                    }

                    if ($current->getFilename() === 'assignment.md') {
                        return false;
                    }

                    $extension = strtolower($current->getExtension());
                    return !in_array($extension, $this->ignoredExtensions, true);
                }
            )
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getSize() > 120_000) {
                continue;
            }

            $relativePath = ltrim(str_replace($taskPath, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $content = file_get_contents($file->getPathname());
            if ($content === false || !mb_check_encoding($content, 'UTF-8')) {
                continue;
            }

            $files[] = [
                'path' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
                'content' => $content,
            ];
        }

        usort($files, fn (array $a, array $b): int => strcmp($a['path'], $b['path']));

        return $files;
    }
}
