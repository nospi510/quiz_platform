<?php
$title = 'Connexion';
ob_start();
?>
<h1 class="text-2xl font-bold mb-4">Connexion</h1>
<form method="POST" action="/auth/login" class="bg-white p-6 rounded shadow-md">
    <div class="mb-4">
        <label for="username" class="block text-sm font-medium">Nom d'utilisateur</label>
        <input type="text" name="username" id="username" class="mt-1 p-2 w-full border rounded" required>
    </div>
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium">Mot de passe</label>
        <input type="password" name="password" id="password" class="mt-1 p-2 w-full border rounded" required>
    </div>
    <button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Se connecter</button>
</form>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>