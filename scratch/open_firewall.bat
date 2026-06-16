@echo off
netsh advfirewall firewall add rule name="Apache XAMPP HTTP" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="Apache XAMPP HTTPS" dir=in action=allow protocol=TCP localport=443
echo.
echo === Done! Firewall rules added successfully ===
echo.
pause
