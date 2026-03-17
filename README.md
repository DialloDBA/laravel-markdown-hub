# MarkdownHub

> Plateforme professionnelle de gestion, édition et publication de fichiers README & Markdown — avec assistant IA intégré et gestion d'abonnements.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-4.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue?style=flat-square)](LICENSE)

---

## ✨ Fonctionnalités

### 📂 Gestion de fichiers
- Éditeur Markdown intégré (GitHub Flavored Markdown)
- Organisation par dossiers hiérarchiques
- Importation par lot (`.md`, `.txt`, `.markdown`, max 10 Mo)
- Fusion de plusieurs fichiers en un seul document
- Recherche instantanée + suppression groupée

### 🤖 Assistant IA
- Compatible **OpenAI**, **Groq**, **Mistral AI** et tout fournisseur OpenAI-compatible
- Sélection du fournisseur et du modèle par l'utilisateur
- Clé API personnelle par utilisateur (chiffrée en base)
- Sauvegarde directe des réponses en fichier README
- Historique de conversation en session

### 💳 Abonnements & Paiements
- Plans configurables (Free, Pro, …) par l'admin
- Multi-gateway : Stripe et tout autre passerelle configurable
- Historique des paiements par utilisateur

### 🛡️ Panneau d'administration
- Gestion des fournisseurs IA (clés API chiffrées)
- Gestion des passerelles de paiement (config chiffrée)
- Gestion des plans d'abonnement
- Gestion des utilisateurs (rôles, statut)
- Paramètres globaux de la plateforme

### 🌍 Internationalisation
- Interface bilingue **Français / Anglais**
- Changement de langue persisté en session

### 📄 Export
- Export PDF professionnel (DomPDF)

---

## 🛠️ Stack technique

| Couche | Technologie |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Frontend réactif | Livewire 3 Volt + Alpine.js |
| CSS | Tailwind CSS 4 |
| Markdown | League\CommonMark (GFM) |
| PDF | Barryvdh\DomPDF |
| Base de données | SQLite (dev) / MySQL ou PostgreSQL (prod) |
| Sécurité | Chiffrement Laravel (`Crypt`) pour toutes les clés API |

---

## ⚙️ Installation

### Prérequis
- PHP ≥ 8.2
- Composer
- Node.js ≥ 18 + npm

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/diallodba/markdownhub.git
cd markdownhub

# 2. Dépendances PHP
composer install

# 3. Dépendances JS + build
npm install && npm run build

# 4. Environnement
cp .env.example .env
php artisan key:generate

# 5. Base de données
touch database/database.sqlite
php artisan migrate --seed

# 6. Serveur local
php artisan serve
```

Application disponible sur **http://127.0.0.1:8000**

---

## 🔑 Accès par défaut

Après `php artisan migrate --seed` :

| Champ | Valeur |
|---|---|
| Email | `admin@markdownhub.com` |
| Mot de passe | `Admin@1234!` |

> ⚠️ **Changez ce mot de passe immédiatement en production.**

---

## 🔧 Configuration fournisseurs IA

Depuis **`/admin/ai-providers`** — entrez votre clé API (chiffrée automatiquement) :

| Fournisseur | Base URL | Modèles populaires |
|---|---|---|
| OpenAI | `https://api.openai.com/v1` | gpt-4o, gpt-4o-mini |
| Groq *(gratuit)* | `https://api.groq.com/openai/v1` | llama-3.3-70b-versatile |
| Mistral AI | `https://api.mistral.ai/v1` | mistral-large-latest |
| Ollama *(local)* | `http://localhost:11434/v1` | llama3, mistral |

---

## 📁 Structure du projet

```
├── app/
│   ├── Http/Middleware/       # SetLocale, IsAdmin
│   ├── Http/Controllers/      # ImportController
│   └── Models/                # User, ReadmeFile, Folder, AiProvider…
├── database/migrations/       # 10 migrations
├── database/seeders/          # AdminSeeder
├── lang/fr/ & lang/en/        # Traductions complètes
├── resources/views/
│   ├── layouts/               # app, admin, guest
│   ├── livewire/              # Composants Volt
│   │   └── admin/             # Panneau administration
│   └── welcome.blade.php      # Landing page bilingue
└── routes/web.php & auth.php
```

---

## 🚀 Production

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> Remplacez SQLite par **MySQL** ou **PostgreSQL** dans `.env` pour la production.

---

## 👤 Auteur

**Abdourahamane Diallo**

[![Email](https://img.shields.io/badge/Email-contact@atanax.com-D14836?style=flat-square&logo=gmail&logoColor=white)](mailto:contact@atanax.com)
[![Website](https://img.shields.io/badge/Website-diallodba.com-000000?style=flat-square&logo=safari&logoColor=white)](https://diallodba.com)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-diallodba-0077B5?style=flat-square&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/diallodba)
[![GitHub](https://img.shields.io/badge/GitHub-diallodba-181717?style=flat-square&logo=github&logoColor=white)](https://github.com/diallodba)
[![Twitter/X](https://img.shields.io/badge/X-@DialloDBA-000000?style=flat-square&logo=x&logoColor=white)](https://x.com/DialloDBA)

---

## 📄 Licence

**Apache License 2.0** — voir [LICENSE](LICENSE).

_Copyright © 2026 Abdourahamane Diallo. All rights reserved._

---

_Développé avec ❤️ pour une documentation plus propre, mieux organisée et augmentée par l'IA._
