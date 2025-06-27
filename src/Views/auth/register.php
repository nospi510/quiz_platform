<?php
$title = 'Inscription';
ob_start();
?>
<div class="card">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Inscription</h2>
    <form method="POST" action="/auth/register" class="space-y-5">
        <div class="text-center">
            <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="form-input mt-1 max-w-xs mx-auto" required>
        </div>
        <div class="text-center">
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-input mt-1 max-w-xs mx-auto" required>
        </div>
        <div class="text-center">
            <label for="extension" class="block text-sm font-semibold text-gray-700 mb-1">Extension (ex. 5001)</label>
            <input type="text" name="extension" id="extension" class="form-input mt-1 max-w-xs mx-auto" required>
        </div>
        <button type="submit" class="btn-primary w-full">S'inscrire</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-600">Déjà un compte ? <a href="/auth/login" class="text-blue-600 hover:underline">Connectez-vous</a></p>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>