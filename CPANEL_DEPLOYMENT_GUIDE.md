# TailorPro Enterprise - cPanel & PHP 8.2 Deployment Guide

This guide ensures 100% seamless, secure, and error-free deployment of TailorPro on any cPanel shared hosting server running **PHP 8.2**.

---

## 1. Prerequisites & cPanel Configuration

### Step 1.1: Set PHP Version to 8.2
1. Log into your **cPanel**.
2. Scroll to the **Software** section and click **MultiPHP Manager**.
3. Select your domain name, choose **PHP 8.2** (`ea-php82` or `alt-php82`) from the dropdown, and click **Apply**.

### Step 1.2: Verify PHP Extensions
Ensure the following PHP extensions are enabled in **cPanel > Select PHP Version > Extensions** (or MultiPHP INI):
- `pdo_mysql`
- `mbstring`
- `bcmath`
- `xml` / `dom`
- `curl`
- `zip`
- `fileinfo`
- `zlib` *(enables instant Gzip compression)*

### Step 1.3: Recommended PHP INI Settings
In **MultiPHP INI Editor**, apply the following settings for smooth performance:
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
zlib.output_compression = Off
```
*(Note: Output compression is already handled natively by TailorPro's custom `GzipResponse` middleware).*

---

## 2. Deployment Methods

### Option A: Direct Deployment to `public_html` (Easiest & Most Common)
TailorPro includes pre-configured root [`.htaccess`](file:///.htaccess) and [`index.php`](file:///index.php) files that allow you to deploy directly into `public_html` without modifying folder paths.

1. Create a zip archive of the project (excluding `node_modules` and local test logs).
2. Open **cPanel > File Manager** and navigate into `public_html/`.
3. Upload and extract your project files directly into `public_html/`.
4. The root `.htaccess` will automatically route visitors to the `public/` directory while strictly protecting `.env`, `composer.json`, and sensitive directories from direct web access.

---

### Option B: Separated Directory Deployment (Maximum Security)
For advanced hosting setups where you can configure the domain's document root:

1. Upload the project to `/home/username/tailorpro/` (one level above `public_html`).
2. In **cPanel > Domains**, edit the Document Root of your domain to point to:
   ```
   /home/username/tailorpro/public
   ```

---

## 3. Database Setup

1. In **cPanel > MySQL Databases**:
   - Create a new database (e.g., `cpaneluser_tailorpro`).
   - Create a new MySQL user (e.g., `cpaneluser_dbuser`) with a strong password.
   - Assign the user to the database with **ALL PRIVILEGES**.
2. In your `.env` file on cPanel:
   ```env
   APP_NAME="TailorPro Enterprise"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cpaneluser_tailorpro
   DB_USERNAME=cpaneluser_dbuser
   DB_PASSWORD=YourStrongPasswordHere
   ```
3. If importing an existing database:
   - Go to **cPanel > phpMyAdmin**.
   - Select your database and click **Import**, then upload your SQL file.
   - Alternatively, open **cPanel > Terminal** and run:
     ```bash
     php artisan migrate --force
     ```

---

## 4. Permissions & Caches

### Set Directory Permissions
Ensure the web server has write access to the cache and storage directories:
- `storage/` → `755` (or `775`)
- `storage/logs/` → `755`
- `storage/framework/` → `755`
- `bootstrap/cache/` → `755`

In **cPanel Terminal** (or SSH):
```bash
chmod -R 775 storage bootstrap/cache
```

### Pre-compile Production Caches
Run these commands in Terminal for sub-50ms response times:
```bash
php artisan optimize
```
*(This caches configurations, routes, events, and compiled Blade views).*

---

## 5. Automated Scheduling (Cron Job)

To keep queued jobs and background tasks running smoothly:
1. In cPanel, search for **Cron Jobs**.
2. Under **Add New Cron Job**, select **Once Per Minute** (`* * * * *`).
3. Set the command (adjust path to your cPanel username):
   ```bash
   /usr/local/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
   ```
   *(Or if deployed in `tailorpro/`: `/usr/local/bin/php /home/username/tailorpro/artisan schedule:run >> /dev/null 2>&1`)*

---

## 6. Verification Checklist

| Check | Expected Result |
|---|---|
| PHP Version | `PHP 8.2.x` shown in cPanel MultiPHP |
| Public Access | `https://yourdomain.com` opens login page |
| API Calls | `/api/orders` responds with 200 OK |
| Authorization Header | Login & Sanctum Bearer tokens work without 401 |
| Compression | Response header `Content-Encoding: gzip` present |
| Font & Print | Bengali invoices and cutting slips generate accurately |
