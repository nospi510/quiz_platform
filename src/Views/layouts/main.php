<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Plateforme Quiz'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/css/tailwind.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col bg-gradient-to-b from-blue-100 to-gray-50">
    <nav class="bg-blue-800 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="/quiz" class="text-2xl font-bold tracking-tight">Plateforme Quiz</a>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): 
                    $user = \App\Models\User::findById($_SESSION['user_id']);
                ?>
                    <span class="text-sm font-medium">Bonjour, <?php echo htmlspecialchars($user->username); ?></span>
                    <a href="/auth/logout" class="btn-primary">Déconnexion</a>
                    <?php if ($user->is_admin): ?>
                        <a href="/admin/results" class="btn-secondary">Résultats Admin</a>
                        <a href="/admin/create_admin" class="btn-secondary">Créer Admin</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/auth/login" class="btn-primary">Connexion</a>
                    <a href="/auth/register" class="btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto flex-grow py-8">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php echo $content; ?>
    </main>
    <footer class="bg-blue-800 text-white p-4 text-center">
        <p class="text-sm">© 2025 Plateforme Quiz - EC2LT</p>
    </footer>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/quiz/results') !== false): ?>
        <div class="confetti-container" id="confetti-container"></div>
        <script>
            function createConfetti() {
                const container = document.getElementById('confetti-container');
                for (let i = 0; i < 150; i++) {
                    const confetti = document.createElement('div');
                    confetti.classList.add('confetti');
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.animationDelay = Math.random() * 4 + 's';
                    confetti.style.width = Math.random() * 8 + 8 + 'px';
                    confetti.style.height = Math.random() * 8 + 8 + 'px';
                    container.appendChild(confetti);
                }
            }
            window.onload = createConfetti;
        </script>
    <?php endif; ?>
</body>
</html>