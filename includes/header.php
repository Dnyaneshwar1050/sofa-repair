<?php
// includes/header.php
require_once __DIR__ . '/db.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="/frontend/public/logo-dark.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/frontend/public/logo-dark.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sofa Repair Near Me in Pune | Silva Furniture</title>

    <!-- Tailwind CSS (CDN for now) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            900: '#7c2d12',
                        }
                    },
                    boxShadow: {
                        'premium': '0 10px 40px -10px rgba(0,0,0,0.08)',
                        'soft': '0 4px 20px -2px rgba(0,0,0,0.05)',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17678278696"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'AW-17678278696');
    </script>

    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-TPTTB5WN');</script>
    <!-- End Google Tag Manager -->

    <!-- Global Scripts -->
    <script>
        <?php if(isLoggedIn()): ?>
        function fetchNotifications() {
            fetch('/api/NotificationController.php', { method: 'GET' })
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (badge && data.unread_count !== undefined) {
                        badge.innerText = data.unread_count;
                        badge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
                    }
                })
                .catch(err => console.error("Notification Poll Error", err));
        }
        // Poll every 30 seconds
        setInterval(fetchNotifications, 30000);
        // Initial fetch
        window.addEventListener('load', fetchNotifications);
        <?php endif; ?>
    </script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased selection:bg-brand-500 selection:text-white">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TPTTB5WN" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <?php include __DIR__ . '/navbar.php'; ?>
    
    <!-- Floating Chatbot Injection -->
    <?php include __DIR__ . '/chatbot.php'; ?>
    
    <main>