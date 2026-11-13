# Library Management RESTful API

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Authentication](https://img.shields.io/badge/Auth-Laravel_Sanctum-red?style=for-the-badge)](https://laravel.com/docs/sanctum)

A robust, secure, and production-ready RESTful API for managing library resources, book catalogs, categories, and borrowing transactions built with Laravel 12.

---

##  Key Features

1. **Authentication (Laravel Sanctum)**
   - User Registration & Login with Bearer Token generation.
   - Secure Logout (Token Revocation).
   - Protected API routes with unified JSON exception handling.

2. **Category Management (CRUD)**
   - Full CRUD support for book categories.
   - Built-in data integrity: prevents deleting categories that still have associated books.

3. **Book Management (CRUD)**
   - Complete catalog management: Title, Author, ISBN, Stock, and Category association.
   - Dynamic search by title, author, or ISBN.
   - Filter books by category.
   - Active borrowing protection: prevents deleting books that are currently on loan.

4. **Borrowing & Returning System**
   - **Concurrency-Safe Stock Management**: Uses database transactions (`DB::beginTransaction()`) and pessimistic locking (`lockForUpdate()`) to prevent race conditions during book borrowing.
   - **Automatic Stock Adjustments**: Stock decrements by 1 on borrow, and increments by 1 on return.
   - **Validation Rules**: Borrowing date validation (`due_date` must be a future date) and prevention of duplicate returns.
   - **Filterable Transaction History**: View borrowed books with optional status filtering (`borrowed` / `returned`).

5. **Standardized API Response Pattern**
   - Unified JSON structure across all endpoints (`status`, `message`, `data`, and pagination metadata) implemented via `ApiResponse` trait.

---

## Architecture & Database Schema

```
[ users ] 1 --- * [ borrowings ] * --- 1 [ books ] * --- 1 [ categories ]
```

- **`users`**: `id`, `name`, `email`, `password`, `timestamps`
- **`categories`**: `id`, `name`, `description`, `timestamps`
- **`books`**: `id`, `category_id` (FK), `title`, `author`, `isbn`, `stock`, `timestamps`
- **`borrowings`**: `id`, `user_id` (FK), `book_id` (FK), `borrowed_at`, `due_date`, `returned_at`, `status` (`borrowed`, `returned`), `timestamps`

---

## Installation & Local Setup

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/library_management_api.git
cd library_management_api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Generate the application key:
```bash
php artisan key:generate
```

### 4. Database Setup & Migrations
Create your database or use SQLite:
```bash
php artisan migrate
```

### 5. Run the Server
```bash
php artisan serve
```
The API is accessible at: `http://127.0.0.1:8000`

---

##  Postman Collection

A complete, ready-to-test Postman collection is included in this repository:
- **File**: `Library_Management_API.postman_collection.json`

### How to Import to Postman:
1. Open Postman.
2. Click the **Import** button (top-left).
3. Drag & drop the `Library_Management_API.postman_collection.json` file.
4. Set the `Bearer Token` in the Authorization tab from the Register/Login response.

---

##  API Endpoints Reference

Base URL: `http://127.0.0.1:8000/api`

### 1. Authentication
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `POST` | `/register` | Register new user | ❌ |
| `POST` | `/login` | User login and receive token | ❌ |
| `POST` | `/logout` | Invalidate current token | ✅ |

### 2. Categories
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/categories` | List all categories with book count | ✅ |
| `POST` | `/categories` | Create a new category | ✅ |
| `GET` | `/categories/{id}` | Detail of a category with its books | ✅ |
| `PUT` | `/categories/{id}` | Update category | ✅ |
| `DELETE`| `/categories/{id}` | Delete category (safe delete) | ✅ |

### 3. Books
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/books` | List books (supports `?search=` and `?category_id=`) | ✅ |
| `POST` | `/books` | Create a new book | ✅ |
| `GET` | `/books/{id}` | Get detail of a specific book | ✅ |
| `PUT` | `/books/{id}` | Update book details / stock | ✅ |
| `DELETE`| `/books/{id}` | Delete book (safe delete) | ✅ |

### 4. Borrowings & Returns
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/borrowings` | List all borrowings (supports `?status=borrowed`) | ✅ |
| `POST` | `/borrowings` | Borrow a book (requires `book_id`, `due_date`) | ✅ |
| `POST` | `/borrowings/{id}/return` | Return a borrowed book | ✅ |

---

##  Standardized Response Format

### Success Response
```json
{
    "status": "success",
    "message": "Book borrowed successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "book_id": 2,
        "borrowed_at": "2026-11-14",
        "due_date": "2026-11-21",
        "status": "borrowed"
    }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Cannot delete book that is currently being borrowed",
    "data": null
}
```
