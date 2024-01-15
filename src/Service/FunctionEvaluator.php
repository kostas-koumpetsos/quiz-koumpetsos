<?php

namespace Inner;
use ChrisKonnertz\StringCalc;
class XValue extends StringCalc\Symbols\AbstractConstant
{
    protected $identifiers = ['x'];
    protected $value = 0;

    public function setValue($value) {
        $this->value = $value;
    }
}

namespace App\Service;

use Psr\Log\LoggerInterface;
use ChrisKonnertz\StringCalc;
use Inner\XValue;

class FunctionEvaluator
{
    private $stringCalc;
    private XValue $xval;

    public function __construct(
        private LoggerInterface $logger
    ) {
        $this->stringCalc = new StringCalc\StringCalc();
        $stringHelper = $this->stringCalc->getContainer()->get('stringcalc_stringhelper'); 
        $this->xval = new XValue($stringHelper);
        $this->stringCalc->addSymbol($this->xval);
    }

    private function convolution($array1, $array2) {
        $result = [];
        $size1 = count($array1);
        $size2 = count($array2);

        for ($i = 0; $i < $size1 + $size2 - 1; $i++) {
            $result[$i] = 0;
            for ($j = 0; $j < $size1; $j++) {
                $k = $i - $j;
                if ($k >= 0 && $k < $size2) {
                    $result[$i] += $array1[$j] * $array2[$k];
                }
            }
        }

        return $result;
    }
    
    private function arrayNorm($array) {
        $sumOfSquares = 0;

        foreach ($array as $element) {
            $sumOfSquares += pow($element, 2);
        }

        $norm = sqrt($sumOfSquares);

        return $norm;
    }

    private function normalizeArray($array) {
        $norm = $this->arrayNorm($array);

        // Avoid division by zero
        if ($norm == 0) {
            return $array;
        }

        $normalizedArray = array_map(function ($element) use ($norm) {
            return $element / $norm;
        }, $array);

        return $normalizedArray;
    }

    private function normalizedCrossCorrelation($array1, $array2) {
        // Reverse the second array
        $array2Reversed = array_reverse($array2);

        // Use the convolution function to calculate cross-correlation
        $result = $this->convolution(
            $this->normalizeArray($array1), 
            $this->normalizeArray($array2Reversed)
        );

        return $result;
    }

    public function mean_squared_error($array1, $array2) {
        $sum_diff_sq = 0;
        $this->logger->debug(json_encode($array1));
        $this->logger->debug(json_encode($array2));

        $this->logger->debug("1: ".count($array1)."  - 2: ".count($array2));

        for($i = 0; $i < count($array1); $i++) {
            $sum_diff_sq += pow($array1[$i] - $array2[$i], 2);
        }

        return $sum_diff_sq / count($array1);
    }

    public function getCorrelationSimilarity($array1, $array2) {
        return $this->normalizedCrossCorrelation($array1, $array2)[count($array1)-1]; 
    }

    public function getSimilarityResult($array1, $array2)
    {
        $similarity = $this->getCorrelationSimilarity($array1, $array2);
        $mse = $this->mean_squared_error($array1, $array2);

        $mx = max($array2);
        $mn = min($array2);

        $sol_delt_sq = pow(1.3 * ($mx-$mn), 2);

        if($sol_delt_sq >= 0.1)
            $normalized_mse = $mse / $sol_delt_sq;
        else
            $normalized_mse = $mse;

        if(($similarity > 0.95 && $normalized_mse < 0.0075) || 
            $normalized_mse < 0.001)
            $success = true;
        else
            $success = false;

        return [
            'corr_similarity' => $similarity,
            'normalized_mse' => $normalized_mse,
            'success' => $success
        ];
    }


    public function createDomain($from, $to, int $n) {
        $step = ($to-$from)/($n-1);
        return range($from, $to, $step);
    }

    public function applyStringFunction($function, $arr){
        $this->logger->debug("Applying $function to ".json_encode($arr)."\n");
        $result = [];
        foreach($arr as $v) {
            $this->xval->setValue($v);
            array_push($result, $this->stringCalc->calculate($function));
        }

        $this->logger->debug("Result: ".json_encode($result)."\n");

        return $result;
    }
}

