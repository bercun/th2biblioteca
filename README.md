# th2biblioteca

Aplicación web de biblioteca por temporadas. Los usuarios exploran el catálogo, buscan libros, compran (sin pasarela de pago: la compra suma bonus), y dejan reseñas de los libros que poseen. Un panel de administración permite gestionar libros y consultar usuarios y compras.

El proyecto no tiene dependencias externas: no usa Composer, npm ni ningún paquete. Todo es PHP, JavaScript vanilla, HTML y CSS.

---

## 1. Lenguajes y tecnologías

| Capa | Tecnología | Notas |
|---|---|---|
| Backend | PHP 8.0+ | `declare(strict_types=1)`, propiedades tipadas, `str_starts_with` (requiere 8.0+) |
| Base de datos | MySQL / MariaDB | Acceso con PDO (`pdo_mysql`), charset `utf8mb4` |
| Frontend | JavaScript ES6+ | Sin frameworks; scripts globales cargados con `<script>` |
| Presentación | HTML5 y CSS3 | `front/style.css`, `front/media query.css`, `admin/admin.css` |

Extensiones de PHP necesarias: `pdo_mysql` y `fileinfo` (se usa `finfo` para validar el MIME de las imágenes subidas).

---

## 2. Requisitos

- PHP 8.0 o superior con `pdo_mysql` y `fileinfo` habilitados.
- MySQL 8.0.16+ o MariaDB 10.2+ con soporte de `utf8mb4` (el esquema usa `CHECK`, que MySQL aplica desde 8.0.16).
- Cualquier servidor web con PHP, o el servidor integrado de PHP para desarrollo.

---

## 3. Instalación y ejecución

### 3.1 Base de datos

1. Crear la base de datos importando **`bd/biblioteca.sql`**. Este archivo ya incluye todas las tablas y columnas actuales (roles, ISBN, precio, categorías, fecha de compra y reseñas), además de los 16 libros de ejemplo y un usuario administrador de prueba (`admin@test.com`). Cambia la contraseña de ese usuario en cualquier entorno real.
2. Los archivos `bd/add_*.sql` y `bd/migrate_books_category.sql` son migraciones **para bases de datos antiguas**. No hace falta ejecutarlos sobre una base creada con `biblioteca.sql`.

### 3.2 Configuración de conexión

`config/connection.php` resuelve la configuración con esta precedencia:

1. Variables de entorno: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
2. Archivo local `config/db.local.php` (ignorado por git):

```php
<?php
return [
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'biblioteca',
    'username' => 'root',
    'password' => '',
];
```

3. Valores por defecto: `localhost:3306`, base `biblioteca`, usuario `root`, contraseña vacía.

### 3.3 Ejecución en desarrollo

Desde la raíz del repositorio:

```bash
php -S localhost:8000
```

Después abre:

- Biblioteca pública: `http://localhost:8000/`
- Panel admin: `http://localhost:8000/admin/index.php`
- API: `http://localhost:8000/index.php?route=<ruta>` (también funciona `index.php/<ruta>` si el servidor soporta `PATH_INFO`)

> **No uses `php -S localhost:8000 index.php`.** Al pasar `index.php` como router, el servidor envía todas las peticiones a ese script: los CSS/JS y `admin/index.php` devuelven la portada en lugar de su contenido. Verificado en este repositorio.

Si el proyecto se sirve desde una subcarpeta (por ejemplo `htdocs/biblioteca` en Apache/XAMPP), no hay que cambiar nada: `basePath()` calcula el prefijo a partir de `DOCUMENT_ROOT`.

---

## 4. Estructura del proyecto

```
index.php              Front controller: enrutado de la API y de la portada
config/
  connection.php       Singleton PDO: getConnection()
  paths.php            basePath(): prefijo de URL del proyecto
  db.local.php         Credenciales locales (git-ignored)
controllers/
  users/               register, login, logout, current
  books/               search, favorites, buy, review
  admin/               books_list, books_create, books_update, books_delete,
                       users_list, users_purchases
                       require_admin.php  (guardia de acceso, no es endpoint)
                       book_image.php     (helpers de datos e imagen, no es endpoint)
models/
  Book.php, User.php, Review.php, UserBook.php, BookCategory.php   Entidades
  RepoBook.php, RepoUser.php, RepoReview.php,
  RepoUserBook.php, RepoBookCategory.php                           Repositorios (SQL)
front/
  index.html           Página principal con modales
  js/                  const.js, utils.js, register.js, favorites.js, search.js,
                       reviews.js, index.js, logout.js
  assets/images/       Portadas de libros
  assets/              favicon, iconos, video de cabecera
admin/
  index.php            Panel de administración (HTML)
  admin.js, admin.css
bd/                    Esquema SQL y migraciones
```

