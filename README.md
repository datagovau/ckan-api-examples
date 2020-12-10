# ckan-api-examples
Examples of using the CKAN API in various programming languages

## Command line tools
You can also call the API using off the shell tools including to automatically upload data

### cURL
```
curl -H'Authorization: APIKEY' 'https://data.gov.au/data/api/action/resource_update' --form upload=@filename.csv --form id=RESOURCE_ID
```
Where APIKEY is your API key, RESOURCE_ID is your resource ID and filename.csv is your file to upload.
cURL comes with many Linux/MacOS operating systems but you can also download it for windows https://curl.se/windows/

### Microsoft Powershell 6+
```
Powershell commands:
$Headers = @{
    Authorization = APIKEY
}

$Form =  @{
     id = "RESOURCE_ID"
     upload = Get-Item .\filename.csv
}

Invoke-WebRequest -Uri https://data.gov.au/data/api/action/resource_update -Method POST -Form $Form -Headers $Headers
```
Where APIKEY is your API key, RESOURCE_ID is your resource ID and filename.csv is your file to upload.

The `-Form` option is not available in Windows Powershell 5.1 that comes with Windows 10 by default so you will get a `Invoke-WebRequest : A parameter cannot be found that matches parameter name 'Form'.` error.
