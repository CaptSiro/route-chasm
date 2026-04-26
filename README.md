# RouteChasm

Successor of RoutePass, a PHP Framework

Documentation coming soon. 

In the meantime, check out `index.php` for routing examples, `src/models` for database abstraction, and `src/components` for rendering views.

This library is still in heavy development, and all features are subject to change.

# Installation

Prerequisite: MySQL and PHP environments is already installed

Either fork the repository or:

```bash
git clone https://github.com/CaptSiro/route-chasm.git <project>
cd <project>
remove-.git-directory
git init
git remote add origin <project-git>
git add .
git commit -m "Initial commit from RouteChasm"
git push -u origin main
```

Edit .htaccess file:

```
<IfModule mod_rewrite.c>
  Options -Indexes
  RewriteEngine on
  RewriteRule (.*) /<project-or-nothing>/index.php [QSA,END]
</IfModule>
```

Edit .env file. All `PROJECT_*` entries are optional as well as `ADMIN_LOGIN_PASSWORD`:

```env
VERSION=1.0.1

PROJECT=RouteChasm
PROJECT_LINK=https://github.com/CaptSiro/route-chasm
PROJECT_AUTHOR=CaptSiro
PROJECT_AUTHOR_LINK=https://github.com/CaptSiro

LANGUAGE=en-US
DOMAIN_URL=http://localhost/route-chasm

DATABASE_HOST=127.0.0.1
DATABASE_NAME=routechasm
DATABASE_USER=root
DATABASE_PASSWORD=

ADMIN_LOGIN_PASSWORD=root

OPENAI_KEY=
OPENAI_MODEL=gpt-4o-mini
```

Run the `init.sql` file located in `src/core/sql`
