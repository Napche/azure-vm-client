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
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
}