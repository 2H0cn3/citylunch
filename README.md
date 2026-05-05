# CityLunch

Application Symfony de commande de plats et desserts du jour, avec panier persistant en session.

## Composants nécessaires

- Docker Engine ≥ 24 et Docker Compose v2
- Aucune installation locale de PHP/MySQL/Mongo n'est requise — tout tourne dans des conteneurs.

| Composant | Version |
|-----------|---------|
| PHP       | 8.3 (FPM) avec extensions `intl`, `pdo_mysql`, `zip`, `opcache`, `mongodb` |
| Nginx     | 1.27 (alpine) |
| MySQL     | 8.0     |
| MongoDB   | 7       |
| Symfony   | 7.x     |
| Bootstrap | 5.3 (CDN) |

## Initialisation du projet

```bash
git clone <URL_DU_DEPOT> citylunch && cd citylunch

docker compose build
docker compose up -d
docker compose exec php composer install
docker compose exec php bin/console doctrine:database:create --if-not-exists
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php bin/console doctrine:fixtures:load --no-interaction
```

- Application : http://localhost:8080
- Compte de demo : `demo@citylunch.test` / `demopass123`

## Configuration IDE recommandée

- **PHPStorm** ou **VS Code** avec :
  - Extension *Symfony* (Symfony Support / VSCode Symfony)
  - *PHP Intelephense* (VS Code) ou inspections PHPStorm
  - Extension *Twig*
  - *EditorConfig* (le fichier `.editorconfig` fourni par Symfony fixe l'indentation)
  - *PHP CS Fixer* configuré sur `@Symfony` pour un style homogène

## Pourquoi deux types de bases de données ?

Les deux bases ont des usages distincts ; les confondre dégraderait performances et maintenabilité.

- **MySQL (relationnelle)** stocke les données métier fortement structurées : `Product`, `Customer`, `Order`, `OrderItem`. Schémas stables, relations claires (`Order` → `Customer`, `Order` → `OrderItem`), contraintes d'intégrité, transactions ACID, jointures pour le reporting et la facturation.
- **MongoDB (NoSQL document)** stocke les **sessions HTTP**. Une session est un document éphémère, sans schéma fixe (panier, jeton CSRF, données de profil temporaires), à durée de vie courte, écrit/lu très fréquemment. Le handler `MongoDbSessionHandler` de Symfony gère nativement le TTL et la purge. Mettre les sessions en MySQL polluerait la base métier de I/O et complexifierait la sauvegarde.

En résumé : MySQL pour la **vérité métier durable**, MongoDB pour le **stockage volatile à fort débit**.

## Stratégie de sauvegarde

| Base | Outil | Fréquence | Rétention | Mode |
|------|-------|-----------|-----------|------|
| MySQL | `mysqldump --single-transaction` (ou Percona XtraBackup en prod) | Snapshot quotidien + binlogs continus | 30j quotidiens, 3 mois hebdo, 1 an mensuels | Chiffré (GPG/AES-256), copié hors-site (S3 + copie froide) |
| MongoDB (sessions) | Pas de sauvegarde longue durée nécessaire (données volatiles) | Snapshot du volume tous les jours | 7 jours | Local + 1 copie distante |

Trois principes :
1. **Règle 3-2-1** — 3 copies, 2 supports différents, 1 hors-site.
2. **Restauration testée** — script de restauration dans un environnement isolé tous les mois.
3. **PITR pour MySQL** — l'archivage des binlogs permet une restauration *point-in-time* avec une RPO de quelques minutes.

## Recommandation SSL

L'application gère des **identifiants utilisateur**, des **cookies de session authentifiés** et à terme des **paiements**. HTTPS est donc obligatoire en production sur l'ensemble des routes — pas seulement `/login`.

- **Certificat** : *Let's Encrypt* (DV) via `certbot`, renouvellement auto tous les 90 jours. Suffisant car CityLunch n'a pas besoin de validation organisationnelle (OV/EV).
- **Terminaison TLS** sur le reverse proxy (Nginx ou Traefik), pas dans le conteneur PHP.
- **Configuration durcie** : TLS 1.2 minimum, idéalement 1.3 uniquement, suites Mozilla *intermediate*, HSTS (`Strict-Transport-Security: max-age=63072000; includeSubDomains; preload`).
- **Cookies de session** : `secure: true`, `httpOnly: true`, `SameSite=Lax` (déjà appliqué via `cookie_secure: auto` dans `framework.yaml`).
- **Redirection 301** systématique HTTP → HTTPS au niveau du proxy.
- En dev local : `mkcert` pour un certificat auto-signé reconnu sans warning navigateur.

## Dépôt Git
URL : https://github.com/2H0cn3/citylunch.git
