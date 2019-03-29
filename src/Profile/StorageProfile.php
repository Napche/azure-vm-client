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

    public $dataDisks = [];

    /**
     * @param int $size
     *      Disk size in GB
     */
    public function addEmptyDataDisk($size = 8, $name = null)
    {
         $disc = [
            "diskSizeGB" => $size,
            "createOption" => "Empty",
            "lun" => count($this->dataDisks) + 1,
        ];
         if ($name) {
             $disc["name"] = $name;
         }
        $this->dataDisks[] = $disc;
    }
}