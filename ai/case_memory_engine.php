<?php

class CaseMemoryEngine
{
    private array $memory = [];

    public function remember(array $case): void
    {
        $this->memory[] = $case;
    }

    public function add(array $case): void
    {
        $this->remember($case);
    }

    public function getAll(): array
    {
        return $this->memory;
    }

    public function count(): int
    {
        return count($this->memory);
    }

    public function clear(): void
    {
        $this->memory = [];
    }

    public function search(string $keyword): array
    {
        $keyword = mb_strtolower(trim($keyword), "UTF-8");

        $result = [];

        foreach ($this->memory as $case) {

            $text = json_encode($case, JSON_UNESCAPED_UNICODE);

            if (
                mb_strpos(
                    mb_strtolower($text, "UTF-8"),
                    $keyword
                ) !== false
            ) {
                $result[] = $case;
            }

        }

        return $result;
    }

    public function findBestMatch(string $symptom): ?array
    {
        $symptom = mb_strtolower(trim($symptom), "UTF-8");

        foreach ($this->memory as $case) {

            if (!isset($case["symptom"])) {
                continue;
            }

            if (
                mb_strpos(
                    mb_strtolower($case["symptom"], "UTF-8"),
                    $symptom
                ) !== false
            ) {
                return $case;
            }

        }

        return null;
    }

    public function export(): array
    {
        return $this->memory;
    }

    public function import(array $cases): void
    {
        $this->memory = $cases;
    }
}