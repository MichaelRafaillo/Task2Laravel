# Laravel API - User, Project & Timesheet Management

A RESTful API built with Laravel 12 for managing users, projects, and timesheets. This application provides comprehensive CRUD operations with authentication, authorization, and filtering capabilities.

## Features

-   **Authentication & Authorization**

    -   User registration and login
    -   Token-based authentication using Laravel Sanctum
    -   Logout (single device and all devices)
    -   Policy-based authorization for all resources

-   **User Management**

    -   Create, read, update, and delete users
    -   Filter users by first_name, last_name, gender, date_of_birth, email
    -   User profile management

-   **Project Management**

    -   Create, read, update, and delete projects
    -   Filter projects by name, department, status, start_date, end_date
    -   Many-to-many relationship with users (project assignments)

-   **Timesheet Management**

    -   Create, read, update, and delete timesheets
    -   Filter timesheets by user_id, project_id, task_name, date, hours
    -   Track work hours per task and project

-   **Additional Features**
    -   Comprehensive exception handling with clean JSON responses
    -   Request validation
    -   Database factories and seeders
    -   Full test coverage with PHPUnit

## Tech Stack

-   **Framework**: Laravel 12
-   **PHP**: 8.2+
-   **Authentication**: Laravel Sanctum
-   **Database**: MySQL/PostgreSQL/SQLite
-   **Testing**: PHPUnit

## Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   MySQL/PostgreSQL/SQLite
-   Node.js and NPM (for frontend assets if needed)

### Setup Steps

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd Task2Laravel
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Environment configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Configure database**
   Edit `.env` file and set your database credentials:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

5. **Run migrations**

    ```bash
    php artisan migrate
    ```

6. **Seed the database** (Optional but recommended for testing)
    ```bash
    php artisan db:seed
    ```

## Test Credentials

After running the database seeder, you can use the following test account:

### Admin Account

-   **Email**: `admin@example.com`
-   **Password**: `password`

### Additional Test Users

The seeder creates 10 additional users with random data. You can check the database or create new users via the registration endpoint.

## API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_token_here}
```

### Public Endpoints

#### Register User

```http
POST /api/register
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**

```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    ...
  },
  "token": "1|xxxxxxxxxxxxx"
}
```

#### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "first_name": "Admin",
    "last_name": "User",
    "email": "admin@example.com",
    ...
  },
  "token": "1|xxxxxxxxxxxxx"
}
```

### Protected Endpoints

#### Logout (Current Device)

```http
POST /api/logout
Authorization: Bearer {token}
```

#### Logout All Devices

```http
POST /api/logout-all
Authorization: Bearer {token}
```

### User Endpoints

#### Create User

```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith",
  "date_of_birth": "1995-05-15",
  "gender": "female",
  "email": "jane@example.com",
  "password": "password123"
}
```

#### Get All Users

```http
GET /api/users?first_name=John&gender=male&email=john@example.com
Authorization: Bearer {token}
```

#### Get User by ID

```http
GET /api/users/{id}
Authorization: Bearer {token}
```

#### Update User

```http
POST /api/users/update
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1,
  "first_name": "Updated Name",
  "email": "updated@example.com"
}
```

#### Delete User

```http
POST /api/users/delete
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1
}
```

### Project Endpoints

#### Create Project

```http
POST /api/projects
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "New Project",
  "department": "Engineering",
  "start_date": "2024-01-01",
  "end_date": "2024-12-31",
  "status": "active"
}
```

#### Get All Projects

```http
GET /api/projects?name=Project&department=Engineering&status=active
Authorization: Bearer {token}
```

#### Get Project by ID

```http
GET /api/projects/{id}
Authorization: Bearer {token}
```

#### Update Project

```http
POST /api/projects/update
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1,
  "name": "Updated Project Name",
  "status": "completed"
}
```

#### Delete Project

```http
POST /api/projects/delete
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1
}
```

### Timesheet Endpoints

#### Create Timesheet

```http
POST /api/timesheets
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "project_id": 1,
  "task_name": "Task Description",
  "date": "2024-01-01",
  "hours": 8.5
}
```

#### Get All Timesheets

```http
GET /api/timesheets?user_id=1&project_id=1&date=2024-01-01
Authorization: Bearer {token}
```

#### Get Timesheet by ID

```http
GET /api/timesheets/{id}
Authorization: Bearer {token}
```

#### Update Timesheet

```http
POST /api/timesheets/update
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1,
  "hours": 8.0,
  "task_name": "Updated Task"
}
```

#### Delete Timesheet

```http
POST /api/timesheets/delete
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1
}
```

## Validation Rules

### User

-   `first_name`: required, string, max 255
-   `last_name`: required, string, max 255
-   `date_of_birth`: required, date, before today
-   `gender`: required, one of: male, female, other
-   `email`: required, valid email, unique
-   `password`: required, string, min 8 characters

### Project

-   `name`: required, string, max 255
-   `department`: required, string, max 255
-   `start_date`: required, date
-   `end_date`: nullable, date, after or equal to start_date
-   `status`: optional, one of: active, completed, cancelled

### Timesheet

-   `user_id`: required, integer, exists in users table
-   `project_id`: required, integer, exists in projects table
-   `task_name`: required, string, max 255
-   `date`: required, date
-   `hours`: required, numeric, min 0, max 24

## Response Codes

-   `200` - Success
-   `201` - Created
-   `401` - Unauthenticated
-   `403` - Unauthorized
-   `404` - Not Found
-   `422` - Validation Error
-   `500` - Server Error

## Database Seeding

The seeder creates:

-   1 admin user (admin@example.com / password)
-   10 additional users
-   15 projects
-   5-10 timesheets per user

To seed the database:

```bash
php artisan db:seed
```

To refresh and reseed:

```bash
php artisan migrate:fresh --seed
```

## Testing

Run the test suite:

```bash
php artisan test
```

Run specific test file:

```bash
php artisan test tests/Feature/AuthTest.php
```

## Project Structure

```
app/
├── DTOs/              # Data Transfer Objects
├── Http/
│   ├── Controllers/
│   │   └── Api/       # API Controllers
│   ├── Requests/      # Form Request Validation
│   └── Resources/     # API Resources (Response Transformers)
├── Models/            # Eloquent Models
├── Policies/          # Authorization Policies
└── Services/          # Business Logic Layer

database/
├── factories/         # Model Factories
├── migrations/        # Database Migrations
└── seeders/          # Database Seeders

routes/
└── api.php           # API Routes

tests/
└── Feature/          # Feature Tests
```

## Authorization Policies

-   **User Policy**: All authenticated users can view, create, and update any user. Users cannot delete themselves.
-   **Project Policy**: All authenticated users can perform all operations on projects.
-   **Timesheet Policy**: Users can view their own timesheets or timesheets for projects they're assigned to. Users can only update/delete their own timesheets.

## Error Handling

All API errors return clean JSON responses without debug information:

-   Authentication errors: 401 with "Unauthenticated." message
-   Authorization errors: 403 with "This action is unauthorized." message
-   Validation errors: 422 with validation error details
-   Not found errors: 404 with appropriate message
-   Server errors: 500 with generic error message

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