---

## 5. Arquitectura

### 5.1 Flujo de una petición

```
Navegador
  │  GET /  ─────────────────────────► index.php ──► serveHome() ──► front/index.html
  │  fetch(API.booksSearch...)  ────► index.php?route=books/search
  │                                      │
  │                                      ├─ resolveRoute()     (PATH_INFO o ?route=)
  │                                      ├─ $routes[$route]    (tabla de rutas)
  │                                      └─ dispatchController()
  │                                             │ chdir(carpeta del controlador)
  │                                             ▼
  │                                      controllers/books/search.php
  │                                             │ session / validación / JSON
  │                                             ▼
  │                                      models/RepoBook.php ──► PDO ──► MySQL
  ◄──────────────── JSON {success, ...} o arreglo/objeto ◄──────────────┘
```

### 5.2 Front controller (`index.php`)

- **`$routes`**: tabla explícita `ruta => archivo`. Un endpoint nuevo no funciona hasta registrarlo aquí.
- **`resolveRoute()`**: toma la ruta de `PATH_INFO`; si no existe, de `?route=`; por defecto `home`.
- **`serveHome()`**: lee `front/index.html` y reemplaza `"/biblioteca/` por `basePath()`. Así las rutas de recursos funcionan en la raíz o en una subcarpeta. Si se sirve el HTML sin pasar por `index.php`, esas rutas `/biblioteca/...` quedan rotas.
- **`dispatchController()`**: hace `chdir()` al directorio del controlador, lo incluye con `require` y restaura el directorio de trabajo. Por eso los controladores usan rutas relativas como `require_once '../../models/RepoBook.php'`.
- Una ruta no registrada devuelve 404 con JSON.

### 5.3 Controladores

Cada controlador es un script que:

1. Envía `Content-Type: application/json; charset=utf-8`.
2. Comprueba la sesión (`$_SESSION['user_id']`) si hace falta.
3. Lee el cuerpo JSON (`php://input`) o los parámetros GET/POST.
4. Valida, llama a los repositorios y responde con `echo json_encode(...)`.

Las respuestas de error usan `http_response_code()` (400, 401, 403, 404, 409, 500) y `{ "success": false, "message": "..." }`.

**Guardias de acceso:**
- `controllers/admin/require_admin.php`: inicia sesión si hace falta, exige `user_id` (401) y que el usuario tenga `role === 'admin'` (403). Todos los controladores de `admin/` lo incluyen al inicio.
- `books/buy.php` y `books/review.php` comprueban la sesión inline (401). `users/current.php` no exige sesión: devuelve `null`.

### 5.4 Modelos y repositorios

- **Entidades** (`Book`, `User`, `Review`, `UserBook`, `BookCategory`): objetos con propiedades públicas tipadas. Implementan `JsonSerializable`, así que `json_encode` las serializa con `toArray()`.
- **Repositorios** (`Repo*`): son la única capa que habla con la base de datos. Reciben un `PDO` opcional en el constructor (por defecto `getConnection()`), usan sentencias preparadas y mapean filas a entidades con `mapRow()`.
- Un repositorio nuevo debe seguir ese patrón: no escribir SQL en los controladores.

### 5.5 Autenticación, sesiones y roles

- Las sesiones son las nativas de PHP. Al registrarse o iniciar sesión se guarda `$_SESSION['user_id']`.
- Contraseñas: `password_hash(..., PASSWORD_DEFAULT)` al registrar, `password_verify()` al iniciar sesión.
- Roles: `users.role` es `ENUM('user','admin')`. El frontend muestra el enlace "Admin" solo si el rol es `admin`, pero la protección real está en `require_admin.php`.
- Al iniciar sesión se incrementa `login_count` (el contador "visitas" del perfil).

### 5.6 Frontend

**Orden de carga** (definido en `front/index.html`, importa mucho):

```
index.js → const.js → utils.js → register.js → reviews.js → favorites.js → search.js → logout.js
```

