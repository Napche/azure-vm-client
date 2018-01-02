<?php

namespace Azure;


use Azure\Entity\NetworkInterface;
use Azure\Entity\VirtualMachineInterface;

class AzureVMClient extends AzureClient
{
    /**
     * @var array
     */
    private $locations = [];

    /**
     *  Lists the virtual machines in a subscription
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-list-subscription
     *
     * @return array
     */
    public function listVM()
    {
        $body = $this->get('providers/Microsoft.Compute/virtualmachines?api-version=' . static::VM_API_VERSION);

        return $body->value;
    }

    /**
     * List all available images.
     *
     * @return array
     */
    public function listImages()
    {
        $body = $this->get('resources?$filter=resourceType eq \'Microsoft.Compute/images\'&api-version=' . static::RESOURCEGROUPS_API_VERSION);

        return $body->value;
    }

    /**
     * Get information about a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-get
     *
     * @param string $name
     * @param string $resourceGroup
     * @return string
     */
    public function getVMStatus($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup, 'instanceView');
        $body = $this->get($url);
        if ($body && isset($body->statuses)) {
            foreach ($body->statuses as $VMStatus) {
                $status = $VMStatus->displayStatus;
                if (stripos($VMStatus->code, 'Power') === 0) {
                    return $VMStatus->displayStatus;
                }
            }
        }
        return $status;
    }

    /**
     * Create or update a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-create-or-update
     *
     * @param VirtualMachineInterface $machine
     * @param string $resourceGroup
     * @return array
     */
    public function createVM(VirtualMachineInterface $machine, $resourceGroup = 'Default')
    {
        $this->validateLocation($machine->getLocation());

        $url = $this->getVMUrl($machine->getName(), $resourceGroup);
        $machine->setResourceGroup($resourceGroup);

        return $this->put($url, $machine);
    }

    /**
     * @see createVM()
     */
    public function updateVM(VirtualMachineInterface $machine, $resourceGroup = 'Default')
    {
        $this->createVM($machine, $resourceGroup);
    }

    /**
     * Delete a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-delete
     *
     * @param $name
     * @param string $resourceGroup
     * @return mixed
     */
    public function deleteVM($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup);

        return $this->delete($url);
    }

    /**
     * Start a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-start
     *
     * @param $name
     * @param string $resourceGroup
     * @return mixed
     */
    public function startVM($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup, 'start');

        return $this->post($url, []);
    }


    /**
     * Restart a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-restart
     *
     * @param $name
     * @param string $resourceGroup
     * @return mixed
     */
    public function rebootVM($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup, 'restart');

        return $this->post($url, []);
    }

    /**
     * Stop a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-stop
     *
     * @param $name
     * @param string $resourceGroup
     * @return mixed
     */
    public function stopVM($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup, 'poweroff');

        return $this->post($url, []);
    }

    /**
     * Stop and deallocate a virtual machine
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-stop-deallocate
     *
     * @param $name
     * @param string $resourceGroup
     * @return mixed
     */
    public function deAllocateVM($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup, 'deallocate');

        return $this->post($url, []);
    }

    /**
     * Lists available virtual machine sizes for a subscription
     *
     * @see https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines/virtualmachines-list-sizes-region
     *
     * @param string $location
     * @return array
     */
    public function listVMSizes($location)
    {
        $this->validateLocation($location);
        $body = $this->get('providers/Microsoft.Compute/locations/'. $location .'/vmSizes?api-version=' . static::VM_API_VERSION);

        return $body->value;
    }

    /**
     * Subscriptions - List Locations
     *
     * @see https://docs.microsoft.com/en-us/rest/api/resources/subscriptions/listlocations
     *
     * @return array
     */
    public function listLocations()
    {
        if (!$this->locations) {
            $body = $this->get('locations?api-version=' . static::LOCATIONS_API_VERSION);
            $this->locations = $body->value;
        }

        return $this->locations;
    }

    /**
     * Resource Groups - List
     *
     * @see https://docs.microsoft.com/en-us/rest/api/resources/resourcegroups/list
     *
     * @return array
     */
    public function listResourceGroups()
    {
        $body = $this->get('resourcegroups?api-version=' . static::RESOURCEGROUPS_API_VERSION);
        return $body->value;
    }

    /**
     * Create or Update Network Interface
     *
     * @link https://docs.microsoft.com/en-us/rest/api/virtualnetwork/networkinterfaces/createorupdate
     *
     * @param VirtualMachineInterface|null $machine
     * @param NetworkInterface $interface
     * @return mixed
     */
    public function createNetworkInterface( VirtualMachineInterface $machine, NetworkInterface $interface)
    {
        $url = $this->getNetworkInterfaceUrl($machine->name, $machine->getResourceGroup());
        $interface->setLocation($machine->location);

        return $this->put($url, $interface);
    }


    public function deleteNetworkInterface(VirtualMachineInterface $machine)
    {
        $url = $this->getNetworkInterfaceUrl($machine->name, $machine->getResourceGroup());
        return $this->delete($url);
    }

    /**
     * Helper to get VM Url by ResourceGroup
     *
     * @param string $vm
     * @param string $resourceGroup
     * @param string $suffix
     *
     * @return string
     */
    private function getVMUrl($vm, $resourceGroup, $suffix = '')
    {
        $this->validateResourceGroup($resourceGroup);
        if ($suffix) {
            $suffix = '/' .  trim($suffix, '/');
        }
        return 'resourceGroups/'. $resourceGroup .'/providers/Microsoft.Compute/virtualMachines/' . $vm . $suffix . '?api-version='. static::VM_API_VERSION;
    }

    /**
     * Helper to get NetworkInterface Url by ResourceGroup
     *
     * @param $name
     * @param $resourceGroup
     * @return string
     */
    public function getNetworkInterfaceUrl( $name, $resourceGroup = 'Default' )
    {
        return 'resourceGroups/'. $resourceGroup .'/providers/Microsoft.Network/networkInterfaces/' . $name . '?api-version=' . static::NETWORK_INTERFACE_API_VERSION;
    }

    /**
     * Validate if a given location exists.
     *
     * @param string $locationName
     * @throws \Exception
     * @return object
     */
    private function validateLocation($locationName)
    {
        $locations = $this->listLocations();
        foreach ($locations as $location) {
            if ($locationName === $location->name) {
                return $location;
            }
        }
        throw new \Exception(sprintf('Unknown location: "%s"', $locationName));
    }

    /**
     * Validate if resourceGroup exists.
     *
     * @param $groupName
     * @return object
     * @throws \Exception
     */
    private function validateResourceGroup($groupName)
    {
        $resourceGroups = $this->listResourceGroups();
        foreach ($resourceGroups as $resourceGroup) {
            if ($groupName === $resourceGroup->name) {
                return $resourceGroup;
            }
        }
        throw new \Exception('Unknown ResourceGroup');
    }
}