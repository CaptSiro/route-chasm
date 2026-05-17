# RouteChasm v1.1.0

Successor of RoutePass, a PHP Framework

Documentation coming soon. 

In the meantime, check out `index.php` for routing examples, `src/models` for database abstraction, and `src/components` for rendering views.

This library is still in heavy development, and all features are subject to change.

# Installation

## Project setup

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

## Docker

The repo includes a minimal Docker setup for Apache PHP and MySQL.
The .env file might need manual editing.

1. Create local files:

```bash
cp .env.docker .env
cp .htaccess.docker .htaccess
```

2. Start the containers:

```bash
docker compose up --build
```

3. Open the app:

```text
http://localhost:8000
```

Notes:

- MySQL is exposed on `localhost:3306`
- The database schema is initialized automatically from `src/core/sql/001-init.sql`
- The PHP container enables `curl` and configures `curl.cainfo` and `openssl.cafile` to use the system CA bundle for AI HTTPS requests
- The Docker env defaults expect the app to run at the domain in `.env`:

```env
DOMAIN_URL=http://localhost:8080
DATABASE_HOST=db
DATABASE_NAME=routechasm
DATABASE_USER=routechasm
DATABASE_PASSWORD=routechasm
DATABASE_PORT=3306
```

- If you need a fresh database seed, remove the named Docker volume and start again:

```bash
docker compose down -v
docker compose up --build
```

# AI Setup

To use the built-in AI features, set a valid OpenAI key in `.env`:

```env
OPENAI_KEY=your_key_here
OPENAI_MODEL=gpt-4o-mini
```

Then rebuild the app container so the PHP extensions and CA settings are present:

```bash
docker compose up --build
```

## Manual 

### PHP and MySQL (Windows)

The recommended way to host the application locally is by installing Wampserver https://wampserver.aviatechno.net/.
Download the full version of Wampserver, this will include PHP server, MySQL server,
and PHPMyAdmin to manage the MySQL server. While installing the Wampserver you might need
to install Visual C++ Redistributable Packages for your system which can also be found on
the Wampserver website.

After installing and running it, download cacert.pem file, and add the absolute location
to the curl.cainfo field in php.ini file. This will enable the server side AI generation requests.

1. Create local files:

```bash
cp .env.default .env
cp .htaccess.default .htaccess
```

2. Edit the .htaccess.default file depending on your installation location:

```
    ...
    RewriteRule (.*) /<project-or-nothing>/index.php [QSA,END]
    ...
```

3. Copy or rename the .env.default file to .env and edit it. All `PROJECT_*` entries are optional as well as `ADMIN_LOGIN_PASSWORD`:
4. Run the `001-init.sql` file located in `src/core/sql`.
