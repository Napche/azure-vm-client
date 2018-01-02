<?php

namespace Azure\Profile;


class HardwareProfile
{
    public $vmSize = "Standard_A0";

    /**
     * @param mixed $vmSize
     */
    public function setVmSize($vmSize)
    {
        $this->vmSize = $vmSize;
    }
}
