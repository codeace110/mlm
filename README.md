BackyardDev System (Laravel Blade + Vite)

This is a Multi-Level Marketing (MLM) web application built using Laravel Blade as the templating engine. The project is powered by Vite for asset bundling and uses Composer and NPM for dependency management.

🚀 Features

Clean Laravel Blade templating with htmlboilerplate

Vite for fast asset building

Organized Laravel project structure

Composer & NPM integration

Ready for authentication and expansion

🛠️ Installation
1. Clone the repository
git clone https://github.com/codeace110/mlm.git
cd mlm

2. Install PHP dependencies
composer install

3. Install JavaScript dependencies
npm install

4. Configure environment

Copy .env.example to .env and update with your database and app settings:

cp .env.example .env


Generate the application key:

php artisan key:generate

5. (Optional) Database migration

If you have migrations, run:

php artisan migrate

6. Build and run the project

Start Vite in dev mode:

npm run dev


Serve the Laravel application:

php artisan serve


Now open http://localhost:8000
 🎉

📂 Project Structure

resources/views → Blade templates

resources/js → JavaScript files (handled by Vite)

resources/css → Stylesheets

routes/web.php → Routes

📦 Tech Stack

Laravel (PHP Framework)

Blade (Templating Engine)

Vite (Asset Bundler)

Composer (PHP Dependency Manager)

NPM (JavaScript Dependency Manager)

🤝 Contributing

Contributions are welcome!

Fork the repo

Create your feature branch

Commit changes

Push to branch

Open a Pull Request

📜 License

This project is licensed under the MIT License.