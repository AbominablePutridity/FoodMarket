/**
 * FoodMarket - Модуль авторизации
 * ================================
 * Отвечает за регистрацию, вход и выход пользователя.
 * 
 * Что делает:
 * - открывает модальные окна для логина и регистрации
 * - отправляет запросы на /api/auth/register
 * - работает с JWT токенами через Api класс
 * - обновляет интерфейс в зависимости от статуса (гость/пользователь/админ)
 */

class Auth {
    /**
     * Показывает модальное окно входа
     */
    static showLoginModal() {
        const modal = document.getElementById('modalContent');
        modal.innerHTML = `
            <h2>Вход в систему</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" placeholder="user@example.com" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Пароль</label>
                    <input type="password" id="loginPassword" placeholder="Ваш пароль" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn--primary btn--full">Войти</button>
                    <button type="button" class="btn btn--secondary btn--full" onclick="Auth.showRegisterModal()">Регистрация</button>
                </div>
            </form>
        `;
        document.getElementById('modalOverlay').style.display = 'flex';

        // обработчик отправки формы
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.login();
        });
    }

    /**
     * Показывает модальное окно регистрации
     */
    static showRegisterModal() {
        const modal = document.getElementById('modalContent');
        modal.innerHTML = `
            <h2>Регистрация</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label for="regEmail">Email</label>
                    <input type="email" id="regEmail" placeholder="user@example.com" required>
                </div>
                <div class="form-group">
                    <label for="regPassword">Пароль</label>
                    <input type="password" id="regPassword" placeholder="Минимум 6 символов" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="regRole">Роль</label>
                    <select id="regRole">
                        <option value="ROLE_USER">Покупатель</option>
                        <option value="ROLE_ADMIN">Администратор</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn--primary btn--full">Зарегистрироваться</button>
                    <button type="button" class="btn btn--secondary btn--full" onclick="Auth.showLoginModal()">Уже есть аккаунт</button>
                </div>
            </form>
        `;
        document.getElementById('modalOverlay').style.display = 'flex';

        // обработчик отправки формы
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.register();
        });
    }

    /**
     * Отправляет запрос на вход
     * Использует стандартный endpoint /api/auth/login (lexik/jwt-authentication-bundle)
     */
    static async login() {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        try {
            const data = await Api.post('/auth/login', { email, password });
            Api.setToken(data.token);
            this.closeModal();
            App.showNotification('Вы успешно вошли в систему', 'success');
            App.init();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    /**
     * Отправляет запрос на регистрацию
     */
    static async register() {
        const email = document.getElementById('regEmail').value;
        const password = document.getElementById('regPassword').value;
        const role = document.getElementById('regRole').value;

        try {
            const data = await Api.post('/auth/register', { email, password, role });
            Api.setToken(data.token);
            this.closeModal();
            App.showNotification('Регистрация прошла успешно!', 'success');
            App.init();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    /**
     * Выход из системы
     */
    static logout() {
        Api.removeToken();
        App.showNotification('Вы вышли из системы', 'info');
        App.init();
    }

    /**
     * Закрывает модальное окно
     */
    static closeModal() {
        document.getElementById('modalOverlay').style.display = 'none';
    }

    /**
     * Обновляет интерфейс в соответствии с состоянием авторизации
     */
    static updateUI() {
        const nav = document.getElementById('authNav');
        const isAuth = Api.isAuthenticated();

        if (!isAuth) {
            // пользователь не авторизован — показываем кнопки входа/регистрации
            nav.innerHTML = `
                <button class="btn btn--primary" onclick="Auth.showLoginModal()">Войти</button>
                <button class="btn btn--secondary" onclick="Auth.showRegisterModal()">Регистрация</button>
            `;
            document.getElementById('cartSection').style.display = 'none';
            document.getElementById('adminPanel').style.display = 'none';
        } else {
            // пользователь авторизован — показываем email и кнопку выхода
            const email = JSON.parse(atob(Api.getToken().split('.')[1])).email || 'Пользователь';
            nav.innerHTML = `
                <span class="header__user">${email}</span>
                <button class="btn btn--secondary btn--small" onclick="Auth.logout()">Выйти</button>
            `;

            // показываем корзину
            document.getElementById('cartSection').style.display = 'block';
            Cart.loadCart();

            // проверяем, админ ли пользователь
            this.checkAdmin();
        }
    }

    /**
     * Проверяет, является ли текущий пользователь администратором
     * и показывает панель администрирования
     */
    static checkAdmin() {
        try {
            const payload = JSON.parse(atob(Api.getToken().split('.')[1]));
            const roles = payload.roles || [];
            if (roles.includes('ROLE_ADMIN')) {
                document.getElementById('adminPanel').style.display = 'block';
            }
        } catch (e) {
            // если токен невалидный — игнорируем
        }
    }
}
