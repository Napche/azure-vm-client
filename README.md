# PHP Client for Azure Virtual Machines REST API.

Based on https://docs.microsoft.com/en-us/rest/api/compute/virtualmachines

## Create an Azure Active Directory (AAD) Application
AAD encourages the use of Applications / Service Principals for authenticating applications. An 
application / service principal combination provides a service identity to manage your Azure Subscription.
[Click here to learn about AAD applications and service principals.](https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-application-objects)
- [Install the Azure CLI](https://docs.microsoft.com/en-us/cli/azure/install-azure-cli)
- run `az login` to log into Azure
- run `az ad sp create-for-rbac` to create an Azure Active Directory Application with access to Azure Resource Manager 
for the current Azure Subscription
  - If you want to run this for a different Azure Subscription, run `az account set --subscription 'your subscription name'`
- run `az account list --query "[?isDefault].id" -o tsv` to get your Azure Subscription Id.
  
The output of `az ad sp create-for-rbac` should look like the following:
```json
{
  "appId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "displayName": "some-display-name",
  "name": "http://azure-cli-2017-04-03-15-30-52",
  "password": "XXXXXXXXXXXXXXXXXXXX",
  "tenant": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
```
The values `tenant`, `appId` and `password` are used in the Client constructor.

## Example Usage
```php
        use Azure\Entity\VirtualMachine;
        use Azure\AzureVMClient;
        use Azure\Profile\StorageProfile;
        
        $resourceGroupName = 'new-resource-group';
        $client->createResourceGroup($resourceGroupName, $region, $tag);
        
        // Create new machine
        $name = 'new_vm';
        $region = 'westeurope';
        $machine = new VirtualMachine( $name, $region );
        $machine->setResourceGroup( $resourceGroupName );
        
        // Add or change Profiles..
        $storage = new StorageProfile();
        $machine->setStorageProfile( $storage );
        
        // Create client with instant authentication.
        $client = new AzureVMClient(
            $subscriptionId,
            $tenant,
            $applicationId,
            $password
        );
        
        /*
        // Create client and authenticate LATER.
        $client = new AzureVMClient(
            $subscriptionId,
        );
        
        // Do some other stuff.
        
        $client->authenticate($tenant, $applicationId, $password);
        */
        
        // Create a VM.
        $client->createVM( $machine );
        
        // Delete afterwards.
        $client->deleteResourceGroup( $resourceGroupName );
```
