@echo off
chcp 65001 >nul
echo ============================================
echo   DEPLOY - puntoventa.crossteam.cl
echo ============================================
echo.

:: ---- CONFIGURACION ----
set VPS_USER=root
set VPS_HOST=192.140.57.128
set VPS_PATH=/home/zadmin/puntoventa.crossteam.cl
set LOCAL_PATH=c:\xampp\htdocs\pventa-app
set STAGING=%TEMP%\pventa_deploy
set ZIP_LOCAL=%TEMP%\pventa_deploy.zip
set ZIP_REMOTE=/tmp/pventa_deploy.zip

:: -----------------------------------------------
:: PASO 1: Copiar archivos a staging 
::   Se excluyen vendor, node_modules, .env,
::   caches de framework y archivos de solo-local
:: -----------------------------------------------
echo [1/4] Preparando archivos en staging...
echo.

if exist "%STAGING%" rmdir /s /q "%STAGING%"
if exist "%ZIP_LOCAL%" del /f /q "%ZIP_LOCAL%"
if exist "%PS_SCRIPT%" del /f /q "%PS_SCRIPT%"
mkdir "%STAGING%"

robocopy "%LOCAL_PATH%" "%STAGING%" /E /NFL /NDL /NJH /NJS ^
  /XD ".git" "node_modules" ^
      "storage\logs" ^
      "storage\framework\cache" ^
      "storage\framework\sessions" ^
      "storage\framework\views" ^
      "bootstrap\cache" ^
      "documentos_fotos" ^
      "fotos_prod" ^
      "logo_empresa" ^
  /XF ".env" "deploy.bat" ".last_deploy"

for /f %%c in ('dir /s /b /a-d "%STAGING%" 2^>nul ^| find /c /v ""') do echo Archivos incluidos: %%c
echo.

:: -----------------------------------------------
:: PASO 2: Crear ZIP con rutas Linux (forward slashes)
::   Los paths se pasan via variables de entorno para evitar
::   que CMD corrompa los backslashes dentro del comando PS
:: -----------------------------------------------
echo [2/4] Creando ZIP...
echo.

set "PVENTA_STAGING=%STAGING%"
set "PVENTA_ZIP=%ZIP_LOCAL%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "Add-Type -AssemblyName System.IO.Compression.FileSystem; $s=$env:PVENTA_STAGING; $z=$env:PVENTA_ZIP; try { $zip=[IO.Compression.ZipFile]::Open($z,1); Get-ChildItem -LiteralPath $s -Recurse -File | ForEach-Object { $r=$_.FullName.Substring($s.Length+1).Replace('\','/'); $e=$zip.CreateEntry($r); $es=$e.Open(); $fs=[IO.File]::OpenRead($_.FullName); $fs.CopyTo($es); $fs.Dispose(); $es.Dispose() }; $zip.Dispose(); Write-Host 'ZIP OK' } catch { Write-Host ('ERROR ZIP: '+$_.Exception.Message); exit 1 }"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] PowerShell no pudo crear el ZIP. Revisa el mensaje de error arriba.
    rmdir /s /q "%STAGING%" 2>nul
    pause
    exit /b 1
)

rmdir /s /q "%STAGING%" 2>nul

if not exist "%ZIP_LOCAL%" (
    echo.
    echo [ERROR] No se creo el archivo ZIP.
    pause
    exit /b 1
)

echo ZIP listo.
echo.

:: -----------------------------------------------
:: PASO 3: Subir tar.gz al servidor
:: -----------------------------------------------
echo [3/4] Subiendo ZIP al servidor...
echo.

scp "%ZIP_LOCAL%" %VPS_USER%@%VPS_HOST%:%ZIP_REMOTE%

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Fallo la subida. Verifica: ssh %VPS_USER%@%VPS_HOST%
    del /f /q "%ZIP_LOCAL%" 2>nul
    pause
    exit /b 1
)

del /f /q "%ZIP_LOCAL%"
echo.

:: -----------------------------------------------
:: PASO 4: Desplegar en el servidor
::   1) Elimina codigo anterior (preserva vendor, storage y .env)
::   2) Extrae tar.gz (rutas con slash - sin problema de backslash)
::   3) Garantiza directorios de storage/bootstrap
::   4) Ajusta permisos y propietario en un solo pase
::   5) Regenera caches de artisan
:: -----------------------------------------------
echo [4/4] Desplegando en servidor...
echo.

ssh %VPS_USER%@%VPS_HOST% "which unzip > /dev/null 2>&1 || apt-get install -y unzip -q; cd %VPS_PATH% && cp -rp public/img/documentos_fotos /tmp/pv_doc_bak 2>/dev/null; cp -rp public/img/fotos_prod /tmp/pv_fot_bak 2>/dev/null; cp -rp public/img/logo_empresa /tmp/pv_log_bak 2>/dev/null; find . -maxdepth 1 -mindepth 1 ! -name storage ! -name .env -exec rm -rf {} + && unzip -q -o %ZIP_REMOTE% -d %VPS_PATH% && mkdir -p public/img && mv /tmp/pv_doc_bak public/img/documentos_fotos 2>/dev/null; mv /tmp/pv_fot_bak public/img/fotos_prod 2>/dev/null; mv /tmp/pv_log_bak public/img/logo_empresa 2>/dev/null; rm -f %ZIP_REMOTE% && echo '<?php' > vendor/composer/platform_check.php && mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs storage/app/public bootstrap/cache && rm -f bootstrap/cache/*.php && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php artisan config:cache && php artisan route:cache && chown -R zadmin:zadmin . && find . -type f -exec chmod 644 {} + && find . -type d -exec chmod 755 {} + && chmod -R 775 storage bootstrap/cache && chmod o+x /home/zadmin && echo DEPLOY OK"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Fallo el despliegue en el servidor.
    echo Conectate por SSH: ssh %VPS_USER%@%VPS_HOST%
    echo.
    pause
    exit /b 1
)

echo.
echo ============================================
echo   DEPLOY COMPLETADO EXITOSAMENTE
echo ============================================
echo.
pause
