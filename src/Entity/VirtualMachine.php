<?php


namespace Azure\Entity;


use Azure\Profile\HardwareProfile;
use Azure\Profile\NetworkProfile;
use Azure\Profile\OsProfile;
use Azure\Profile\StorageProfile;

class VirtualMachine implements VirtualMachineInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $location;

    /**
     * @var
     */
    public $properties;

    /**
     * @var
     */
    private $resourceGroup;

    /**
     * VirtualMachine constructor.
     * @param string $name
     * @param string $location
     */
    public function __construct($name, $location)
    {
        $this->name = $name;
        $this->location = $location;
        $this->setProperties();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed
     */
    public function getResourceGroup()
    {
        return $this->resourceGroup;
    }

    /**
     * @param mixed $resourceGroup
     */
    public function setResourceGroup($resourceGroup)
    {
        $this->resourceGroup = $resourceGroup;
    }

    /**
     * @param NetworkProfile $profile
     */
    public function setNetworkProfile($profile)
    {
        $this->properties['networkProfile'] = $profile;
    }

    /**
     * @param OsProfile $profile
     */
    public function setOsProfile($profile)
    {
        $this->properties['osProfile'] = $profile;
    }

    /**
     * @param StorageProfile $profile
     */
    public function setStorageProfile($profile)
    {
        $this->properties['storageProfile'] = $profile;
    }

    /**
     * @param HardwareProfile $profile
     */
    public function setHardwareProfile($profile)
    {
        $this->properties['hardwareProfile'] = $profile;
    }

    /**
     * Set default properties.
     */
    private function setProperties()
    {
        $this->properties = [
          'hardwareProfile' => $this->getHardwareProfile(),
          'storageProfile' => $this->getStorageProfile(),
          'osProfile' => $this->getOsProfile(),
          'networkProfile' => $this->getNetworkProfile(),
        ];
    }

    public function getHardwareProfile()
    {
        return new HardwareProfile();
    }

    public function getStorageProfile()
    {
        $profile = new StorageProfile();
        $profile->osDisk['name'] = $this->name;

        return $profile;
    }

    public function getOsProfile()
    {
        return new OsProfile();
    }

    public function getNetworkProfile()
    {
        return new NetworkProfile();
    }


}