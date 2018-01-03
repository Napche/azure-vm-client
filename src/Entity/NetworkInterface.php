<?php

namespace Azure\Entity;


class NetworkInterface
{
    /**
     * @var string
     */
    public $location;

    /**
     * @var array
     */
    public $properties = [];

    /**
     * @var string
     */
    public $tags;

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @param array $ipConfig
     */
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
        $this->properties['ipConfigurations'][0]['name'] = 'default';
        $this->properties['ipConfigurations'][0]['properties']['subnet']['id'] = $subnetId;
    }

    /**
     * @param $publicIp
     */
    public function setPublicIp($publicIp)
    {
        $this->properties['ipConfigurations'][0]['properties']['publicIPAddress']['id'] = $publicIp;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
}