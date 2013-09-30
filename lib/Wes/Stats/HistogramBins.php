<?php
namespace Wes\Stats;

use Wes\Logger;

/*
 * Generates histogram bins for incoming float data
 * Likely not perfect (pretty sure throwing all interval maxes into
 * the top bin is a problem for general sets), but it'll do for dates.
 */
class HistogramBins {
    protected $data;
    protected $dataMin;
    protected $dataMax;

    public function __construct($data=null, $processData=true, $dataMin=null, $dataMax=null) {
        $this->dataMin = $dataMin;
        $this->dataMax = $dataMax;
        $this->data = $data;
        if($data !== null && $processData) $this->ProcessData($data);
    }

    public function AddDataPoint($datum) {
        if($this->data === null) $this->data = array();
        $this->data[] = $datum;
    }

    /*
     * Really just used for getting the min and max, but a good spot to throw
     * any preprocessing.
     *
     * Which, it turns out, isn't even really used anymore, considering I have
     * to pass my own arbitrary min and max in. Oh well, now it does both
     * defined and calculated data ranges.
     */
    public function ProcessData($data=null) {
        if($data !== null) $this->data = $data;

        $curMax = null;
        $curMin = null;

        if(empty($this->data)) {
            return;
        }

        foreach($this->data as $datum) {
            if($curMax === null || $curMax < $datum) {
                $curMax = $datum;
            }
            if($curMin === null || $curMin > $datum) {
                $curMin = $datum;
            }
        }

        $this->dataMin = $curMin;
        $this->dataMax = $curMax;
    }

    /*
     * Calculate bins and, optionally, sub-bins. Pretty inefficient with the sub-bins,
     * but if it becomes a problem, we can do some non-recursive one-pass stuff.
     *
     * Or, more likely, move the number crunching out of PHP. :)
     */
    public function GetBins($nBins=10, $nSubBins=0) {
        if(empty($this->data)) {
            $arr = array();
            for($i = 0; $i < $nBins; $i++) {
                $arr[] = 0;
            }
            return $arr;
        }

        $binWidth = $this->GetBinWidth($nBins);
        $interval = $this->dataMax - $this->dataMin;

        $bins = array();
        $subBins = array();

        $subBinCalcs = $nSubBins ? $this->generateSubBinCalcs($nBins) : array();

        for($i = 0; $i < $nBins; $i++) {
            $bins[$i] = 0;
        }

        foreach($this->data as $datum) {
            $index = min(floor((($datum - $this->dataMin) / $binWidth)), $nBins-1);
            $bins[$index] += 1;
            if($nSubBins) $subBinCalcs[$index]->AddDataPoint($datum);
        }

        for($i = 0; $i < count($subBinCalcs); $i++) {
            $subBinCalcs[$i]->ProcessData();
            $subBinCalcs[$i]->dataMin = $i*$binWidth;
            $subBinCalcs[$i]->dataMax = ($i+1)*$binWidth;
            $subBins[] = $subBinCalcs[$i]->GetBins($nSubBins);
        }

        // Only return a nested array if we have sub-bins
        return $nSubBins
            ? array(
                'bins' => $bins,
                'subBins' => $subBins
            )
            : $bins;
    }

    protected function GetBinWidth($nBins) {
        $interval = $this->dataMax - $this->dataMin;
        $binWidth = $interval / $nBins;

        if($binWidth == 0) return 1;
        return $binWidth;
    }

    protected function generateSubBinCalcs($nBins) {
        $subBinCalcs = array();
        for($i = 0; $i < $nBins; $i++) {
            $subBinCalcs[] = new HistogramBins();
        }
        return $subBinCalcs;
    }

}
