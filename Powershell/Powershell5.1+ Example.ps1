<# Get and set default proxy incase behind one #>
[System.Net.WebRequest]::DefaultWebProxy = [System.Net.WebRequest]::GetSystemWebProxy()
[System.Net.WebRequest]::DefaultWebProxy.Credentials = [System.Net.CredentialCache]::DefaultNetworkCredentials

<# Prerequisite variables #>
$apiKey = "API-KEY"
$resourceId = "RESOURCE-ID"
$uploadFilePath = "PATH"

<# Build metadata #>
$metadata = @{
    url = "URL" <# Could be improved to automtically pull from upload file #>
}

<# Set the API call variable #>
$uri = "https://data.gov.au/data/api/action/resource_update"

<# Build multipart form data #>
<# Multipart variables #>
$boundary = [System.Guid]::NewGuid().ToString();
$LF = "`r`n";

<# Multipart data #>
$data = [System.Collections.ArrayList]::new()
$data.Add("--$boundary")
$data.Add("Content-Disposition: form-data; name=`"id`"")
$data.Add("")
$data.Add($resourceId)
foreach($key in $metadata.Keys)
{
    $data.Add("--$boundary")
    $data.Add("Content-Disposition: form-data; name=`"$key`"")
    $data.Add("")
    $data.Add($metadata.$key)
}
$data.Add("--$boundary")
$data.Add("Content-Disposition: form-data; name=`"upload`"; filename=`"$uploadFilePath`"")
$data.Add("Content-Type: application/octet-stream$LF")
$data.Add("")
$data.Add($(Get-Content $uploadFilePath -Raw))
$data.Add("--$boundary--$LF")
$multipart = $data -join $LF

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
    Invoke-WebRequest -Uri $uri -Method POST -ContentType "multipart/form-data; boundary=`"$boundary`"" -Headers $Headers -Body $multipart
}
catch {
    $Failure = $_.ErrorDetails.Message | ConvertFrom-Json
    $success = $Failure.success
    $message = $Failure.error.message
}

<# Return variables incase this script is called by another and want to pass the result #>
return $success, $message