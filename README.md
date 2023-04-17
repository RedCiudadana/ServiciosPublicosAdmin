# Install
- php8.0 `sudo apt install php8.1 php8.1-xml php8.1-pgsql php8.1-cgi php8.1-intl php8.1-mbstring`
- postgresql12 ``
- apache2

# Install Symfony CLI
https://symfony.com/download

wget https://get.symfony.com/cli/installer -O - | bash

# Requerimets
https://symfony.com/doc/current/setup.html#technical-requirements

Technical Requirements
Before creating your first Symfony application you must:

Install PHP 8.1 or higher and these PHP extensions (which are installed and enabled by default in most PHP 8 installations): Ctype, iconv, PCRE, Session, SimpleXML, and Tokenizer;
Install Composer, which is used to install PHP packages.

# After install database we need to create labels in graph database
Before create labes we should initialize AGE connection with this queries.

```
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