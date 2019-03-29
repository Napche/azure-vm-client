<?php

namespace Azure\Entity;


use Azure\Profile\HardwareProfile;
use Azure\Profile\networkProfile;
use Azure\Profile\osProfile;
use Azure\Profile\storageProfile;

interface VirtualMachineInterface
{

    /**
     * @return HardwareProfile
     */
    public function getHardwareProfile();

    /**
     * @return storageProfile
     */
    public function getStorageProfile();

    /**
     * @return osProfile
     */
    public function getOsProfile();

    /**
     * @return networkProfile
     */
    public function getNetworkProfile();

    /**
     * @return string
     */
    public function getResourceGroup();

    /**
     * @return string
     */
    public function getName();


}