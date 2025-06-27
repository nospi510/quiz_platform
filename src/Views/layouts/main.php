<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Plateforme Quiz'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/css/styles.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col bg-gradient-to-b from-blue-100 to-gray-50">
    <div class="carousel">
        <div class="carousel-inner">
            <div class="carousel-item"><img src="/images/photo1.jpeg" alt="Carrousel Image 1"></div>
            <div class="carousel-item"><img src="/images/photo2.jpeg" alt="Carrousel Image 2"></div>
            <div class="carousel-item"><img src="/images/photo3.jpeg" alt="Carrousel Image 3"></div>
        </div>
    </div>
    <nav class="bg-blue-800 text-white p-4 shadow-lg relative z-10">
        <div class="container mx-auto flex justify-between items-center">
            <a href="/quiz" class="flex items-center">
                <img src="/images/logo.jpg" alt="Logo EC2LT" class="h-10 w-10 rounded-full object-cover">
            </a>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): 
                    $user = \App\Models\User::findById($_SESSION['user_id']);
                ?>
                    <span class="text-sm font-medium">Bonjour, <?php echo htmlspecialchars($user->username); ?></span>
                    <a href="/auth/logout" class="btn-primary">Déconnexion</a>
                    <?php if ($user->is_admin): ?>
                        <a href="/admin/results" class="btn-secondary">Résultats Admin</a>
                        <a href="/admin/create_admin" class="btn-secondary">Créer Admin</a>
                        <a href="/admin/quiz_settings" class="btn-secondary">Paramètres Quiz</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/auth/login" class="btn-primary">Connexion</a>
                    <a href="/auth/register" class="btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto flex-grow py-8 relative z-10">
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
    <footer class="bg-blue-800 text-white py-3 text-center relative z-10">
        <div class="container mx-auto flex items-center justify-between">
            <div class="w-1/4 flex justify-start">
                <img src="/images/photo5.png" alt="Footer Logo EC2LT" class="h-12 w-21 object-contain">
            </div>
            <div class="w-1/2 text-center">
                <p class="text-xs font-semibold">École Centrale des Logiciels Libres et de Télécommunications</p>
                <p class="text-[9px]">Liberté 2 près du Rond Point Jet d’eau derrière l’immeuble de la banque CBAO</p>
                <p class="text-[9px] mt-1">© 2025 Plateforme Quiz - EC2LT</p>
            </div>
            <div class="w-1/4 text-right">
                <p class="text-[9px]">Tél : (+221) 33 868 18 85 / 33 824 10 60 / 77 466 71 63</p>
                <p class="text-[9px]">
                    Email : 
                    <a href="mailto:ecole@ec2lt.sn" class="hover:underline">ecole@ec2lt.sn</a> / 
                    <a href="mailto:ecole.ec2lt@gmail.com" class="hover:underline">ecole.ec2lt@gmail.com</a>
                </p>
            </div>
        </div>
    </footer>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/quiz/results') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/results') !== false): ?>
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
            <?php if (strpos($_SERVER['REQUEST_URI'], '/admin/results') !== false): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('form');
                    const countdownElement = document.getElementById('countdown');
                    if (form && countdownElement) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            countdownElement.classList.remove('hidden');
                            let countdown = 5;
                            countdownElement.textContent = `Affichage des résultats dans ${countdown}...`;
                            const interval = setInterval(() => {
                                countdown--;
                                if (countdown <= 0) {
                                    clearInterval(interval);
                                    form.submit();
                                } else {
                                    countdownElement.textContent = `Affichage des résultats dans ${countdown}...`;
                                }
                            }, 1000);
                        });
                    }
                });
            <?php endif; ?>
            window.onload = createConfetti;
        </script>
    <?php endif; ?>
    <script>
        // Gestion du carrousel
        document.addEventListener('DOMContentLoaded', function() {
            const carouselInner = document.querySelector('.carousel-inner');
            if (carouselInner) {
                const items = carouselInner.querySelectorAll('.carousel-item');
                let currentIndex = 0;
                const totalItems = items.length;

                function showNextItem() {
                    currentIndex = (currentIndex + 1) % totalItems;
                    carouselInner.style.transform = `translateX(-${currentIndex * 100 / totalItems}%)`;
                }

                // Initialiser à la première image
                carouselInner.style.transform = 'translateX(0)';
                setInterval(showNextItem, 5000); // Change toutes les 5 secondes
            }
        });
    </script>
</body>
</html>