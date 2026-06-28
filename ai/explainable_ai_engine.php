<?php

class ExplainableAIEngine
{
    public function explain(
        array $decision
    ): string {

        $text = "";

        $text .= "ตรวจพบว่าอาการมีความเป็นไปได้ของ ";

        $text .= $decision["symptom_name"] . ". ";

        $text .= "ระดับความรุนแรง ";

        $text .= $decision["urgency_level"] . ". ";

        if ($decision["ems_required"]) {

            $text .= "แนะนำให้เรียก EMS ทันที. ";

        } else {

            $text .= "ยังไม่จำเป็นต้องเรียก EMS. ";

        }

        $text .= "ควรเข้ารับการรักษาที่แผนก ";

        $text .= $decision["department"] . ". ";

        $text .= $decision["hospital_recommendation"];

        return $text;
    }
}