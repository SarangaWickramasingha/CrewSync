# CrewSync

CrewSync backend — plain PHP + Apache + MySQL.

## Run with Docker (recommended)

1. Install Docker Desktop.
2. (Optional, first run only) Install Composer dependencies:
   `docker compose run --rm backend sh -c "composer install"` — skipped if `backend/vendor` already exists.
3. Start everything:
   ```
   docker compose up -d --build
   ```
4. Backend is at `http://localhost:8080`, MySQL at `localhost:3307`.

Notes:
- The DB is created and seeded automatically from `database/crewsync_db_final.sql`.
- MySQL root password defaults to `root`; override with `MYSQL_ROOT_PASSWORD` in a `.env` file next to `docker-compose.yml`.
- `backend/.env` supplies JWT / mail / other settings. `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `JWT_SECRET` are overridden by Docker (`DB_HOST=db`, port `3306`) via the environment.
- Host ports `8080` / `3307` are chosen to avoid clashing with XAMPP's Apache (`80`) and MySQL (`3306`).

## Without Docker (XAMPP)

- Drop `backend/` into `C:\xampp\htdocs\CrewSync-backend\backend`.
- Create `backend/.env` from `backend/.env.example` and point `DB_HOST` at your MySQL.
- Import `database/crewsync_db_final.sql` into MySQL.