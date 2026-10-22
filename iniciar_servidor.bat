@echo off
title CinePimenta - Aluguel de Assentos de Cinema
color 0C
echo =====================================================================
echo              CinePimenta - Servidor Local de Desenvolvimento
echo =====================================================================
echo.
echo  Iniciando servidor PHP em: http://localhost:8000
echo.
echo  Contas de Acesso:
echo   [Gerente]: gerente@cinema.com  /  Senha: admin123
echo   [Cliente]: cliente@cinema.com  /  Senha: cliente123
echo.
echo  Pressione CTRL+C para encerrar o servidor a qualquer momento.
echo =====================================================================
echo.

where php >nul 2>nul
if %errorlevel% equ 0 (
    start http://localhost:8000
    php -S localhost:8000
) else (
    if exist "C:\Users\Etec Esportes\AppData\Local\php\php.exe" (
        start http://localhost:8000
        "C:\Users\Etec Esportes\AppData\Local\php\php.exe" -S localhost:8000
    ) else (
        echo [ERRO] PHP nao encontrado no sistema.
        pause
    )
)
