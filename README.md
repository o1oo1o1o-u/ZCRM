# ZCRM Multi

[![Laravel](https://img.shields.io/badge/Laravel-8.0%2B-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

**Wrapper Laravel simple et moderne pour interagir avec l'API Zoho CRM, avec support multi-CRM et auto-refresh des tokens OAuth2.**

## 📋 Sommaire

- [Fonctionnalités](#-fonctionnalités)
- [Installation](#-installation)
- [Prérequis](#-prérequis--créer-une-app-zoho)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
  - [Sélection du CRM](#sélection-du-crm)
  - [Récupération de données](#récupération-de-données)
  - [Création, mise à jour et suppression](#création-mise-à-jour-et-suppression)
  - [Pagination automatique](#pagination-automatique)
  - [Upload de fichiers](#upload-de-fichiers)
  - [Recherche avancée](#recherche-avancée)
- [Commandes Artisan](#-commandes-artisan)
- [Stockage des connexions](#-stockage-des-connexions)
- [Gestion des erreurs](#-gestion-des-erreurs)
- [Roadmap](#-roadmap)
- [Auteur](#-auteur)
- [Licence](#-licence)

## 🚀 Fonctionnalités

- ✅ **Support multi-comptes Zoho CRM** (stockage local SQLite)
- 🔐 **Gestion automatique des tokens OAuth2** (avec refresh)
- 🔄 **API fluide** via façade : `ZCRM::use('moncrm')->useModule('Leads')->getRecords()`
- 📦 **Support des modules standard** (Leads, Contacts, Deals, etc.)
- 📄 **Pagination automatique** avec `getAllRecords()`
- 📎 **Upload de fichiers** avec `uploadFile()`
- 🔍 **Recherche avancée** avec critères ou builder fluide
- 🎯 **Compatible** Laravel 8, 9, 10+

## 🛠 Installation

### 1. Ajouter le dépôt GitHub dans Composer

Ajoutez ce dépôt dans votre `composer.json` :

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/o1oo1o1o-u/ZCRM.git"
  }
]
```

OU utilisez la commande :

```bash
composer config repositories.zcrm vcs https://github.com/o1oo1o1o-u/ZCRM.git
```

Puis installez le package :

```bash
composer require devreux/zcrm-multi:^1.0
```

### 2. Publier la configuration

```bash
php artisan vendor:publish --tag=config
```

## ⚙️ Prérequis : créer une app Zoho

Vous devez enregistrer votre application Zoho pour récupérer les identifiants d'API.

📖 [Documentation officielle](https://www.zoho.com/crm/developer/docs/api/register-client.html)

### Étapes :
1. Connectez-vous sur [Zoho API Console](https://api-console.zoho.com/)
2. Créez une application de type "Server-based"
3. Définissez une URL de redirection (ex: http://localhost/zcrm/callback)
4. Notez les `client_id` et `client_secret` fournis
5. Configurez les scopes nécessaires (par défaut : `ZohoCRM.modules.ALL`)

## 🔧 Configuration

Pour configurer une connexion CRM :


### Commande `zcrm:init-auth` (assistée)

Cette méthode génère un lien d'autorisation et configure tout automatiquement :

```bash
php artisan zcrm:init-auth \
  --name=moncrm \
  --client_id=1000.abcxyz \
  --client_secret=xxxxxxxx \
  --region=eu
```

La commande générera un lien à ouvrir dans votre navigateur pour autoriser l'application.

> ⚠️ ZCRM garde en mémoire le dernier CRM utilisé via `use('...')`.
> Si aucun n’est défini, le premier CRM enregistré sera utilisé par défaut.

## ✅ Utilisation

### Sélection du CRM

```php
use ZCRM;

// Utiliser un CRM spécifique
$leads = ZCRM::use('moncrm')->useModule('Leads')->getRecords();

// Utiliser le CRM par défaut (premier enregistré)
$contact = ZCRM::useModule('Contacts')->getRecord('1234567890');
```

### Récupération de données

```php
// Récupérer tous les enregistrements d'un module (max 200 par défaut)
$leads = ZCRM::useModule('Leads')->getRecords();

// Récupérer un enregistrement spécifique par ID
$contact = ZCRM::useModule('Contacts')->getRecord('1234567890');

// Récupérer avec options supplémentaires
$leads = ZCRM::useModule('Leads')->getRecords([
    'fields' => 'First_Name,Last_Name,Email,Phone',
    'sort_by' => 'Created_Time',
    'sort_order' => 'desc',
    'per_page' => 100
]);
```

### Création, mise à jour et suppression

```php
// Créer un nouvel enregistrement
$newLead = ZCRM::useModule('Leads')->createRecord([
    'First_Name' => 'Ju',
    'Last_Name' => 'Devreux',
    'Email' => 'ju@devreux.fr'
]);

// Mettre à jour un enregistrement existant
ZCRM::useModule('Deals')->updateRecord('987654321', [
    'Stage' => 'Qualification',
    'Amount' => 15000
]);

// Supprimer un enregistrement
ZCRM::useModule('Leads')->deleteRecord('1234567890');
```

### Pagination automatique

```php
// Récupérer TOUS les enregistrements (gestion auto des pages)
$allDeals = ZCRM::useModule('Deals')->getAllRecords();

// Avec options supplémentaires
$clients = ZCRM::useModule('Contacts')->getAllRecords([
    'fields' => 'First_Name,Last_Name,Email',
    'sort_by' => 'Created_Time'
]);
```

### Upload de fichiers

```php
// Uploader un fichier pour un enregistrement
ZCRM::useModule('Leads')->uploadFile('12345', storage_path('app/devis.pdf'));
```

### Recherche avancée

#### Méthode 1 : Avec une chaîne de critères

```php
$parisiens = ZCRM::useModule('Leads')->findByCriteria('(City:equals:Paris)');
```

#### Méthode 2 : Avec le builder fluide

```php
use ZCRM\Support\ZCRMSearchBuilder;

$criteria = ZCRMSearchBuilder::make()
    ->where('Email', 'starts_with', 'contact@')
    ->andWhere('City', 'equals', 'Lyon');

$results = ZCRM::useModule('Contacts')->findByCriteria($criteria);

// Conditions plus avancées
$criteria = ZCRMSearchBuilder::make()
    ->where('Last_Name', 'equals', 'Durand')
    ->andWhere('Created_Time', 'between', '2023-01-01,2023-12-31')
    ->orWhere('Email', 'contains', 'gmail.com');

$results = ZCRM::useModule('Leads')->findByCriteria($criteria);
```

## 🔧 Commandes Artisan

| Commande | Description | Paramètres |
|----------|-------------|------------|
| `zcrm:add-crm` | Ajouter une connexion CRM | `--name`, `--client_id`, `--client_secret`, `--refresh_token`, `--region` |
| `zcrm:init-auth` | Initialiser OAuth en une étape | `--name`, `--client_id`, `--client_secret`, `--region`, [`--redirect_uri`], [`--scope`] |
| `zcrm:list-crm` | Lister toutes les connexions CRM | - |
| `zcrm:remove-crm` | Supprimer une connexion CRM | `{name}` |

## 📦 Stockage des connexions

Les connexions sont stockées dans un fichier SQLite local :

```
storage/app/zcrm/crm_connections.sqlite
```

Champs enregistrés :
- `name` (clé d'accès)
- `client_id`, `client_secret` 
- `refresh_token`
- `access_token`, `expires_at` (auto-géré)
- `region`, `api_domain`

## ⚠️ Gestion des erreurs

Toutes les erreurs lèvent une exception `ZCRM\Exceptions\ZCRMException`.

```php
try {
    $lead = ZCRM::useModule('Leads')->getRecord('invalid_id');
} catch (\ZCRM\Exceptions\ZCRMException $e) {
    logger()->error('Erreur Zoho CRM: ' . $e->getMessage());
    // Gérer l'erreur...
}
```

## 📚 Roadmap

- [ ] Recherche par email ou téléphone
- [ ] Support des modules personnalisés
- [ ] Téléchargement de pièces jointes
- [ ] Cache et log optionnels
- [ ] Tests unitaires

## 👨‍💻 Auteur

Développé par Ju – Devreux  
Contact : contact@devreux.fr

## 📄 Licence

[MIT](LICENSE)