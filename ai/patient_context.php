<?php

class PatientContext
{
    public int $age = 0;
    public string $gender = "";
    public float $weight = 0;
    public float $height = 0;

    public string $bloodPressure = "";
    public int $heartRate = 0;
    public int $spo2 = 0;
    public float $temperature = 0;

    public bool $pregnant = false;
    public bool $smoker = false;
    public bool $drinker = false;

    public array $chronicDiseases = [];
    public array $allergies = [];
    public array $medications = [];

    public function __construct(array $data = [])
    {
        $this->age = (int)($data["age"] ?? 0);
        $this->gender = $data["gender"] ?? "";
        $this->weight = (float)($data["weight"] ?? 0);
        $this->height = (float)($data["height"] ?? 0);

        $this->bloodPressure = $data["blood_pressure"] ?? "";
        $this->heartRate = (int)($data["heart_rate"] ?? 0);
        $this->spo2 = (int)($data["spo2"] ?? 0);
        $this->temperature = (float)($data["temperature"] ?? 0);

        $this->pregnant = (bool)($data["pregnant"] ?? false);
        $this->smoker = (bool)($data["smoker"] ?? false);
        $this->drinker = (bool)($data["drinker"] ?? false);

        $this->chronicDiseases = $data["chronic_diseases"] ?? [];
        $this->allergies = $data["allergies"] ?? [];
        $this->medications = $data["medications"] ?? [];
    }

    public function hasDisease(string $name): bool
    {
        return in_array($name, $this->chronicDiseases);
    }

    public function hasAllergy(string $name): bool
    {
        return in_array($name, $this->allergies);
    }

    public function takingMedicine(string $name): bool
    {
        return in_array($name, $this->medications);
    }

    public function isElderly(): bool
    {
        return $this->age >= 60;
    }

    public function isChild(): bool
    {
        return $this->age <= 12;
    }

    public function hasLowSpo2(): bool
    {
        return $this->spo2 > 0 && $this->spo2 < 94;
    }

    public function hasHighFever(): bool
    {
        return $this->temperature >= 38.5;
    }

    public function hasTachycardia(): bool
    {
        return $this->heartRate > 120;
    }

    public function getBMI(): float
    {
        if ($this->height <= 0) {
            return 0;
        }

        $heightMeter = $this->height / 100;

        return round(
            $this->weight / ($heightMeter * $heightMeter),
            2
        );
    }

    public function toArray(): array
    {
        return [
            "age" => $this->age,
            "gender" => $this->gender,
            "weight" => $this->weight,
            "height" => $this->height,
            "bmi" => $this->getBMI(),
            "blood_pressure" => $this->bloodPressure,
            "heart_rate" => $this->heartRate,
            "spo2" => $this->spo2,
            "temperature" => $this->temperature,
            "pregnant" => $this->pregnant,
            "smoker" => $this->smoker,
            "drinker" => $this->drinker,
            "chronic_diseases" => $this->chronicDiseases,
            "allergies" => $this->allergies,
            "medications" => $this->medications
        ];
    }
}