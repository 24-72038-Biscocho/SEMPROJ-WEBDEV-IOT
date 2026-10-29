<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GreenPulse: Web-Integration IoT System for Lettuce Microclimate Monitoring.">
    <title>GreenPulse: Web-Integration IoT System for Lettuce Microclimate Monitoring</title>
    <script>
        (() => {
            const theme = localStorage.getItem('greenpulse-theme') === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('public/css/style.css?v=13')) ?>">
</head>
<body class="landing-page">
    <?= $content ?>
    <script src="<?= e(url('public/js/theme.js')) ?>" defer></script>
</body>
</html>
