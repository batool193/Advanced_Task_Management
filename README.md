Advanced Task Management API Project
Description
This project is a ** Advanced Task Management System ** built with Laravel 11 that provides a RESTful APIf for managing users, tasks ,comments and attachements. The project follows repository design patterns and incorporates clean code and refactoring principles.The system Has JWT Authentication for (login , logout) .

Key Features:
Users CRUD
Tasks CRUD , Soft Delete with Restore
Automatically change the status of tasks
Use Job Queues to send daily reports to users
Authentication:
Use JWT for API authentication to ensure high security and protection against potential attacks.
Rate Limiting:
Implement request rate limiting to protect the API from attacks like DDoS.
CSRF Protection:
Ensure the API is protected against CSRF attacks.
XSS and SQL Injection Protection:
Use Laravel's built-in protection against XSS and SQL Injection, including sanitizing user inputs before processing them.
Error Handling:
Install Log Viewer package to implement error logging system
Scans a file for viruses using VirusTotal API.
Repository Design Pattern: Implements repositories and services for clean separation of concerns.
Form Requests: Validation is handled by custom form request classes.
API Response Service: Unified responses for API endpoints.
Pagination: Results are paginated for better performance and usability.

Technologies Used:
Laravel 11
PHP
MySQL
XAMPP (for local development environment)
Composer (PHP dependency manager)
Postman Collection: Contains all API requests for easy testing and interaction with the API.
Installation
Prerequisites
Ensure you have the following installed on your machine:

XAMPP: For running MySQL and Apache servers locally.
Composer: For PHP dependency management.
PHP: Required for running Laravel.
MySQL: Database for the project
Postman: Required for testing the requestes.
Steps to Run the Project
Clone the Repository
https://github.com/batool193/Advanced_Task_Management.git
Navigate to the Project Directory
cd Advanced_Task_Management
Install Dependencies
composer install
Create Environment File
cp .env.example .env
Update the .env file with your database configuration (MySQL credentials, database name, etc.).
Run this command to generate JWT Secret
php artisan jwt:secret
Run Migrations
php artisan migrate
Seed the Database
php artisan db:seed
Run the Application
php artisan serve
Interact with the API and test the various endpoints via Postman collection Get the collection from here:
https://documenter.getpostman.com/view/27922320/2sAXxV7WA8