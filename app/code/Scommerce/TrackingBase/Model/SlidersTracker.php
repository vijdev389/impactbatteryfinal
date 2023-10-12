<?php

namespace Scommerce\TrackingBase\Model;

class SlidersTracker
{
    protected $slidersData = [];

    public function setSlidersCollection($items) 
    {
        $this->slidersData = array_merge($this->slidersData, $items);
    }

    public function getSlidersCollection() 
    {
        return $this->slidersData;
    }
}
