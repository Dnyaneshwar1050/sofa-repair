## InfinityFree deployment (React UI + PHP API + MySQL)

Goal: keep your **premium React UI** and replace only the backend.

Architecture:

- React build (static) in `public_html/`
- PHP API router in `public_html/api/`
- MySQL database

React calls the API at **`/api/...`** (same domain), so it works on InfinityFree shared hosting.

### 1) Build React

In `frontend/` run:

- `npm install`
- `npm run build`

This creates `frontend/dist/`.

### 2) Upload files

Upload the **contents** of `frontend/dist/` to InfinityFree `public_html/`.

Then upload these from the repo root to `public_html/` too:

- `api/`
- `config/`
- `.htaccess`

### 2) Create the database

In InfinityFree control panel:

- Create a MySQL database + user
- Open phpMyAdmin
- Import `schema.sql`

### 3) Configure DB connection

Edit `config/database.php`:

- `DB_HOST` (usually `sqlXXX.infinityfree.com` or shown in InfinityFree panel)
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

### 4) Create admin user (one time)

Visit:

- `/admin/create_admin.php`

Create your **phone/email + password**, then **delete `admin/create_admin.php`** after success.

Now login from the React app (it uses `/api/auth/login`).

### 5) Test flows

- Browse services (API: `/api/services`)
- Create booking (API: `/api/bookings`)
- Admin login (API: `/api/auth/login`)

### Notes

- Uses **PDO prepared statements** everywhere (basic SQL injection protection).
- Uses JWT Bearer tokens (Authorization header) to match the existing React app.
- IMPORTANT: set a strong secret in `api/lib/auth.php` (`JWT_SECRET`).

