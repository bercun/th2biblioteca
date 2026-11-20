# Documentación del proyecto: Biblioteca (Brooklyn Public Library)

Materia: PHP y Base de Datos

Proyecto semestral TH2 - Equipo Vortex (ESI, Montevideo)

---

## 1. Descripción general

Aplicación web de una biblioteca digital. Los usuarios pueden explorar un catálogo de libros organizado por temporadas (invierno, primavera, verano, otoño), buscar libros, "comprar" libros (sin pasarela de pago real: la compra suma puntos de bonus), dejar una valoración de 1 a 5 estrellas sobre los libros que poseen y ver su perfil. Un administrador cuenta con un panel para gestionar el catálogo y consultar usuarios y compras.

**Tecnologías**

| Capa | Tecnología |
|---|---|
| Backend | PHP 8 (tipado estricto, propiedades tipadas, POO) |
| Base de datos | MySQL / MariaDB, acceso con PDO |
| Frontend | HTML5, CSS3 y JavaScript puro (sin frameworks) |
| Dependencias | Ninguna (sin Composer ni npm) |

El frontend se comunica con el backend mediante peticiones `fetch` a una API REST que devuelve JSON.

---

## 2. Requisitos funcionales y cómo se resolvieron

### 2.1 Registro de usuarios

**Requisito:** un visitante puede crear una cuenta.

**Solución:**

- Controlador `controllers/users/register.php`.
- Valida que todos los campos estén completos, que el email sea válido (`filter_var(..., FILTER_VALIDATE_EMAIL)`) y que la contraseña tenga al menos 8 caracteres.
- Comprueba que el email no exista (`RepoUser::emailExists`); si existe responde `409 Conflict`.
- La contraseña **nunca se guarda en texto plano**: se almacena con `password_hash(..., PASSWORD_DEFAULT)`.
- Tras crear el usuario se inicia sesión automáticamente guardando `$_SESSION['user_id']`.
- En BD, la columna `email` es `UNIQUE`, lo que garantiza la unicidad aunque falle la validación en PHP.

### 2.2 Inicio y cierre de sesión

**Requisito:** el usuario puede entrar y salir del sistema; la sesión se mantiene entre páginas.

**Solución:**

- `login.php` busca el usuario por email y verifica la contraseña con `password_verify()`. Si no coincide, devuelve `401` con un mensaje genérico (no revela si falló el email o la contraseña).
- Si es correcto, guarda `$_SESSION['user_id']` y ejecuta `UPDATE users SET login_count = login_count + 1` (contador de "visitas" del perfil).
- `logout.php` vacía `$_SESSION`, elimina la cookie de sesión y llama a `session_destroy()`.
- `current.php` devuelve el usuario de la sesión (con sus libros) o `null`, lo que permite al frontend restaurar la sesión al recargar la página.

### 2.3 Roles (usuario y administrador)

**Requisito:** existen usuarios normales y administradores con distintos permisos.

**Solución:**

- Columna `users.role` de tipo `ENUM('user','admin')`, con valor por defecto `user`.
- El archivo `controllers/admin/require_admin.php` actúa como "guardia": comprueba que haya sesión (`401` si no) y que el rol sea `admin` (`403` si no). Todos los controladores de administración lo incluyen al principio.
- El frontend solo muestra el enlace "Admin" a administradores, pero la seguridad real se aplica en el servidor.

### 2.4 Catálogo por temporadas ("Favorites")

**Requisito:** mostrar los libros agrupados por temporada.

**Solución:**

- La tabla `books_category` guarda las cuatro temporadas y `books.category_id` es una clave foránea hacia ella.
- `RepoBook::listBySeason()` hace un `INNER JOIN` entre `books` y `books_category` y un `LEFT JOIN` con `reviews` para calcular la nota media y la cantidad de reseñas con `AVG()` y `COUNT()` agrupando por libro (`GROUP BY`).
- El controlador `books/favorites.php` valida el parámetro `season` contra una lista blanca (`in_array(..., true)`).
- El frontend pide el catálogo y lo filtra por temporada con radio buttons.

### 2.5 Búsqueda de libros

**Requisito:** buscar por título, autor o ISBN.

**Solución:**

- `RepoBook::search()` usa `LIKE` con parámetros enlazados sobre `title`, `author` e `isbn`.
- Para el ISBN se añade una comparación normalizada: se eliminan guiones y espacios con `REPLACE()` en SQL y con `preg_replace()` en PHP, así `9781250811257` encuentra `978-1-250-81125-7`.
- Se devuelve también la valoración media de cada resultado (`LEFT JOIN` + `AVG`).

### 2.6 Compra de libros y sistema de bonus

