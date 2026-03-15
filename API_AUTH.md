# API Authentication (Laravel Sanctum)

This project uses **Laravel Sanctum** for API token authentication.

## Endpoints

Base URL: `/api` (e.g. `http://localhost:8000/api`)

| Method | Endpoint | Auth | Description |
|--------|----------|------|--------------|
| POST | `/api/auth/register` | No | Register a new user and receive a token |
| POST | `/api/auth/login` | No | Login and receive a token |
| POST | `/api/auth/logout` | Bearer | Revoke current token (logout) |
| GET | `/api/auth/user` | Bearer | Get current user |

## Usage

### 1. Register

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password","password_confirmation":"password"}'
```

**Response (201):**
```json
{
  "message": "User registered successfully.",
  "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
  "token": "1|abc...",
  "token_type": "Bearer"
}
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"john@example.com","password":"password"}'
```

**Response (200):** Same shape as register (user + token).

### 3. Authenticated requests

Send the token in the `Authorization` header:

```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 4. Logout

Revokes the current token so it can no longer be used:

```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Best practices in this setup

- **Form requests**: `RegisterRequest` and `LoginRequest` validate input and return consistent JSON validation errors.
- **Password rules**: Registration uses Laravel’s `Password::defaults()` (min length, etc.); customize in `AppServiceProvider` if needed.
- **Rate limiting**: Auth routes use `throttle:60,1` (60 requests per minute per client).
- **Token storage**: Tokens are hashed in the database; only the plain-text token is returned once at login/register. Store it securely (e.g. secure storage on device or env var in server-side clients).
- **Protected routes**: Use `auth:sanctum` middleware on any route that requires a valid token.

## Protecting other API routes

In `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    // ...
});
```

Clients must send `Authorization: Bearer <token>` for these routes.

## Setup

1. Configure `.env` (database, etc.).
2. Run migrations: `php artisan migrate`
3. Start the server: `php artisan serve`

If you see "could not find driver" when using SQLite, enable the `pdo_sqlite` extension in your PHP config or use MySQL by setting `DB_CONNECTION=mysql` and database credentials in `.env`.
