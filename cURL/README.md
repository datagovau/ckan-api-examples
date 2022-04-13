# cURL
cURL is a command line tool and library to assist in data transfer. If you need to download the tool please navigate to the offical page [here](https://docs.microsoft.com/en-us/powershell/). From there you can select the version best suited for your operating system.

## Example
From the command line enter the following
```
curl -H'Authorization: APIKEY' 'https://data.gov.au/data/api/action/resource_update' --form upload=@FILEPATH --form id=RESOURCE_ID --form url=URL
```
Where 
* APIKEY is your account API key.
* FILEPATH is the path to the file you wish to upload.
* RESOURCE_ID is the id of the resource you want to update on Data.gov.au.

## Documentation
For help relating to cURL please use the official documentation [here](https://curl.se/docs/)