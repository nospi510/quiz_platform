<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Plateforme Quiz'); ?></title>
    <link href="/css/tailwind.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between">
            <a href="/quiz" class="font-bold">Plateforme Quiz</a>
            <?php if (isset($_SESSION['user_id'])): 
                $user = \App\Models\User::findById($_SESSION['user_id']);
            ?>
                <div>
                    <span>Bonjour, <?php echo htmlspecialchars($user->username); ?></span>
                    <a href="/auth/logout" class="ml-4">Déconnexion</a>
                    <?php if ($user->is_admin): ?>
                        <a href="/admin/results" class="ml-4">Résultats Admin</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div>
                    <a href="/auth/login" class="mr-4">Connexion</a>
                    <a href="/auth/register">Inscription</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container mx-auto mt-8">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php echo $content; ?>
    </div>
</body>
</html>