- `index.js` solo gestiona UI: menús, modales, carrusel y vídeo de cabecera. No usa `API` en su carga.
- **`const.js`** define `BASE_PATH` (calculado desde la URL de su propio `<script>`), `API_BASE`, `API` (URLs de cada endpoint), `ADMIN_URL`, `FRONT_URL` y `BOOK_DEFAULT_IMAGE`. Cualquier llamada `fetch` debe usar `API.*`, no URLs escritas a mano.
- **Estado global** (en `register.js`): `currentUser`, `selectedBookId`, `selectedBookCard`. `reviews.js` y `favorites.js` leen `currentUser`.
- Las tarjetas de libro se generan con `createBookCardElement()` (`utils.js`) con clases `favorites-items`, `buy-before-login` y `own`. La búsqueda y los favoritos reutilizan esas tarjetas.
- Las temporadas son los radio buttons `name="season"` con valores `winter`, `spring`, `summer`, `autumn`. Los favoritos se piden una vez y se filtran en cliente por clase CSS (`hidden`).
- Al cargar la página, `loadCurrentUser()` consulta `users/current` para restaurar la sesión PHP.

### 5.7 Panel de administración (`admin/`)

- `admin/index.php` es HTML estático. Carga `const.js` y `utils.js` desde `../front/js/` y después `admin.js`.
- `initAdmin()` consulta `users/current`; si no hay sesión o el rol no es `admin`, muestra un mensaje de acceso denegado.
- Los libros se crean y actualizan con `FormData` (multipart) para poder subir imagen; el borrado usa JSON.
- El selector de temporada del formulario usa valores fijos `1`–`4` (IDs de `books_category`). Si cambian esos IDs, hay que actualizar también `admin/index.php`.

---

## 6. Base de datos

Tablas principales (`bd/biblioteca.sql`):

| Tabla | Contenido | Relaciones |
|---|---|---|
| `users` | Datos, `password` (hash), `login_count`, `bonus`, `role` | |
| `books_category` | Temporadas: `winter`, `spring`, `summer`, `autumn` (IDs 1–4) | |
| `books` | Título, autor, `isbn` (UNIQUE, nullable), descripción, `image`, `bonus`, `price` | `category_id` → `books_category` (RESTRICT) |
| `user_books` | Compras: `(user_id, book_id)` PK, `purchased_at` | FK a `users` y `books` (CASCADE) |
| `reviews` | Reseñas: `rating` 1–5, una por usuario y libro | FK a `users` y `books` (CASCADE) |

Detalles que conviene conocer:

- El `isbn` puede ser `NULL` en muchos libros (un UNIQUE permite varios `NULL`).
- `books.image` guarda una ruta relativa como `front/assets/images/book1.png`. Las filas de ejemplo usan `../front/...`; el modelo normaliza ambas formas al leer y guardar (`Book::normalizeImage`).
- `reviews` usa `ON DUPLICATE KEY UPDATE`: volver a valorar un libro reemplaza la nota anterior.

---

## 7. Funcionalidades

| Función | Quién | Detalle |
|---|---|---|
| Registro | Visitante | Nombre, apellido, email válido, contraseña de 8+ caracteres. Inicia sesión automáticamente. |
| Inicio de sesión / cierre | Visitante / usuario | Credenciales verificadas con `password_verify`. Logout destruye la sesión y la cookie. |
| Catálogo por temporada | Todos | `books/favorites`, filtrado por temporada en el cliente. |
| Búsqueda | Todos | Por título, autor o ISBN. Si la consulta contiene dígitos, también compara ignorando guiones y espacios del ISBN. |
| Compra | Usuario | Añade el libro a `user_books` y suma su `bonus` al saldo del usuario. No hay pago real. Un libro solo se compra una vez. |
| Reseñas | Usuario con el libro comprado | Nota de 1 a 5. Se muestra la media y el número de reseñas en las tarjetas. |
| Perfil | Usuario | Lista de libros comprados con fecha, nota propia (editable con estrellas) y saldo. |
| Gestión de libros | Admin | Alta, edición, borrado y subida de portada (JPG, PNG, WEBP o GIF, máx. 2 MB). |
| Usuarios y compras | Admin | Lista de usuarios con número de compras y detalle de compras por usuario. |

---

## 8. Referencia de la API

