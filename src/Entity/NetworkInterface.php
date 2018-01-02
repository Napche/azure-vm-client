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
}