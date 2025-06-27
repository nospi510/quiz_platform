<?php
$title = 'Créer un Administrateur';
ob_start();
?>
<div class="card">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Créer un Administrateur</h2>
    <form method="POST" action="/admin/create_admin" class="space-y-5">
        <div class="text-center">
            <label for="username" class="block text-sm font-semibold text-gray-700">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="form-input mt-1" required>
        </div>
        <div class="text-center">
            <label for="password" class="block text-sm font-semibold text-gray-700">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-input mt-1" required>
        </div>
        <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-full border border-blue-600 shadow-lg hover:bg-blue-700 hover:scale-105 transition-all duration-300 ease-in-out w-full">Créer</button>    </form>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>