Todas las rutas van en `index.php?route=<ruta>`. Los métodos no están restringidos en el servidor; el frontend usa los indicados aquí.

| Ruta | Método | Sesión | Entrada | Salida |
|---|---|---|---|---|
| `users/register` | POST | No | JSON `firstName, lastName, email, password` | `{success, user}` (200 en éxito; 409 si el email ya existe) |
| `users/login` | POST | No | JSON `email, password` | `{success}`; 401 si las credenciales no coinciden |
| `users/logout` | POST | No | — | `{success, message}` |
| `users/current` | GET | Opcional | — | Objeto del usuario con `books` y `books_count`, o `null` si no hay sesión |
| `books/search` | GET | No | `q` | Array de libros con `average_rating` y `reviews_count` |
| `books/favorites` | GET | No | `season` (opcional, `winter`/`spring`/`summer`/`autumn`) | Array de libros con `season` |
| `books/buy` | POST | Sí | JSON `bookId` | `{success, book, booksCount, bonus}`; 409 si ya se posee |
| `books/review` | POST | Sí | JSON `bookId, rating` (1–5) | `{success, bookId, rating, averageRating, reviewsCount}`; 403 si no se ha comprado |
| `admin/books_list` | GET | Admin | — | `{success, books}` |
| `admin/books_create` | POST | Admin | multipart o JSON: `title, author, isbn, description, categoryId, price, bonus, image` (ruta) o `imageFile` (archivo) | `{success, bookId, image, message}` |
| `admin/books_update` | POST | Admin | Igual que create + `bookId` | `{success, image, message}` |
| `admin/books_delete` | POST | Admin | JSON `bookId` | `{success, message}` |
| `admin/users_list` | GET | Admin | — | `{success, users}` |
| `admin/users_purchases` | GET | Admin | `user_id` | `{success, user, purchases}` |

Los campos de respuesta de `users/*` y `books/*` están en camelCase en algunos casos (`firstName`, `booksCount`, `averageRating`) y en snake_case en otros (`first_name`, `books_count`, `average_rating`). El frontend normaliza ambos (`normalizeUser`, `createBookRatingElement`).

---

## 9. Funciones y piezas clave

### PHP

| Archivo / función | Qué hace |
|---|---|
| `index.php` → `resolveRoute()` | Obtiene la ruta de `PATH_INFO` o `?route=`. |
| `index.php` → `dispatchController()` | Ejecuta el controlador con el directorio de trabajo cambiado para que sus `require` relativos funcionen. |
| `index.php` → `serveHome()` | Sirve la portada y ajusta las rutas `/biblioteca/`. |
| `config/connection.php` → `getConnection()` | PDO singleton con `ERRMODE_EXCEPTION` y `FETCH_ASSOC`. Si falla la conexión responde 500 en JSON. |
| `config/paths.php` → `basePath()` | Prefijo de URL del proyecto calculado desde `DOCUMENT_ROOT`. |
| `controllers/admin/require_admin.php` | Guardia de administrador (401/403). |
| `controllers/admin/book_image.php` → `getAdminBookRequestData()` | Normaliza los datos de alta/edición desde multipart (`$_POST`/`$_FILES['imageFile']`) o JSON. |
| `controllers/admin/book_image.php` → `saveUploadedBookImage()` | Valida tamaño (≤ 2 MB), MIME real con `finfo` y guarda el archivo en `front/assets/images/` con nombre `book_YYYYmmdd_HHMMSS_xxxxxxxx.ext`. |
| `controllers/admin/book_image.php` → `resolveBookImagePath()` | Usa la imagen subida si existe; si no, normaliza la ruta de texto recibida. |
| `controllers/books/buy.php` | Valida sesión, libro y que no esté ya comprado; inserta en `user_books`, suma `bonus` y devuelve contadores. |
| `controllers/books/review.php` | Exige compra previa, guarda la nota (upsert) y devuelve la nueva media. |
| `models/RepoBook.php` → `search()` | Búsqueda `LIKE` sobre título, autor e ISBN, con normalización de ISBN y media de reseñas. |
| `models/RepoBook.php` → `listBySeason()` | Catálogo por temporada, con filtro opcional. |
| `models/RepoBook.php` → `create()` / `update()` / `delete()` | CRUD de libros; `create` devuelve el nuevo ID. |
| `models/RepoUser.php` → `findByEmail()`, `create()`, `addBonus()`, `incrementLoginCount()` | Operaciones de usuario usadas por login, registro y compras. |
| `models/RepoUserBook.php` → `userOwnsBook()`, `purchase()`, `listOwnedByUser()`, `listPurchasesForAdmin()` | Compras y listados de libros del usuario. |
| `models/RepoReview.php` → `save()`, `getBookStats()` | Upsert de reseñas y cálculo de media y número de reseñas. |
| `models/Book.php` → `normalizeImage()` / `resolveImage()` | Guarda rutas relativas sin prefijo en BD y añade `basePath()` al devolverlas al cliente. |