**Requisito:** un usuario autenticado puede adquirir un libro una sola vez y acumula bonus.

**Solución:**

- Controlador `books/buy.php`: exige sesión, valida el `bookId`, comprueba que el libro existe (`404`) y que no lo posee ya (`409`).
- La tabla `user_books` representa la relación **muchos a muchos** entre usuarios y libros. Su **clave primaria compuesta** `(user_id, book_id)` impide a nivel de BD que un usuario compre dos veces el mismo libro.
- La fecha se guarda en `purchased_at` (`TIMESTAMP DEFAULT CURRENT_TIMESTAMP`).
- Tras insertar la compra se ejecuta `UPDATE users SET bonus = bonus + :bonus`. Se hace como una operación atómica en SQL (no se lee y reescribe el valor desde PHP), evitando condiciones de carrera.
- Devuelve el nuevo número de libros y el saldo de bonus actualizado.

### 2.7 Reseñas y valoraciones

**Requisito:** solo quien compró un libro puede valorarlo (1 a 5), una valoración por usuario y libro.

**Solución:**

- Controlador `books/review.php`: valida que `rating` esté entre 1 y 5 y verifica con `RepoUserBook::userOwnsBook()` que el usuario posee el libro (`403` en caso contrario).
- En la tabla `reviews`:
  - `UNIQUE KEY (user_id, book_id)` asegura una reseña por usuario y libro.
  - `CHECK (rating BETWEEN 1 AND 5)` valida el rango en la propia BD.
  - `updated_at ... ON UPDATE CURRENT_TIMESTAMP` registra la última modificación.
- `RepoReview::save()` usa `INSERT ... ON DUPLICATE KEY UPDATE` (upsert): si el usuario vuelve a valorar, se actualiza la nota en lugar de fallar.
- `RepoReview::getBookStats()` recalcula media (`ROUND(AVG(rating),1)`) y total para devolverlos al instante.

### 2.8 Perfil de usuario

**Requisito:** el usuario ve sus visitas, bonus, cantidad de libros y la lista de libros comprados con su nota.

**Solución:**

- `RepoUserBook::listOwnedByUser()` combina `user_books`, `books` (`INNER JOIN`) y `reviews` (`LEFT JOIN` por usuario y libro, para traer la nota propia o `NULL` si no hay), ordenado por fecha de compra descendente.

### 2.9 Panel de administración

**Requisito:** el administrador gestiona libros y consulta usuarios y compras.

**Solución:**

| Endpoint | Función |
|---|---|
| `admin/books_list` | Lista de libros con su temporada |
| `admin/books_create` | Alta de libro |
| `admin/books_update` | Edición de libro |
| `admin/books_delete` | Borrado de libro |
| `admin/users_list` | Usuarios con cantidad de compras (`LEFT JOIN` + `COUNT` + `GROUP BY`) |
| `admin/users_purchases` | Detalle de compras de un usuario |

- Alta y edición validan campos obligatorios, que precio y bonus no sean negativos y que la categoría exista (`RepoBookCategory::exists`).
- Si el ISBN está duplicado, la BD lanza una excepción por la restricción `UNIQUE` y el controlador responde `409`.
- El ISBN vacío se guarda como `NULL`; como `UNIQUE` admite múltiples `NULL`, varios libros pueden carecer de ISBN.
- **Subida de imágenes** (`book_image.php`): el formulario se envía como `multipart/form-data`. Se valida el tamaño (máx. 2 MB), el tipo MIME real con `finfo` (no se confía en la extensión; solo JPG, PNG, WEBP y GIF) y se guarda con un nombre único generado (`book_YYYYmmdd_HHMMSS_<aleatorio>.ext`) mediante `move_uploaded_file()`.
- Al borrar un libro, las claves foráneas con `ON DELETE CASCADE` eliminan automáticamente sus compras y reseñas.

---

## 3. Arquitectura PHP

### 3.1 Front controller (`index.php`)

Un único punto de entrada recibe todas las peticiones. Tiene un arreglo `$routes` que asocia cada ruta con su controlador (por ejemplo `books/buy` => `controllers/books/buy.php`). La ruta se toma de `?route=` o de `PATH_INFO`.

- Si la ruta no existe, devuelve `404` en JSON.
- La función `dispatchController()` cambia el directorio de trabajo (`chdir`) al del controlador antes de incluirlo, y lo restaura después; por eso los controladores pueden usar `require_once '../../models/...'`.
- `serveHome()` sirve `front/index.html` ajustando las rutas de recursos con `basePath()` (`config/paths.php`), de modo que la aplicación funciona tanto en la raíz como en una subcarpeta del servidor.

### 3.2 Estructura por capas (MVC simplificado)

