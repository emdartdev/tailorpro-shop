# tailorpro-shop

High-performance, self-hosted enterprise tailoring management system built with **Laravel 12**, **Vue 3 (Composition API)**, and **Tailwind CSS**.

---

## 🚀 Features

- **Order & Customer Management**: Comprehensive workflow for custom tailoring orders, measurements, and delivery statuses.
- **Worker & Staff Accounting**: Worker piece tracking, salary distributions, and entry management.
- **Bangla Typography & Thermal/Standard Printing**: Client-side Hind Siliguri Bengali typography engine for invoices, delivery challans, cutting slips, and worker piece summaries.
- **Bilingual Support**: English & Bengali (Bangla) localization.
- **Offline Resilience**: Service worker support for core offline operational caching.
- **Single-Pass Eloquent Analytics**: Optimized dashboard metrics and composite MySQL indexing for sub-second responses.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+), Laravel Sanctum
- **Frontend**: Vue 3, Tailwind CSS, Lucide Icons, Vite
- **Database**: MySQL 8.x / SQLite (dev)

---

## 📦 Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/emdartdev/tailorpro-shop.git
   cd tailorpro-shop
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Start Local Development Server:**
   ```bash
   # In terminal 1:
   php artisan serve

   # In terminal 2:
   npm run dev
   ```
   *Alternatively, run `start-local.bat` on Windows.*

---

## 📄 License

Proprietary / All rights reserved.
