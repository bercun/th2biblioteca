//REST API constants for the front and admin pages

const API_BASE = "/biblioteca/index.php?route=";
const ADMIN_API = `${API_BASE}admin`;

const FRONT_URL = "/biblioteca/index.php";
const ADMIN_URL = "/biblioteca/admin/index.php";
const BOOK_DEFAULT_IMAGE = "/biblioteca/front/assets/images/bookDefault.png";

const API = {
    register: `${API_BASE}users/register`,
    login: `${API_BASE}users/login`,
    logout: `${API_BASE}users/logout`,
    currentUser: `${API_BASE}users/current`,
    booksSearch: `${API_BASE}books/search`,
    booksFavorites: `${API_BASE}books/favorites`,
    booksBuy: `${API_BASE}books/buy`,
    booksReview: `${API_BASE}books/review`,
    adminBooksList: `${ADMIN_API}/books_list`,
    adminBooksCreate: `${ADMIN_API}/books_create`,
    adminBooksUpdate: `${ADMIN_API}/books_update`,
    adminBooksDelete: `${ADMIN_API}/books_delete`,
    adminUsersList: `${ADMIN_API}/users_list`,
    adminUsersPurchases: `${ADMIN_API}/users_purchases`
};
