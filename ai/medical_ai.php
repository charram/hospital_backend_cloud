<?php

require_once __DIR__ . "/ReasoningEngine.php";
require_once __DIR__ . "/PatientContext.php";

class MedicalAI
{
    private ReasoningEngine $reasoningEngine;

    public function __construct()
    {
        $this->reasoningEngine = new ReasoningEngine();
    }

   public function analyze(
    string $symptom,
    ?PatientContext $patient = null
): array
{
    return $this->reasoningEngine->analyze(
        $symptom,
        $patient
    );
}
}