<?php

class OpenAiQuestionClient
{
    public function generate(array $task, string $promptTemplate): ?array
    {
        $apiKey = getenv('OPENAI_API_KEY');
        if (!$apiKey) {
            return null;
        }

        $payload = [
            'model' => getenv('OPENAI_MODEL') ?: 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $promptTemplate,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->buildContext($task), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                ],
            ],
            'temperature' => 0.8,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 25,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            return null;
        }

        $json = json_decode($content, true);
        if (isset($json['questions']) && is_array($json['questions'])) {
            return $json['questions'];
        }

        return is_array($json) ? $json : null;
    }

    private function buildContext(array $task): array
    {
        return [
            'assignment' => $task['assignment'],
            'solutionFiles' => $task['files'],
        ];
    }
}
