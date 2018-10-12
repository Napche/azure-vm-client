<?php

namespace Azure;


use Azure\Entity\NetworkInterface;
use Azure\Entity\VirtualMachineInterface;

class AzureVMClient extends AzureClient
{
    /**
     *  Lists the virtual machines in a subscription
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
     * @param $vmName
     * @return mixed
     * @throws \Exception
     */
    public function getVmDetailsByName($vmName)
    {
        $body = $this->get('resources?$filter=resourceType eq \'Microsoft.Compute/virtualMachines\'and name eq \'' . $vmName . '\'&api-version=' . static::RESOURCEGROUPS_API_VERSION);

        return $body->value;
    }

    /**
     * List all available publishers for a location.
     *
     * @param string $location
     * @return array
     * @throws \Exception
     */
    public function listPublishers($location)
    {
        return $this->get($this->getPublisherRequest($location) . '?api-version=' . static::IMAGES_API_VERSION);
    }
    
    public function listOffers($location, $publisher)
    {
        return $this->get($this->getPublisherRequest($location) . '/' . $publisher . '/artifacttypes/vmimage/offers?api-version=' . static::IMAGES_API_VERSION);
    }

    /**
     * List all Skus for a given publisher at a given location.
     *
     * @param string $location
     * @param string $publisher
     * @param string $offer
     * @return array
     * @throws \Exception
     */
    public function listSkus($location, $publisher, $offer)
    {
        return $this->get($this->getPublisherRequest($location) . '/' . $publisher . '/artifacttypes/vmimage/offers/' . $offer . '/skus?api-version=' . static::IMAGES_API_VERSION);
    }

    /**
     * List all available skus.
     *
     * @return array
     * @throws \Exception
     */
    public function listSubscriptionSkus()
    {
        return $this->get('providers/Microsoft.Compute/skus?api-version=' . static::SKU_API_VERSION);
    }

