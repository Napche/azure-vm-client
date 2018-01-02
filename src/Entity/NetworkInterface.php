<?php

namespace Azure\Entity;


class NetworkInterface
{
    public $location;

    public $properties;

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function addIpConfiguration($ipConfig)
    {
        $this->properties['ipConfigurations'][] = $ipConfig;
    }

    /**
     * @param bool $isIPv6
     */
    public function isIPv6($isIPv6)
    {
        $this->properties['ipConfigurations']['privateIPAddressVersion'] = $isIPv6 ? 'IPv6' : 'IPv4';
    }

    /**
     * @param string $subnetId
     */
    public function setSubnet($subnetId)
    {
        $this->properties['ipConfigurations']['subnet']['id'] = $subnetId;
    }
}