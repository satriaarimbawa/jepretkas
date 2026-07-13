$configFile = "deploy.config.json"
if (-not (Test-Path $configFile)) {
    Write-Error "deploy.config.json tidak ditemukan!"
    exit 1
}

$config = Get-Content $configFile -Raw | ConvertFrom-Json
$hostName = $config.host
$username = $config.username
$password = $config.password
$remoteDir = $config.remote_dir

$excludes = @(
    '.git',
    '.gitignore',
    '.github',
    'deploy.config.json',
    'deploy.config.json.example',
    'deploy.php',
    'deploy.ps1',
    'keuangan.sqlite',
    '.system_generated',
    '.gemini'
)

function Create-FtpDirectory {
    param($uri, $username, $password)
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $request.UsePassive = $true
    try {
        $response = $request.GetResponse()
        $response.Close()
        Write-Host "Create folder: $uri"
    } catch {
        $null = $_
    }
}

function Upload-FtpFile {
    param($localFile, $remoteUri, $username, $password)
    $request = [System.Net.FtpWebRequest]::Create($remoteUri)
    $request.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.UsePassive = $true
    $request.UseBinary = $true

    $fileBytes = [System.IO.File]::ReadAllBytes($localFile)
    $request.ContentLength = $fileBytes.Length

    $requestStream = $request.GetRequestStream()
    $requestStream.Write($fileBytes, 0, $fileBytes.Length)
    $requestStream.Close()

    $response = $request.GetResponse()
    $response.Close()
    Write-Host "Uploaded: $remoteUri"
}

function Sync-Directory {
    param($localFolder, $remotePath)
    
    $items = Get-ChildItem -Path $localFolder
    foreach ($item in $items) {
        $isExcluded = $false
        foreach ($exclude in $excludes) {
            if ($item.Name -eq $exclude) {
                $isExcluded = $true
                break
            }
        }
        if ($isExcluded) {
            continue
        }

        $relativeItemPath = ""
        if ($remotePath -eq "") {
            $relativeItemPath = $item.Name
        } else {
            $relativeItemPath = "$remotePath/$($item.Name)"
        }
        
        $ftpUri = "ftp://$hostName/$remoteDir/$relativeItemPath"

        if ($item.PSIsContainer) {
            Create-FtpDirectory -uri $ftpUri -username $username -password $password
            Sync-Directory -localFolder $item.FullName -remotePath $relativeItemPath
        } else {
            try {
                Upload-FtpFile -localFile $item.FullName -remoteUri $ftpUri -username $username -password $password
            } catch {
                Write-Host "Failed to upload $($item.Name)"
            }
        }
    }
}

Write-Host "Starting upload to ftp://$hostName/$remoteDir..."
Sync-Directory -localFolder (Get-Item .).FullName -remotePath ""
Write-Host "Deployment completed!"
