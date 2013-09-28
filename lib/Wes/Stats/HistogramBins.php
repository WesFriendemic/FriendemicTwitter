<?php
namespace Wes\Stats;

/*
 * Generates histogram bins for incoming float data
 * Likely not perfect (pretty sure throwing all interval maxes into
 * the top bin is a problem for general sets), but it'll do for dates.
 */
class HistogramBins {
    protected $data;
    protected $dataMin;
    protected $dataMax;

    public function __construct($data=null) {
        if($data !== null) $this->ProcessData($data);
    }

    public function AddDataPoint($datum) {
        if($this->data === null) $this->data = array();
        $this->data[] = $datum;
    }

    /*
     * Really just used for getting the min and max, but a good spot to throw
     * any preprocessing.
     */
    public function ProcessData($data=null) {
        if($data !== null) $this->data = $data;

        $curMax = null;
        $curMin = null;

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
            return array();
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
