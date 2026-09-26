# Luxe Commerce Platform (Kazi Fashion World)

![Project Status](https://img.shields.io/badge/Status-Completed-success)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?logo=livewire&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?logo=tailwind-css&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-v3-FBA918)

A high-performance, premium e-commerce platform designed for beauty and fashion brands. Built with the powerful TALL stack (Tailwind, Alpine.js, Laravel, Livewire) and managed via a comprehensive Filament Admin dashboard.

## 🚀 Key Features

* **Premium UI/UX:** Mobile-first design with a Glassmorphism aesthetic, smooth transitions, and a global **Dark Mode** toggle.
* **Shoppable Video Reels:** TikTok/Instagram-style interactive video carousel with embedded product links, eager-loaded for instantaneous playback.
* **Real-time Flash Sales:** Dynamic countdown timers for active flash sale campaigns.
* **Advanced Product Management:** Support for complex product variations (colors, sizes), dynamic inventory tracking, and categorized filtering.
* **Optimized Performance:** Extensive use of Laravel Caching (`Cache::remember`), Native Image Lazy Loading, and N+1 query optimization.
* **Filament Admin Dashboard:** A robust backend featuring an integrated file manager, visual order tracking, and centralized store settings management.

## 🛠 Tech Stack

* **Backend:** PHP 8.2, Laravel 11
* **Frontend:** Livewire 3, Alpine.js, Tailwind CSS
* **Admin Panel:** Filament v3
* **Database:** MySQL
* **UI Libraries:** Swiper.js (for 3D Coverflow Carousels)

## ⚙️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/luxe-commerce-platform.git
   cd luxe-commerce-platform
   ```

2. **Install Composer dependencies**
   ```bash
   composer install
   ```

3. **Install NPM dependencies**
   ```bash
   npm install
   npm run build
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure your database credentials in the `.env` file.*

5. **Run Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Link Storage**
   ```bash
   php artisan storage:link
   ```

7. **Serve the Application**
   ```bash
   php artisan serve
   ```

## 🔒 Security & Environment
* **Note:** This repository is sanitized for public viewing. The `.env` file containing sensitive production credentials is appropriately ignored in `.gitignore`, and a clean `.env.example` is provided for setup.

## 📝 License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# laravel-kazifashionworld-ecommerce
