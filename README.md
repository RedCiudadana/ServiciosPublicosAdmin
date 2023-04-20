# Install
- php8.0 `sudo apt install php8.1 php8.1-xml php8.1-pgsql php8.1-cgi php8.1-intl php8.1-mbstring php8.1-gd`
- postgresql12 ``
- apache2

# Install Symfony CLI
https://symfony.com/download

wget https://get.symfony.com/cli/installer -O - | bash

# Install composer


# Requerimets
https://symfony.com/doc/current/setup.html#technical-requirements

Technical Requirements
Before creating your first Symfony application you must:

Install PHP 8.1 or higher and these PHP extensions (which are installed and enabled by default in most PHP 8 installations): Ctype, iconv, PCRE, Session, SimpleXML, and Tokenizer;
Install Composer, which is used to install PHP packages.

# After install database we need to create labels in graph database
Before create labes we should initialize AGE connection with this queries.
Notes: run this queries in the database application otherwhise age extension is not loadeed correctly.

```
CREATE EXTENSION 'age';
LOAD 'age';
SET search_path = ag_catalog, "$user", public;
```

Create database
```
SELECT * FROM ag_catalog.create_graph('graph_public_services');
```

Create labels in graph database.
```
SELECT * 
FROM cypher('graph_public_services', $$
    CREATE (:PublicService) $$)
as (p agtype);

SELECT * 
FROM cypher('graph_public_services', $$
    CREATE (:Route) $$)
as (p agtype);
```

In postgresql.conf configures this variables:
```
shared_preload_libraries = 'age'
search_path = 'ag_catalog, "$user", public'
```

<VirtualHost *:80>
    ServerAlias 164.92.136.254
    # ServerAlias test.admin.tramites.gob.gt

    DocumentRoot /srv/web-apps/test.admin.tramites.gob.gt/current/public
    DirectoryIndex /index.php
    <Directory /srv/web-apps/test.admin.tramites.gob.gt/current/public>
        AllowOverride all
        Require all granted
        Allow from All

        FallbackResource /index.php
    </Directory>

    <Directory /srv/web-apps/test.admin.tramites.gob.gt/current/public/bundles>
        DirectoryIndex disabled
        FallbackResource disabled
    </Directory>
</VirtualHost>