```
index.php               Enrutador (front controller)
config/                 Conexión a BD y rutas base
controllers/            Lógica de cada endpoint (reciben, validan, responden JSON)
  users/  books/  admin/
models/                 Entidades y repositorios
  Book, User, Review, UserBook, BookCategory      (entidades)
  RepoBook, RepoUser, RepoReview, RepoUserBook,
  RepoBookCategory                                (acceso a datos)
front/  admin/          Interfaz (HTML, CSS, JavaScript)
bd/                     Scripts SQL
```

- **Entidades:** clases con propiedades tipadas que implementan `JsonSerializable`, de modo que `json_encode()` las convierte directamente en JSON.
- **Repositorios (patrón Repository):** son la única capa que contiene SQL. Los controladores nunca escriben consultas. Cada repositorio recibe un `PDO` opcional por constructor (inyección de dependencias), lo que facilita sustituir la conexión.
- **Controladores:** siempre responden con `Content-Type: application/json` y la estructura `{ "success": bool, ... }`, usando códigos HTTP significativos: 400 (datos inválidos), 401 (sin sesión), 403 (sin permiso), 404 (no encontrado), 409 (conflicto) y 500 (error de servidor).

### 3.3 Conexión a la base de datos (`config/connection.php`)

- Patrón **Singleton** con una variable `static`: se crea una sola conexión PDO por petición.
- Configuración con precedencia: variables de entorno > archivo `config/db.local.php` (ignorado por git, para no subir credenciales) > valores por defecto.
- Opciones PDO: `ERRMODE_EXCEPTION` (los errores lanzan excepciones), `FETCH_ASSOC` y charset `utf8mb4`.
- Si la conexión falla, se registra el error con `error_log` y se responde `500` sin exponer detalles al cliente.

### 3.4 Seguridad aplicada

| Riesgo | Medida |
|---|---|
| Inyección SQL | Sentencias preparadas con parámetros enlazados en todos los repositorios |
| Contraseñas expuestas | `password_hash` / `password_verify` (bcrypt) |
| Acceso no autorizado | Sesiones PHP y guardia `require_admin.php` en el servidor |
| Subida de archivos maliciosos | Validación de tamaño, MIME real con `finfo`, lista blanca de extensiones y nombre generado |
| Valores inválidos | Validación en PHP (`filter_var`, rangos, listas blancas) y restricciones en BD (`CHECK`, `ENUM`, `UNIQUE`) |
| Credenciales en el repositorio | `db.local.php` en `.gitignore` y soporte de variables de entorno |
| Cierre de sesión incompleto | Se vacía `$_SESSION`, se borra la cookie y se destruye la sesión |

---

## 4. Base de datos

### 4.1 Modelo de datos

Base `biblioteca`, charset `utf8mb4`, collation `utf8mb4_unicode_ci` (admite tildes, ñ y caracteres especiales).

```
users (1) ────< user_books >──── (1) books >──── (1) books_category
  │                                  │
  └─────────────< reviews >──────────┘
```

| Tabla | Campos principales | Clave primaria | Claves foráneas / restricciones |
|---|---|---|---|
| `users` | `first_name`, `last_name`, `email`, `password`, `login_count`, `bonus`, `role` | `id` (AUTO_INCREMENT) | `email` UNIQUE; `role` ENUM('user','admin') |
| `books_category` | `season` ENUM('winter','spring','summer','autumn') | `id` | `season` UNIQUE |
| `books` | `title`, `author`, `isbn`, `description`, `category_id`, `image`, `bonus`, `price` DECIMAL(8,2) | `id` | `category_id` -> `books_category(id)` ON DELETE RESTRICT ON UPDATE CASCADE; `isbn` UNIQUE NULL |
| `user_books` | `user_id`, `book_id`, `purchased_at` | `(user_id, book_id)` compuesta | FK a `users` y `books`, ambas ON DELETE CASCADE |
| `reviews` | `user_id`, `book_id`, `rating` TINYINT, `created_at`, `updated_at` | `id` | UNIQUE `(user_id, book_id)`; FK a `users` y `books` con CASCADE; CHECK `rating BETWEEN 1 AND 5` |

### 4.2 Relaciones

- **Categoría -> Libros (1:N):** una temporada tiene muchos libros. Se usa `RESTRICT` para impedir borrar una temporada que tenga libros.
- **Usuarios <-> Libros (N:M):** resuelta con la tabla intermedia `user_books` (compras).
- **Usuarios <-> Libros (reseñas):** otra relación N:M con atributos propios (`rating`), limitada a una reseña por pareja.

### 4.3 Decisiones de diseño

