//REST API constants for the front and admin pages

// Project base path derived from this script's URL (<base>/front/js/const.js)
const BASE_PATH = (() => {
    const src = document.currentScript ? document.currentScript.src : "";
    const path = src ? new URL(src, window.location.href).pathname : "";
    const index = path.lastIndexOf("/front/js/");
    return index >= 0 ? path.substring(0, index) : "";
})();

const API_BASE = `${BASE_PATH}/index.php?route=`;
const ADMIN_API = `${API_BASE}admin`;

const FRONT_URL = `${BASE_PATH}/index.php`;
const ADMIN_URL = `${BASE_PATH}/admin/index.php`;
const BOOK_DEFAULT_IMAGE = `${BASE_PATH}/front/assets/images/bookDefault.png`;

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
