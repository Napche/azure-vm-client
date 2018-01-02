<?php

namespace Azure\Profile;


class OsProfile
{
    public $computerName;

    public $adminUserName;

    public $adminPassword;

    public $linuxConfiguration = [];

    /**
     * @param mixed $computerName
     */
    public function setComputerName($computerName)
    {
        $this->computerName = $computerName;
    }

    /**
     * @param mixed $adminUserName
     */
    public function setAdminUserName($adminUserName)
    {
        $this->adminUserName = $adminUserName;
    }

    /**
     * @param mixed $adminPassword
     */
    public function setAdminPassword($adminPassword)
    {
        $this->adminPassword = $adminPassword;
    }

    /**
     * @param mixed $linuxConfiguration
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