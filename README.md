# WhatsML Laravel Project Setup

This project is built with [Laravel](https://laravel.com/).  
Follow the steps below to set it up on your local machine or server.

---

## 1. Clone the Repository
```bash
git clone https://github.com/trysoft-team/whatsml.git
cd whatsml
```

---

## 2. Install Dependencies
Install the backend dependencies with Composer:
```bash
composer install
```

---

## 3. Configure Environment
Copy the example environment file and update it with your local configuration:
```bash
cp .env.example .env   # Linux / Mac
# OR
copy .env.example .env  # Windows
```

Edit the `.env` file to match your database and other environment settings.

---

## 4. Generate Application Key
Run the following command to generate an app key:
```bash
php artisan key:generate
```

---