### JavaScript

| Archivo / función | Qué hace |
|---|---|
| `front/js/const.js` → `BASE_PATH`, `API` | Configuración de URLs. Debe cargarse antes que el resto de scripts que usan `API`. |
| `front/js/utils.js` → `createBookCardElement()` | Construye la tarjeta de libro (título, ISBN, bonus, precio, valoración, botones Buy/Own y portada). |
| `front/js/utils.js` → `setContainerMessage()`, `formatDateTime()` | Mensajes de estado y formato de fechas. |
| `front/js/register.js` → `loadCurrentUser()`, `handleLogin()` | Carga la sesión desde `users/current` y actualiza cabecera, contadores y enlace de admin. |
| `front/js/register.js` → `buyBook()` | Envía la compra y actualiza la UI (contadores, bonus, lista de libros). Responde a 401 abriendo el login y a 409 marcando el libro como propio. |
| `front/js/register.js` → `findBookIdForFavoriteCard()` | Recupera el ID por título y autor cuando la tarjeta no lo tiene en `data-book-id`. |
| `front/js/register.js` → `markBookAsOwned()`, `createOwnedBookListItem()` | Cambia Buy por Own y pinta la lista "Mis libros" con estrellas. |
| `front/js/favorites.js` → `loadFavoriteBooks()`, `showSeasonBooks()` | Carga el catálogo completo y muestra solo la temporada seleccionada. |
| `front/js/search.js` → `searchBooks()`, `renderSearchResults()` | Búsqueda y render de resultados, ocultando Buy en libros ya comprados. |
| `front/js/reviews.js` → `createBookRatingElement()`, `createProfileStarRating()` | Muestra la media en las tarjetas y las estrellas en el perfil. |
| `front/js/reviews.js` → `saveBookReview()` | Envía la nota y actualiza la media en todas las tarjetas del libro. |
| `front/js/index.js` | Menús, modales, carrusel de imágenes y reinicio del vídeo de cabecera. |
| `admin/admin.js` → `initAdmin()` | Comprueba el rol y carga libros y usuarios. |
| `admin/admin.js` → `saveBook()` | Construye el `FormData` (incluido `imageFile`) y llama a create o update según `editingBookId`. |
| `admin/admin.js` → `renderBooksTable()`, `loadUsers()`, `loadPurchases()` | Tablas del panel y detalle de compras por usuario. |

---

## 10. Puntos de atención

- **Sin pruebas automatizadas ni linter.** La verificación es `php -l` sobre los archivos modificados.
- **Transacciones:** `buy.php` inserta la compra y después suma el bonus en dos sentencias sin transacción. Si la segunda falla, la compra queda registrada sin bonus.
- **Borrado de libros:** `books_delete.php` borra la fila (y en cascada compras y reseñas), pero no elimina el archivo de portada de `front/assets/images/`.
- **Seguridad pendiente:** no hay token CSRF, no se regenera el ID de sesión al iniciar sesión y no hay límite de intentos de login. Son mejoras necesarias antes de exponer la app en producción.
- **Cambio de temporadas:** los IDs de `books_category` están fijos en el HTML del panel (`1`–`4`).
- **Métodos HTTP sin validar:** ningún controlador comprueba `REQUEST_METHOD`; cualquier método llega a la lógica.
- **Archivo local de configuración:** `config/db.local.php` está en `.gitignore`. No lo subas al repositorio.

---

## 11. Verificación

```powershell
# Sintaxis de un archivo
php -l controllers\books\buy.php

# Sintaxis de todos los PHP
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Para probar los endpoints, arranca `php -S localhost:8000` con la base de datos importada y usa la consola del navegador o `Invoke-RestMethod` (PowerShell).
