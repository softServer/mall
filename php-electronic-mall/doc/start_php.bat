TASKKILL /F /IM nginx.exe

@ping 127.0.0.1 -n 6 >nul

d:
cd D:\Program Files (x86)\nginx-1.16.1
start nginx
cd D:\Program Files (x86)\php-5.6.40
.\php-cgi.exe -b 127.0.0.1:9000 -c php.ini