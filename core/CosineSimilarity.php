<?php

class CosineSimilarity {
    
    public function calculate($vectorA, $vectorB) {
        $dotProduct = $this->dotProduct($vectorA, $vectorB);
        
        $magnitudeA = $this->magnitude($vectorA);
        $magnitudeB = $this->magnitude($vectorB);
        
        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }
        
        $cosineSimilarity = $dotProduct / ($magnitudeA * $magnitudeB);
        
        return round($cosineSimilarity, 4);
    }

    private function dotProduct($vectorA, $vectorB) {
        $sum = 0;
        
        foreach ($vectorA as $key => $valueA) {
            if (isset($vectorB[$key])) {
                $sum += $valueA * $vectorB[$key];
            }
        }
        
        return $sum;
    }

    private function magnitude($vector) {
        $sum = 0;
        
        foreach ($vector as $value) {
            $sum += $value * $value;
        }
        
        return sqrt($sum);
    }

    public function findBestMatch($queryVector, $documentVectors) {
        $bestScore = 0;
        $bestIndex = -1;
        
        foreach ($documentVectors as $index => $docVector) {
            $score = $this->calculate($queryVector, $docVector);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $index;
            }
        }
        
        return [
            'index' => $bestIndex,
            'score' => $bestScore
        ];
    }
}
