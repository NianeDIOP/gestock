@echo off
cd /d "C:\xampp\htdocs\ayib-diop-new"

:: Démarrer le serveur Laravel
start "Laravel Server" php artisan serve

:: Ouvrir le navigateur en plein écran après 3 secondes
timeout /t 3 > nul
start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --start-fullscreen http://localhost:8000

exit