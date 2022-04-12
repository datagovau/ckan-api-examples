<# Get and set default proxy incase behind one #>
[System.Net.WebRequest]::DefaultWebProxy = [System.Net.WebRequest]::GetSystemWebProxy()
[System.Net.WebRequest]::DefaultWebProxy.Credentials = [System.Net.CredentialCache]::DefaultNetworkCredentials

<# Prerequisite variables #>
$apiKey = "API-KEY"
$resourceId = "RESOURCE-ID"
$uploadFilePath = "PATH"

<# Set the API call variable #>
$uri = "https://data.gov.au/data/api/action/resource_update"

<# Build form data #>
$data = @{
    id = $resourceId
    upload = Get-Item $uploadFilePath
    url = "URL" <# Could be improved to automtically pull from upload file #>
}

<# Build request header #>
$headers = @{
    Authorization = $apiKey
}

<# Result variables #>
<# success stays true and message stays empty unless an error is encountered #>
$success = $true
$message = ""

<# Submit request and capture error if there is one #>
try {
    Invoke-WebRequest -Uri $uri -Method POST -Headers $Headers -Form $data
}
catch {
    $Failure = $_.ErrorDetails.Message | ConvertFrom-Json
    $success = $Failure.success
    $message = $Failure.error.message
}

<# Return variables incase this script is called by another and want to pass the result #>
return $success, $message