# Configuration Admin - Flux de Connexion

## ✅ Étapes Complétées

### 1️⃣ Configuration de Sécurité
- **Fichier**: `config/packages/security.yaml`
- **Modification**: 
  - Provider changé de `users_in_memory` à `users_in_database` (UtilisateurRepository)
  - Redirection automatique après connexion vers `/admin`
  - Route logout configurée pour rediriger vers `/signin`

### 2️⃣ Contrôleur Admin Créé
- **Fichier**: `src/Controller/Admin/AdminController.php`
- **Route**: `/admin` (nécessite le rôle `ROLE_ADMIN`)
- **Fonctionnalité**: Affiche le tableau de bord admin

### 3️⃣ Template Admin Créé
- **Fichier**: `templates/layout/admin.html.twig`
- **Fonctionnalités**:
  - Navbar avec email de l'utilisateur
  - Bouton déconnexion
  - Tableau de bord avec 4 sections
  - Design responsive Tailwind CSS

### 4️⃣ Utilisateur Admin Créé
- **Email**: `admin@example.com`
- **Mot de passe**: `admin123456`
- **Rôle**: Admin
- **Source**: Fixture Doctrine (`src/DataFixtures/AppFixtures.php`)

### 5️⃣ Fixtures de Rôles Créées
- **Admin** - Administrateur du système
- **User** - Utilisateur standard
- **Chef de Projet** - Chef de projet

### 6️⃣ Mise à Jour de l'Entité Utilisateur
- **Fichier**: `src/Entity/Utilisateur.php`
- **Modification**: Méthode `getRoles()` mappée pour inclure le rôle depuis la base de données
- **Résultat**: Convertit le rôle en `ROLE_ADMIN` pour l'authentification Symfony

---

## 🧪 Comment Tester

### Démarrer le serveur Symfony
```bash
php bin/console server:run
# ou
symfony serve
```

### Accéder à la page de connexion
1. Ouvrir: `http://localhost:8000/signin`

### Se connecter avec les identifiants Admin
- **Email**: `admin@example.com`
- **Mot de passe**: `admin123456`

### Vérifier la redirection
- Après connexion, vous devez être redirigé automatiquement à `/admin`
- La page affiche le message de bienvenue: "Bienvenue, User!"

### Se déconnecter
- Cliquer sur le bouton "Se déconnecter" (rouge en haut à droite)
- Vous serez redirigé vers `/signin`

---

## 📋 Routes Disponibles

| Route | Méthode | Protection | Description |
|-------|---------|-----------|-------------|
| `/` | GET | - | Page d'accueil |
| `/signin` | GET/POST | Public | Formulaire de connexion |
| `/logout` | GET | Public | Déconnexion |
| `/admin` | GET | ROLE_ADMIN | Tableau de bord admin |
| `/request-account` | GET/POST | Public | Demande de compte |

---

## 🔒 Protection des Routes

### Access Control (security.yaml)
```yaml
- { path: ^/admin, roles: ROLE_ADMIN }  # Seulement les admins
- { path: ^/request-account, roles: PUBLIC_ACCESS }  # Public
- { path: ^/signin, roles: PUBLIC_ACCESS }  # Public
- { path: ^/logout, roles: PUBLIC_ACCESS }  # Public
```

---

## 📝 Architecture

### Flux d'Authentification
1. **Utilisateur** → accède à `/signin`
2. **SecurityController** → affiche `security/signin.html.twig`
3. **Formulaire** → POST vers `/signin` avec email et mot de passe
4. **Symfony Security** → valide avec `UtilisateurRepository`
5. **Redirection** → redirige vers `/admin` (default_target_path)
6. **AdminController** → vérifie `ROLE_ADMIN`, affiche le tableau de bord

### Flux de Déconnexion
1. **Utilisateur** → clique "Se déconnecter"
2. **Logout Handler** → efface la session
3. **Redirection** → vers `/signin`

---

## 🛠️ Commandes Utiles

### Recharger les fixtures
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### Vérifier les routes
```bash
php bin/console debug:router
```

### Vérifier la configuration de sécurité
```bash
php bin/console debug:config security
```

### Vérifier le cache
```bash
php bin/console cache:clear
```

---

## 🚀 Prochaines Étapes

1. Créer les contrôleurs pour:
   - Gestion des Projets
   - Gestion des Utilisateurs
   - Gestion des Compétences

2. Implémenter les templates correspondants

3. Ajouter les formulaires pour les CRUD

4. Configurer les permissions pour les autres rôles

---

## 📂 Fichiers Modifiés/Créés

### Modifiés
- `config/packages/security.yaml`
- `src/Entity/Utilisateur.php`
- `templates/security/Signin.html.twig` (no changes needed)

### Créés
- `src/Controller/Admin/AdminController.php`
- `src/DataFixtures/AppFixtures.php`
- `src/Command/CreateAdminUserCommand.php` (optionnel)
- `templates/layout/admin.html.twig`
- `src/Repository/CompetenceRepository.php`
- `src/Repository/IndisponibiliteRepository.php`

