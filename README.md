# ZCRM Multi

[![Laravel](https://img.shields.io/badge/Laravel-8.0%2B-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

**Wrapper Laravel simple et moderne pour interagir avec l'API Zoho CRM, avec support multi-CRM et auto-refresh des tokens OAuth2.**

## 📋 Sommaire

- [Fonctionnalités](#-fonctionnalités)
- [Installation](#-installation)
- [Prérequis](#-prérequis--créer-une-app-zoho)
- [Ajouter une connexion CRM](#-ajouter-une-connexion-crm)
- [Utilisation](#-utilisation)
  - [Récupérer des données](#récupérer-des-données)
  - [Créer / mettre à jour / supprimer](#créer--mettre-à-jour--supprimer)
  - [Pagination automatique](#récupérer-tous-les-enregistrements-pagination-automatique)
  - [Upload de fichiers](#uploader-un-fichier)
  - [Recherche avec critères](#-recherche-avec-critères)
- [Commandes Artisan](#-artisan-commands-disponibles)
- [Stockage des connexions](#-stockage-des-connexions)
- [Gestion des erreurs](#-gestion-des-erreurs)
- [Roadmap](#-à-venir)
- [Auteur](#-auteur)
- [Licence](#-licence)

## 🚀 Fonctionnalités

- ✅ **Support multi-comptes Zoho CRM** (stockage local SQLite)
- 🔐 **Gestion automatique des tokens OAuth2** (avec refresh)
- 🔄 **API fluide** via façade : `ZCRM::use()->useModule()->getRecords()`
- 📦 **Support des modules standard** (Leads, Contacts, Deals, etc.)
- 📄 **Pagination automatique** avec `getAllRecords()`
- 📎 **Upload de fichiers** avec `uploadFile()`
- 🔍 **Recherche avancée** avec critères ou builder fluide
- 🎯 **Compatible** Laravel 8, 9, 10+

## 🛠 Installation

### 1. Ajouter le dépôt GitHub dans Composer

Ajoute ce dépôt dans ton `composer.json` si ce n’est pas déjà fait :

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/o1oo1o1o-u/ZCRM.git"
  }
]
```

```bash
composer require devreux/zcrm-multi:dev-main
```


### 2. Publier la config

```bash
php artisan vendor:publish --tag=config
```

## ⚙️ Prérequis : créer une app Zoho

Tu dois enregistrer ton app Zoho pour récupérer les identifiants d'API.

📖 [Documentation officielle](https://www.zoho.com/crm/developer/docs/api/register-client.html)

### Étapes :
1. Va sur [Zoho API Console](https://api-console.zoho.com/)
2. Crée une app (Server-based)
3. Définis une URL de redirection (ex: http://localhost/callback)
4. Note les `client_id`, `client_secret`
5. Génére ton `refresh_token` via l'URL OAuth (voir doc)

## ➕ Ajouter une connexion CRM

```bash
php artisan zcrm:add-crm \
  --name=moncrm \
  --client_id=1000.abcxyz \
  --client_secret=xxxxxxxx \
  --refresh_token=1000.xxx.yyy.zzz \
  --region=eu
```

> ⚠️ Si tu n'utilises pas `use('moncrm')`, le premier CRM enregistré est utilisé par défaut.

## ✅ Utilisation

### Récupérer des données

```php
use ZCRM;

$leads = ZCRM::use('moncrm')->useModule('Leads')->getRecords();

$contact = ZCRM::useModule('Contacts')->getRecord('1234567890');
```

### Créer / mettre à jour / supprimer

```php
$newLead = ZCRM::useModule('Leads')->createRecord([
    'First_Name' => 'Ju',
    'Last_Name' => 'Devreux',
    'Email' => 'ju@devreux.fr'
]);

ZCRM::useModule('Deals')->updateRecord('987654321', [
    'Stage' => 'Qualification'
]);

ZCRM::useModule('Leads')->deleteRecord('1234567890');
```

### Récupérer tous les enregistrements (pagination automatique)

```php
$allDeals = ZCRM::useModule('Deals')->getAllRecords();
```

### Uploader un fichier

```php
ZCRM::useModule('Leads')->uploadFile('12345', storage_path('devis.pdf'));
```

## 🔍 Recherche avec critères

En string brute :
```php
ZCRM::useModule('Leads')->findByCriteria('(City:equals:Paris)');
```

Avec le builder fluide :
```php
use ZCRM\Support\ZCRMSearchBuilder;

$criteria = ZCRMSearchBuilder::make()
    ->where('Email', 'starts_with', 'contact@')
    ->andWhere('City', 'equals', 'Lyon');

$results = ZCRM::useModule('Contacts')->findByCriteria($criteria);
```

## 🔧 Artisan commands disponibles

| Commande | Description |
|----------|-------------|
| `zcrm:add-crm` | Ajouter une connexion CRM |
| `zcrm:list-crm` | Lister toutes les connexions CRM enregistrées |
| `zcrm:remove-crm {nom}` | Supprimer une connexion CRM |

## 📦 Stockage des connexions

Les connexions sont stockées dans un fichier SQLite local :

```bash
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
    $lead = ZCRM::useModule('Leads')->getRecord('invalid');
} catch (\ZCRM\Exceptions\ZCRMException $e) {
    logger()->error($e->getMessage());
}
```

## 📚 À venir

- Recherche par email ou téléphone
- Modules personnalisés
- Téléchargement de pièces jointes
- Cache et log optionnels
- Tests unitaires

## 👨‍💻 Auteur

Développé par Ju – Devreux  
Contact : contact@devreux.fr

## 📄 Licence

[MIT](LICENSE)