- **Integridad en la propia BD:** además de validar en PHP, se usan `UNIQUE`, `CHECK`, `ENUM`, claves foráneas y `NOT NULL`, de modo que los datos son consistentes aunque se acceda por otra vía.
- **Borrado en cascada:** al eliminar un usuario o un libro se eliminan sus compras y reseñas; así no quedan registros huérfanos.
- **Precio con `DECIMAL(8,2)`:** evita los errores de redondeo de `FLOAT` en valores monetarios.
- **ISBN opcional pero único:** se permite `NULL` (en SQL `NULL` nunca es igual a `NULL`, por lo que no choca con `UNIQUE`).
- **Rutas de imagen portables:** en la BD se guardan rutas relativas (`front/assets/images/...`) y `Book::resolveImage()` añade el prefijo base al enviar al cliente.
- **Promedios calculados, no almacenados:** la nota media se obtiene con `AVG()` en cada consulta, evitando datos redundantes que puedan desincronizarse.

### 4.4 Consultas SQL destacadas

- `JOIN` de varios tipos: `INNER JOIN` (libros + categoría), `LEFT JOIN` (libros sin reseñas siguen apareciendo).
- Agregación: `AVG`, `COUNT`, `ROUND` con `GROUP BY`.
- Búsqueda: `LIKE` y `REPLACE` para normalizar ISBN.
- Upsert: `INSERT ... ON DUPLICATE KEY UPDATE`.
- Actualización atómica: `SET bonus = bonus + :bonus`, `SET login_count = login_count + 1`.

### 4.5 Scripts SQL (`bd/`)

- `biblioteca.sql`: esquema completo actual, 16 libros de ejemplo (4 por temporada) y un administrador de prueba (`admin@test.com`).
- `add_admin_role.sql`, `add_books_isbn.sql`, `add_book_price.sql`, `add_reviews.sql`, `add_user_books_purchased_at.sql`, `migrate_books_category.sql`: migraciones incrementales para bases creadas con versiones anteriores del esquema.

---

## 5. Frontend (resumen)

- **`front/index.html`**: página única con secciones (bienvenida con carrusel, favoritos por temporada, buscador, ayuda) y modales (login, registro, perfil, compra).
- **`front/js/const.js`**: define `BASE_PATH` y el objeto `API` con las URL de todos los endpoints.
- **`register.js`**: sesión, login, registro y compra.
- **`favorites.js` / `search.js`**: catálogo por temporada y búsqueda.
- **`reviews.js`**: estrellas y envío de valoraciones.
- **`utils.js`**: generación de tarjetas de libro y utilidades.
- **`admin/admin.js`**: tablas, formulario de libros (con `FormData` para la imagen) y consulta de usuarios y compras.
- El diseño es adaptable a distintos tamaños de pantalla (`media query.css`).

---

## 6. Flujo completo de ejemplo: compra de un libro

1. El usuario pulsa "Buy" en una tarjeta de libro (JavaScript).
2. `fetch` hace `POST index.php?route=books/buy` con `{ "bookId": 5 }`.
3. `index.php` busca la ruta en `$routes` y ejecuta `controllers/books/buy.php`.
4. El controlador comprueba la sesión, valida el ID, busca el libro (`RepoBook`) y verifica que no se posea (`RepoUserBook`).
5. Se inserta la fila en `user_books` y se suma el bonus en `users`.
6. Se responde JSON con el libro, la cantidad de libros y el bonus actualizado.
7. JavaScript actualiza la interfaz: el botón pasa a "Own" y se actualizan los contadores del perfil.

---

## 7. Limitaciones conocidas y mejoras posibles

- La compra y la suma de bonus son dos sentencias separadas sin **transacción**; lo ideal sería envolverlas en `beginTransaction()` / `commit()`.
- No hay protección CSRF, ni regeneración del ID de sesión al iniciar sesión (`session_regenerate_id`), ni límite de intentos de login.
- Los controladores no restringen el método HTTP (GET/POST).
- Al borrar un libro no se elimina el archivo de portada del disco.
- El pago es simulado: el formulario de tarjeta no se procesa en el servidor.
- No hay pruebas automatizadas; la verificación se hace con `php -l` y pruebas manuales.

---

## 8. Puesta en marcha

1. Importar `bd/biblioteca.sql` en MySQL/MariaDB.
2. Crear `config/db.local.php` (o usar variables de entorno `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`).
3. Desde la raíz del proyecto ejecutar: `php -S localhost:8000`
4. Abrir `http://localhost:8000/` (biblioteca) o `http://localhost:8000/admin/index.php` (administración, usuario de prueba `admin@test.com`).

Se necesitan las extensiones de PHP `pdo_mysql` y `fileinfo`.
