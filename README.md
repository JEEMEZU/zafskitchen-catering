Zaf's Kitchen Catering
A web-based catering management system that allows customers to browse menus, book catering services, and communicate with the team — all in one place.

Table of Contents

About
Features
Tech Stack
Getting Started

Prerequisites
Installation
Running with Docker


Project Structure
Configuration
Contributing
License

 About
Zaf's Kitchen Catering is a PHP-based web application designed to streamline the catering booking process. Customers can view the menu, make reservations, and receive email confirmations, while admins can manage bookings through a dashboard.

Features

🔐 User Authentication — Sign in with email/password or Google OAuth
📅 Booking System — Reserve catering services with automatic cancellation for expired bookings
📧 Email Notifications — Automated approval and confirmation emails
💬 Chat API — Real-time communication between customers and the team
📊 Admin Dashboard — Manage bookings, users, and events
🖼️ Profile Management — Upload and manage profile pictures/avatars
🍱 Menu Browsing — View available catering menus and packages
🐳 Docker Support — Easy containerized deployment


Tech Stack

Backend: PHP
Database: MySQL
Authentication: Google OAuth 2.0
Email: PHPMailer (via Composer)
Containerization: Docker
Frontend: HTML, CSS, JavaScript


🚀 Getting Started
Prerequisites

PHP 8.0+
MySQL / MariaDB
Composer
Docker (optional, for containerized setup)

Installation

Clone the repository:

bash   git clone https://github.com/JEEMEZU/zafskitchen-catering.git
   cd zafskitchen-catering

Install PHP dependencies:

bash   composer install

Set up the database:

Create a MySQL database
Import the SQL file (if provided)
Update connection.php with your database credentials


Configure Google OAuth:

Update google-oauth-config.php with your Google Client ID and Secret


Configure email settings:

Update mail.php or sendmail.php with your SMTP credentials


Start a local PHP server:

bash   php -S localhost:8000 router.php

Open your browser and navigate to http://localhost:8000

Running with Docker
bashdocker build -t zafskitchen-catering .
docker run -p 8000:80 zafskitchen-catering
Or use the provided startup script:
bashbash docker-start.sh

📁 Project Structure
zafskitchen-catering/
├── Catering_Photos/        # Catering event photos
├── Menu/                   # Menu images and assets
├── authbackground/         # Auth page backgrounds
├── indexbackground/        # Homepage backgrounds
├── logo/                   # Brand logo assets
├── right_image/            # Layout images
├── slide_image/            # Slideshow images
├── auth.php                # Authentication logic
├── connection.php          # Database connection
├── dashboard.php           # Admin dashboard
├── index.php               # Homepage
├── signin.php              # Sign-in page
├── booking/                # Booking management files
├── chat_api.php            # Chat functionality
├── mail.php                # Email handling
├── google-oauth-config.php # Google OAuth setup
├── Dockerfile              # Docker configuration
└── router.php              # URL routing

⚙️ Configuration
Make sure to set the following before running the app:
FileWhat to Configureconnection.phpDatabase host, name, username, passwordgoogle-oauth-config.phpGoogle Client ID & Secretmail.php / sendmail.phpSMTP host, port, username, password

🤝 Contributing
Contributions are welcome! To contribute:

Fork the repository
Create a new branch (git checkout -b feature/your-feature)
Commit your changes (git commit -m 'Add your feature')
Push to the branch (git push origin feature/your-feature)
Open a Pull Request


📄 License
This project is for educational/personal use. All rights reserved © Zaf's Kitchen Catering.
