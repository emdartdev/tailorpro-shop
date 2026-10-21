# ==============================================================================
# TailorPro Automated MySQL Backup Script (Windows / PowerShell)
# ==============================================================================
# Usage:
#   powershell.exe -ExecutionPolicy Bypass -File .\backup-mysql.ps1
#
# Can be scheduled via Windows Task Scheduler to run daily at 02:00 AM.
# ==============================================================================

param (
    [string]$DbUser = "root",
    [string]$DbPassword = "",
    [string]$DbName = "tailorpro",
    [string]$BackupDir = "D:\tailorpro-backups",
    [int]$RetentionDays = 30
)

$ErrorActionPreference = "Stop"

# Create backup directory if not exists
if (!(Test-Path -Path $BackupDir)) {
    New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null
    Write-Host "[+] Created backup directory at: $BackupDir" -ForegroundColor Cyan
}

$Timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$BackupFileName = "$DbName-backup-$Timestamp.sql"
$BackupFilePath = Join-Path -Path $BackupDir -ChildPath $BackupFileName

Write-Host "[*] Starting TailorPro database backup: $DbName at $(Get-Date)..." -ForegroundColor Yellow

# Locate mysqldump (check PATH or common XAMPP / MySQL installation paths)
$MysqldumpCmd = "mysqldump"
if (!(Get-Command mysqldump -ErrorAction SilentlyContinue)) {
    $PotentialPaths = @(
        "C:\xampp\mysql\bin\mysqldump.exe",
        "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe",
        "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqldump.exe",
        "C:\laragon\bin\mysql\current\bin\mysqldump.exe"
    )
    foreach ($path in $PotentialPaths) {
        if (Test-Path $path) {
            $MysqldumpCmd = $path
            break
        }
    }
}

# Construct arguments
$DumpArgs = @(
    "--user=$DbUser",
    "--single-transaction",
    "--quick",
    "--routines",
    "--triggers",
    "--databases", $DbName,
    "--result-file=$BackupFilePath"
)

if ($DbPassword -ne "") {
    $DumpArgs = @("--password=$DbPassword") + $DumpArgs
}

& $MysqldumpCmd @DumpArgs

if ($LASTEXITCODE -eq 0 -and (Test-Path $BackupFilePath)) {
    $FileSize = (Get-Item $BackupFilePath).Length / 1MB
    Write-Host ("[✓] Backup successfully created: {0} ({1:N2} MB)" -f $BackupFileName, $FileSize) -ForegroundColor Green

    # Optional: Compress with PowerShell built-in zip
    $ZipFilePath = "$BackupFilePath.zip"
    Compress-Archive -Path $BackupFilePath -DestinationPath $ZipFilePath -CompressionLevel Optimal
    Remove-Item -Path $BackupFilePath -Force
    Write-Host "[✓] Compressed archive saved: $ZipFilePath" -ForegroundColor Green

    # Retention Policy: Clean up backups older than $RetentionDays
    $CutoffDate = (Get-Date).AddDays(-$RetentionDays)
    Get-ChildItem -Path $BackupDir -Filter "$DbName-backup-*.zip" | Where-Object { $_.LastWriteTime -lt $CutoffDate } | ForEach-Object {
        Write-Host "[-] Purging obsolete backup: $($_.Name)" -ForegroundColor DarkGray
        Remove-Item $_.FullName -Force
    }

    Write-Host "[✓] Backup cycle finished successfully. Retention policy: $RetentionDays days." -ForegroundColor Green
} else {
    Write-Host "[!] Error: mysqldump execution failed with exit code $LASTEXITCODE." -ForegroundColor Red
    exit 1
}
