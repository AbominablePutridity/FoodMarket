/**
 * FoodMarket - API клиент
 * =========================
 * Этот модуль отвечает за все HTTP-запросы к бэкенду.
 * 
 * Что делает:
 * - отправляет GET/POST/PUT/DELETE запросы на /api/
 * - автоматически подставляет JWT токен в заголовки
 * - обрабатывает ошибки и возвращает JSON
 * 
 * Как использовать:
 *   const data = await Api.get('/products');
 *   const result = await Api.post('/cart/add', { productId: 1, quantity: 2 });
 */

class Api {
    /** Базовый URL API */
    static BASE_URL = '/api';

    /**
     * Возвращает JWT токен из localStorage
     */
    static getToken() {
        return localStorage.getItem('foodmarket_token');
    }

    /**
     * Сохраняет JWT токен в localStorage
     */
    static setToken(token) {
        localStorage.setItem('foodmarket_token', token);
    }

    /**
     * Удаляет JWT токен (разлогинивание)
     */
    static removeToken() {
        localStorage.removeItem('foodmarket_token');
    }

    /**
     * Проверяет, авторизован ли пользователь
     */
    static isAuthenticated() {
        return !!this.getToken();
    }

    /**
     * Базовый метод для всех HTTP-запросов
     * 
     * @param {string} method - HTTP метод (GET, POST, PUT, DELETE)
     * @param {string} path - путь относительно /api/ (например '/products')
     * @param {object|null} body - тело запроса (для POST/PUT)
     * @returns {Promise<object>} - ответ сервера в виде JS объекта
     */
    static async request(method, path, body = null) {
        const url = `${this.BASE_URL}${path}`;

        // настраиваем заголовки
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        // если есть токен — добавляем его в заголовок Authorization
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        // настраиваем параметры запроса
        const options = { method, headers };

        // если есть тело запроса — преобразуем в JSON
        if (body !== null) {
            options.body = JSON.stringify(body);
        }

        try {
            // выполняем запрос
            const response = await fetch(url, options);

            // для DELETE может не быть тела
            if (response.status === 204) {
                return { success: true };
            }

            // для чеков (text/plain) — возвращаем текст
            const contentType = response.headers.get('Content-Type') || '';
            if (contentType.includes('text/plain')) {
                const text = await response.text();
                return { text, status: response.status };
            }

            // парсим JSON ответ
            const data = await response.json();

            // если ошибка — выбрасываем исключение
            if (!response.ok) {
                const errorMessage = data.error || data.message || `Ошибка ${response.status}`;
                throw new Error(errorMessage);
            }

            return data;

        } catch (error) {
            // если это не сетевой запрос, а наша ошибка — просто пробрасываем
            if (error.name !== 'TypeError') {
                throw error;
            }
            // иначе — сетевая ошибка
            throw new Error('Не удалось подключиться к серверу');
        }
    }

    /** GET запрос */
    static get(path) {
        return this.request('GET', path);
    }

    /** POST запрос */
    static post(path, body) {
        return this.request('POST', path, body);
    }

    /** PUT запрос */
    static put(path, body) {
        return this.request('PUT', path, body);
    }

    /** DELETE запрос */
    static delete(path) {
        return this.request('DELETE', path);
    }

    /**
     * Загрузка файлов (multipart/form-data)
     *
     * @param {string} path - путь относительно /api/
     * @param {FormData} formData - данные формы с файлами
     * @returns {Promise<object>}
     */
    static async upload(path, formData) {
        const url = `${this.BASE_URL}${path}`;
        const headers = {
            'Accept': 'application/json',
        };
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        const options = { method: 'POST', headers, body: formData };
        try {
            const response = await fetch(url, options);
            const contentType = response.headers.get('Content-Type') || '';
            if (contentType.includes('text/plain')) {
                const text = await response.text();
                return { text, status: response.status };
            }
            const data = await response.json();
            if (!response.ok) {
                const errorMessage = data.error || data.message || `Ошибка ${response.status}`;
                throw new Error(errorMessage);
            }
            return data;
        } catch (error) {
            if (error.name !== 'TypeError') {
                throw error;
            }
            throw new Error('Не удалось подключиться к серверу');
        }
    }
}
