<?php

namespace Azure\Profile;


class StorageProfile
{
    public $osDisk = [
        "name" => '',
        "osType" => 'Linux',
        "createOption" => 'fromImage'
    ];

    public $imageReference = [];
}