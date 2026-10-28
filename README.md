# Unshelf

**Plataforma web per compartir llibres entre particulars.** Unshelf permet deixar en préstec i regalar llibres a persones de la mateixa zona, de manera gratuïta i amb entrega en mà.

## Tecnologies

| Capa          | Tecnologia                                                              |
| ------------- | ----------------------------------------------------------------------- |
| Front-end     | Angular (SPA), TypeScript, SCSS                                         |
| Back-end      | Laravel (API REST), PHP 8.4, Laravel Sanctum (autenticació per _token_) |
| Base de dades | MySQL 8.4                                                               |
| Entorn        | Docker i Docker Compose (PHP-FPM, Nginx, MySQL, Adminer, Node)          |

## Estructura del repositori

```
.
├── backend/              API REST (Laravel)
├── frontend/             Aplicació web (Angular)
├── docker/
│   ├── php/Dockerfile    Imatge PHP 8.4 amb Composer i les extensions necessàries
│   └── nginx/            Configuració del servidor web
├── docs/                 Col·lecció de Postman
├── docker-compose.yml    Definició dels serveis
└── .env.example          Variables de Docker Compose
```

---

## Instal·lació (Windows)

### Requisits

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git
- Powershell o CMD

### Passos

**1. Clonar el repositori i crear els fitxers de configuració**

```powershell
git clone https://github.com/jfarrebo/unshelf.git
cd unshelf
copy .env.example .env
copy backend\.env.example backend\.env
```

**2. Configurar el back-end**

Al `backend\.env` hi ha d'haver les linies:

```
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=unshelf
DB_USERNAME=unshelf
DB_PASSWORD=secret

FRONTEND_URL=http://localhost:4200
ADMIN_EMAIL=admin@unshelf.cat
ADMIN_PASSWORD=secret
```

**3. Construir i aixecar els contenidors**

```powershell
docker compose build
docker compose up -d db adminer
```

**4. Instal·lar el back-end**

```powershell
docker compose run --rm app composer install
docker compose up -d app web
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

**5. Aixecar el front-end**

```powershell
docker compose up -d frontend
```

### Adreces

| Servei                  | URL                                                                                            |
| ----------------------- | ---------------------------------------------------------------------------------------------- |
| Aplicació (Angular)     | http://localhost:4200                                                                          |
| API (Laravel)           | http://localhost:8000/api                                                                      |
| Adminer (base de dades) | http://localhost:8080 · servidor `db` · usuari `unshelf` · contrasenya `secret` · BD `unshelf` |

### Usuaris de prova

| Rol           | Correu              | Contrasenya |
| ------------- | ------------------- | ----------- |
| Administrador | `admin@unshelf.cat` | `secret`    |
| Usuari        | `test@unshelf.test` | `password`  |

---

## API

Totes les rutes tenen el prefix `/api` i responen en JSON. Les rutes protegides necessiten la capçalera `Authorization: Bearer <token>`.

| Mètode | Ruta              | Accés         | Descripció                            |
| ------ | ----------------- | ------------- | ------------------------------------- |
| POST   | `/api/register`   | Públic        | Crea un compte i retorna el _token_   |
| POST   | `/api/login`      | Públic        | Inicia la sessió i retorna el _token_ |
| POST   | `/api/logout`     | Usuari        | Tanca la sessió (revoca el _token_)   |
| GET    | `/api/me`         | Usuari        | Dades de l'usuari actual              |
| PUT    | `/api/me`         | Usuari        | Edita el perfil                       |
| GET    | `/api/admin/ping` | Administrador | Comprovació del control de rols       |

### Proves

La col·lecció de Postman amb totes les peticions és a `docs/Unshelf.postman_collection.json`. La variable `base_url` apunta a `http://localhost:8000/api` i el _token_ es desa automàticament en fer el registre o l'inici de sessió.

---

## Llicència

[PENDENT: triar la llicència del codi, p. ex. MIT]
