@echo off
powershell -Command "[Console]::OutputEncoding = [System.Text.Encoding]::UTF8"
echo Ожидание MySQL...

set attempt=1
:check_mysql
docker exec php-admin-panel php -r "try { new PDO('mysql:host=db;dbname=php_admin_panel_db', 'app_user', 'app_pass'); echo 'success'; } catch (Exception $e) { echo 'error'; }" 2>nul | findstr "success" >nul

if %errorlevel% equ 0 (
    echo MySQL готов!
    echo Запуск миграции...
    docker exec php-admin-panel php migrations/001_init_tables.php
    exit /b 0
)

timeout /t 2 /nobreak >nul
set /a attempt+=1

if %attempt% gtr 30 (
    echo Ошибка: MySQL не запустился за 60 секунд
    exit /b 1
)

goto check_mysql