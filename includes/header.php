<?php
/**
 * Codeflip Arena - Cabeçalho HTML Padrão
 * Mobile-First com Tailwind CSS e FontAwesome
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

$pageTitle = $pageTitle ?? APP_NAME;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="<?= APP_DESCRIPTION ?>">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?></title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN com Configuração de Tema Gamer/Neon -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                            950: '#1e1b4b'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Estilos Customizados da Arena -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">
