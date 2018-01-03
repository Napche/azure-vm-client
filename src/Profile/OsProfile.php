<?php

namespace Azure\Profile;


class OsProfile
{
    /**
     * @var string
     */
    public $computerName;

    /**
     * @var string
     */
    public $adminUserName;

    /**
     * @var string
     */
    public $adminPassword;

    /**
     * @var array
     */
    public $linuxConfiguration = [];

    /**
     * @var string
     */
    public $customData;

    /**
     * @param string $computerName
     */
    public function setComputerName($computerName)
    {
        $this->computerName = $computerName;
    }

    /**
     * @param string $adminUserName
     */
    public function setAdminUserName($adminUserName)
    {
        $this->adminUserName = $adminUserName;
    }

    /**
     * @param string $adminPassword
     */
    public function setAdminPassword($adminPassword)
    {
        $this->adminPassword = $adminPassword;
    }

    /**
     * @param string $customData
     */
    public function setCustomData($customData)
    {
        $this->customData = $customData;
    }

    /**
     * @param array $linuxConfiguration
     */
    public function setLinuxConfiguration($linuxConfiguration)
    {
        $this->linuxConfiguration = $linuxConfiguration;
    }

    /**
     * @param array $key
     */
    public function addSshKey(array $key)
    {
        $this->linuxConfiguration['ssh']['publicKeys'][] = $key;
    }
}