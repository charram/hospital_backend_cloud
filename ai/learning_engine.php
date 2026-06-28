<?php

class LearningEngine
{
    private array $cases = [];

    public function __construct()
    {
        $this->cases = [];
    }

    public function addCase(array $case): void
    {
        $this->cases[] = $case;
    }

    public function learn(array $case): void
    {
        $this->addCase($case);
    }

    public function getCases(): array
    {
        return $this->cases;
    }

    public function countCases(): int
    {
        return count($this->cases);
    }

    public function findSimilarCase(string $symptom): ?array
    {
        $symptom = mb_strtolower(trim($symptom), "UTF-8");

        foreach ($this->cases as $case) {

            if (!isset($case["symptom"])) {
                continue;
            }

            if (mb_strpos(
                mb_strtolower($case["symptom"], "UTF-8"),
                $symptom
            ) !== false) {

                return $case;

            }
        }

        return null;
    }

    public function predict(string $symptom): ?array
    {
        return $this->findSimilarCase($symptom);
    }

    public function clear(): void
    {
        $this->cases = [];
    }

    public function export(): array
    {
        return $this->cases;
    }

    public function import(array $cases): void
    {
        $this->cases = $cases;
    }
}