    /**
     * Get information about a virtual machine
     *
     * @param string $name
     * @param string $resourceGroup
     * @return string
     */
    public function getVMStatus($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl($name, $resourceGroup, 'instanceView');
        $body = $this->get($url);
        $status = 'Unknown';
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
     * Get details for a VM.
     *
     * @param string $name
     * @param string $resourceGroup
     * @return object
     */
    public function getVMDetail($name, $resourceGroup = 'Default')
    {
        $url = $this->getVMUrl( $name, $resourceGroup );
        $body = $this->get($url);
        return $body;
    }


    /**
     * Create or update a virtual machine
     *
     * @param VirtualMachineInterface $machine
     * @return array
     */
    public function createVM( VirtualMachineInterface $machine )
    {
        $this->validateLocation($machine->getLocation());

        $url = $this->getVMUrl($machine->getName(), $machine->getResourceGroup());
        return $this->put($url, $machine);
    }

    /**
     * @see createVM()
     */
    public function updateVM(VirtualMachineInterface $machine, $resourceGroup = 'Default')
    {
        return $this->createVM($machine, $resourceGroup);
    }

    /**
     * Delete a virtual machine
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
     * Resource Groups - List
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
     * @param VirtualMachineInterface|null $machine
     * @param NetworkInterface $interface
     * @return mixed
     */
    public function createNetworkInterface( VirtualMachineInterface $machine, NetworkInterface $interface)
    {
        $url = $this->getNetworkInterfaceUrl($machine->name, $machine->getResourceGroup());
        $interface->setLocation($machine->location);
        $interface->setTags($machine->tags);

        return $this->put($url, $interface);
    }

    /**
     * Public IP Addresses - Create Or Update
     *
     * @param string $name
     * @param string $resourceGroup
     * @param string $location
     * @param string $tags
     * @param bool $ipv6
     * @return mixed
     */
    public function createPublicIpAddress($name, $resourceGroup, $location, $tags = '', $ipv6 = false)
    {
        $url = $this->getPublicIPUrl($name, $resourceGroup);

        $options = [
            'tags' => $tags,
            'location' => $location,
            'properties' => [
                'publicIPAllocationMethod' => $ipv6 ? 'Dynamic' : 'Static',
                'publicIPAddressVersion' => $ipv6 ? 'IPv6' : 'IPv4',
            ]
        ];
        return $this->put($url, $options);
    }


    /**
     * Create Virtual Network
     *
     * @param string $name
     * @param string $resourceGroup
     * @param string $location
     * @param array $prefixes
     * @param string $tags
     * @return object
     */
    public function createVirtualNetwork($name, $resourceGroup = 'Default', $location = 'westeurope', $prefixes = ["10.0.0.0/16"], $tags = '')
    {
        $url = $this->getVirtualNetworkUrl($name, $resourceGroup);

        $options = [
            'tags' => $tags,
            'location' => $location,
            'properties' => [
                'addressSpace' => [
                  'addressPrefixes' => $prefixes,
                ],
                'subnets' => [],
            ],
        ];

        return $this->put($url, $options);
    }

    /**
     * Get All virtual networks for a given location.
     *
     * @param $location
     * @return array
     * @throws \Exception
     */
    public function getVirtualNetworks($location)
    {
        $body = $this->get('resources?$filter=resourceType eq \'Microsoft.Network/virtualNetworks\' and location eq \''. $location . '\'&api-version=' . static::RESOURCEGROUPS_API_VERSION);
        return $body->value;
    }

    /**
     * Get Details about a Virtual Network
     *
     * @param $virtualNetworkName
     * @return mixed
     * @throws \Exception
     */
    public function getVirtualNetworkDetail($virtualNetworkName)
    {
        $body = $this->get('resources?$filter=resourceType eq \'Microsoft.Network/virtualNetworks\' and name eq \''. $virtualNetworkName . '\'&api-version=' . static::RESOURCEGROUPS_API_VERSION);
        return $body->value;
    }

    /**
     * Get all virtual networks.
     *
     * @return array
     * @throws \Exception
     */
    public function getAllVirtualNetworks()
    {
        $body = $this->get( 'providers/Microsoft.Network/virtualNetworks?api-version=2018-01-01');
        return $body->value;
    }


    /**
     * Get Subnets for a virtual network.
     *
     * @param string $resourceGroup
     * @param string $virtualNetworkName
     */
    public function getVirtualNetworkSubnets($resourceGroup, $virtualNetworkName)
    {
        $url = $this->getVirtualNetworkUrl($virtualNetworkName, $resourceGroup, '/subnets');
        $body = $this->get($url);
        return $body->value;
    }


    /**
     * Check IP Availability for a virtual Network.
     *
     * @param string $resourceGroup
     * @param string $virtualNetworkName
     * @return array
     * @throws \Exception
     */
    public function checkIpAvailability($resourceGroup, $virtualNetworkName)
    {
        $url = 'resourceGroups/'. $resourceGroup .'/providers/Microsoft.Network/virtualNetworks/' . $virtualNetWorkName . '/CheckIPAddressAvailability?ipAddress=10.0.0.0&api-version=' . static::NETWORK_INTERFACE_API_VERSION;
        $body = $this->get($url);
        return $body->value;
    }

    /**
     * Create a subnet.
     *
     * @param $name
     * @param string $resourceGroup
     * @return mixed
     */
    public function createSubnet( $networkName, $name, $resourceGroup = 'Default', $prefix = '10.0.0.0/16')
    {
        $url = $this->getVirtualNetworkUrl($networkName, $resourceGroup, '/subnets/' . $name);

        $options  = [
            'properties' => [
                'addressPrefix' => $prefix,
            ]
        ];

        return $this->put($url, $options);
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
     * Helper to get Virtual Network Url by ResourceGroup
     *
     * @param $name
     * @param $resourceGroup
     * @return string
     */
    public function getVirtualNetworkUrl( $name, $resourceGroup = 'Default', $suffix = '')
    {
        return 'resourceGroups/'. $resourceGroup .'/providers/Microsoft.Network/virtualNetworks/' . $name . $suffix . '?api-version=' . static::NETWORK_INTERFACE_API_VERSION;
    }

    /**
     * Helper to get NetworkInterface Url by ResourceGroup
     *
     * @param $name
     * @param $resourceGroup
     * @return string
     */
    public function getPublicIPUrl( $name, $resourceGroup = 'Default' )
    {
        return 'resourceGroups/'. $resourceGroup .'/providers/Microsoft.Network/publicIPAddresses/' . $name . '?api-version=' . static::NETWORK_INTERFACE_API_VERSION;
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

    /**
     * Helper function to build publishers url.
     * @param string $location
     * @return string
     */
    private function getPublisherRequest($location)
    {
        $this->validateLocation($location);
        return 'providers/Microsoft.Compute/locations/'.$location.'/publishers';
    }
}
