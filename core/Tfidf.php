<?php

class Tfidf {
    
    private $documents = [];
    private $vocabulary = [];
    private $idfValues = [];
    
    public function __construct() {
        $this->documents = [];
        $this->vocabulary = [];
        $this->idfValues = [];
    }

    public function addDocument($tokens) {
        $this->documents[] = $tokens;
        
        foreach ($tokens as $token) {
            if (!in_array($token, $this->vocabulary)) {
                $this->vocabulary[] = $token;
            }
        }
    }

    public function buildVocabulary($allDocuments) {
        $this->documents = $allDocuments;
        $this->vocabulary = [];
        
        foreach ($allDocuments as $tokens) {
            foreach ($tokens as $token) {
                if (!in_array($token, $this->vocabulary)) {
                    $this->vocabulary[] = $token;
                }
            }
        }
        
        $this->calculateIDF();
    }

    private function calculateIDF() {
        $N = count($this->documents);
        
        if ($N === 0) {
            return;
        }
        
        foreach ($this->vocabulary as $term) {
            $df = 0;
            
            foreach ($this->documents as $doc) {
                if (in_array($term, $doc)) {
                    $df++;
                }
            }
            
            if ($df > 0) {
                $this->idfValues[$term] = log($N / $df);
            } else {
                $this->idfValues[$term] = 0;
            }
        }
    }

    public function calculateTF($tokens) {
        $tf = [];
        $totalTerms = count($tokens);
        
        if ($totalTerms === 0) {
            return $tf;
        }
        
        $termFrequency = array_count_values($tokens);
        
        foreach ($termFrequency as $term => $count) {
            $tf[$term] = $count / $totalTerms;
        }
        
        return $tf;
    }

    public function calculateTFIDF($tokens) {
        $tf = $this->calculateTF($tokens);
        $tfidf = [];
        
        foreach ($this->vocabulary as $term) {
            $tfValue = isset($tf[$term]) ? $tf[$term] : 0;
            $idfValue = isset($this->idfValues[$term]) ? $this->idfValues[$term] : 0;
            
            $tfidf[$term] = $tfValue * $idfValue;
        }
        
        return $tfidf;
    }

    public function getVocabulary() {
        return $this->vocabulary;
    }

    public function getIdfValues() {
        return $this->idfValues;
    }

    public function vectorize($tokens) {
        $vector = [];
        
        foreach ($this->vocabulary as $term) {
            $vector[$term] = in_array($term, $tokens) ? 1 : 0;
        }
        
        return $this->calculateTFIDF($tokens);
    }
}
