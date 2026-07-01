/**
 * FoodMarket - Главный модуль приложения
 * =======================================
 * Основной управляющий модуль. Инициализирует приложение,
 * загружает товары, управляет уведомлениями и пагинацией.
 * 
 * Что делает:
 * - загружает товары и категории при старте
 * - применяет фильтры и сортировку
 * - управляет пагинацией (50 товаров на странице)
 * - отображает уведомления для пользователя
 */

class App {
    /** Текущее состояние (фильтры, страница) */
    static state = {
        page: 1,
        sortBy: 'name',
        sortOrder: 'asc',
        categoryId: null,
        categoryName: 'Все товары',
    };

    /**
     * Точка входа — вызывается при загрузке страницы
     * и после каждого действия (вход/выход)
     */
    static async init() {
        this.setupEventListeners();
        Auth.updateUI();
        await this.loadCategories();
        await this.loadProducts();
    }

    /**
     * Настраивает обработчики событий на элементы страницы
     */
    static setupEventListeners() {
        // сортировка — применяется сразу при изменении
        const applySort = () => {
            this.state.sortBy = document.getElementById('sortBy').value;
            this.state.sortOrder = document.getElementById('sortOrder').value;
            this.state.page = 1;
            this.loadProducts();
        };
        document.getElementById('sortBy').addEventListener('change', applySort);
        document.getElementById('sortOrder').addEventListener('change', applySort);

        // применение фильтров (поиск + цена)
        const applyFilters = () => {
            this.state.page = 1;
            this.loadProducts();
        };
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        // Enter в полях фильтра тоже применяет
        document.getElementById('searchInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') applyFilters();
        });
        document.getElementById('priceMin').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') applyFilters();
        });
        document.getElementById('priceMax').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') applyFilters();
        });

        // оплата корзины
        document.getElementById('btnCheckout').addEventListener('click', () => {
            Cart.checkout();
        });

        // закрытие модального окна по клику на фон
        document.getElementById('modalOverlay').addEventListener('click', (e) => {
            if (e.target === document.getElementById('modalOverlay')) {
                Auth.closeModal();
            }
        });

        // админские кнопки
        document.getElementById('btnAddProduct').addEventListener('click', () => {
            Admin.showAddProductModal();
        });
        document.getElementById('btnSeedData').addEventListener('click', () => {
            Admin.seedData();
        });

        // слайдер изображений в карточке товара
        document.getElementById('productsGrid').addEventListener('click', (e) => {
            const target = e.target;
            const slider = target.closest('[data-slider]');
            if (!slider) return;
            const urls = slider.dataset.slider.split('||').map(u => decodeURIComponent(u));
            if (urls.length < 2) return;
            let isNext = false, isPrev = false;
            if (target.closest('[data-slider-next]')) isNext = true;
            if (target.closest('[data-slider-prev]')) isPrev = true;
            if (!isNext && !isPrev) return;
            let currentIdx = parseInt(slider.dataset.sliderIdx) || 0;
            if (isNext) currentIdx = (currentIdx + 1) % urls.length;
            if (isPrev) currentIdx = (currentIdx - 1 + urls.length) % urls.length;
            slider.dataset.sliderIdx = currentIdx;
            const imgEl = slider.querySelector('[data-slider-img]');
            if (imgEl) imgEl.src = urls[currentIdx];
            slider.querySelectorAll('[data-slider-dot]').forEach((dot, idx) => {
                dot.classList.toggle('product-card__dot--active', idx === currentIdx);
            });
        });
    }

    static categoryColors = ['#2b7a3a', '#c0392b', '#e67e22', '#8e44ad', '#2980b9', '#16a085'];

    /**
     * Загружает категории с сервера и отображает в боковом меню
     */
    static async loadCategories() {
        try {
            const categories = await Api.get('/categories');

            const list = document.getElementById('categoryList');
            let html = `<li class="${!this.state.categoryId ? 'active' : ''}"
                           onclick="App.filterByCategory(null, 'Все товары')">
                           Все товары
                        </li>`;

            categories.forEach((cat, index) => {
                const color = App.categoryColors[index] || '#666';
                html += `<li class="${this.state.categoryId === cat.id ? 'active' : ''}"
                           onclick="App.filterByCategory(${cat.id}, '${cat.name}')">
                           <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${color};margin-right:6px;"></span>
                           ${cat.name}
                        </li>`;
            });

            list.innerHTML = html;
        } catch (error) {
            this.showNotification('Не удалось загрузить категории', 'error');
        }
    }

    /**
     * Фильтрует товары по категории
     * 
     * @param {number|null} categoryId - ID категории (null = все)
     * @param {string} categoryName - название категории
     */
    static filterByCategory(categoryId, categoryName) {
        this.state.categoryId = categoryId;
        this.state.categoryName = categoryName;
        this.state.page = 1;
        document.getElementById('searchInput').value = '';
        document.getElementById('priceMin').value = '';
        document.getElementById('priceMax').value = '';
        this.loadProducts();

        // обновляем активный класс в меню
        document.querySelectorAll('#categoryList li').forEach((li, index) => {
            // первый li это "Все товары"
            if (index === 0) {
                li.className = !categoryId ? 'active' : '';
            } else {
                li.className = li.textContent.includes(categoryName) ? 'active' : '';
            }
        });
    }

    /**
     * Загружает товары с текущими фильтрами и отображает их
     */
    static async loadProducts() {
        const grid = document.getElementById('productsGrid');
        grid.innerHTML = '<div class="loading">Загрузка товаров...</div>';

        // собираем параметры запроса
        const params = new URLSearchParams({
            page: this.state.page,
            sortBy: this.state.sortBy,
            sortOrder: this.state.sortOrder,
        });

        if (this.state.categoryId) {
            params.set('categoryId', this.state.categoryId);
        }

        const search = document.getElementById('searchInput').value.trim();
        if (search) {
            params.set('search', search);
        }

        const minPrice = document.getElementById('priceMin').value.trim();
        const maxPrice = document.getElementById('priceMax').value.trim();
        if (minPrice) {
            params.set('minPrice', minPrice);
        }
        if (maxPrice) {
            params.set('maxPrice', maxPrice);
        }

        try {
            const data = await Api.get(`/products?${params.toString()}`);

            // обновляем заголовок и количество
            const title = this.state.categoryId
                ? this.state.categoryName
                : 'Все товары';
            document.getElementById('productsTitle').textContent = title;
            document.getElementById('productsCount').textContent =
                `Найдено: ${data.total} шт.`;

            this.renderProducts(data.products || []);
            this.renderPagination(data.page, data.pages);

        } catch (error) {
            grid.innerHTML = `<div class="error">Ошибка загрузки товаров: ${error.message}</div>`;
        }
    }

    static imageColors = ['#2b7a3a', '#c0392b', '#e67e22', '#8e44ad', '#2980b9', '#16a085', '#7f8c8d', '#27ae60'];

    static getImageColor(name) {
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return this.imageColors[Math.abs(hash) % this.imageColors.length];
    }

    static renderImageSlider(productName, images) {
        const primaryIdx = images.findIndex(i => i.is_primary);
        const startIdx = primaryIdx >= 0 ? primaryIdx : 0;
        const data = images.map(i => encodeURIComponent(i.url)).join('||');
        const dots = images.length > 1
            ? `<div class="product-card__dots">${images.map((_, idx) =>
                `<span class="product-card__dot${idx === startIdx ? ' product-card__dot--active' : ''}" data-slider-dot></span>`
              ).join('')}</div>`
            : '';
        const arrows = images.length > 1
            ? `<button class="product-card__arrow product-card__arrow--left" data-slider-prev>&#10094;</button>
               <button class="product-card__arrow product-card__arrow--right" data-slider-next>&#10095;</button>`
            : '';
        return `
            <div class="product-card__image" data-slider='${data}' data-slider-idx='${startIdx}'>
                <img src="${images[startIdx].url}" alt="${productName}" data-slider-img>
                ${arrows}
                ${dots}
            </div>
        `;
    }

    /**
     * Отображает карточки товаров
     */
    static renderProducts(products) {
        const grid = document.getElementById('productsGrid');
        grid.style.display = 'flex';
        grid.style.flexWrap = 'wrap';
        grid.style.gap = '12px';
        grid.style.marginBottom = '20px';

        if (products.length === 0) {
            grid.innerHTML = '<p style="color: var(--color-text-light);">Товары не найдены</p>';
            return;
        }

        let html = '';
        const isAdmin = document.getElementById('adminPanel').style.display === 'block';

        products.forEach(product => {
            const price = Number(product.price);
            const discountedPrice = Number(product.discounted_price);
            const hasDiscount = product.discount_percent > 0;
            const firstLetter = product.name.charAt(0).toUpperCase();
            const bgColor = this.getImageColor(product.name);

            const images = product.images || [];
            const imageHtml = images.length === 0
                ? `<div class="product-card__image" style="background:${bgColor};display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:rgba(255,255,255,0.9);">${firstLetter}</div>`
                : this.renderImageSlider(product.name, images);

            html += `
                <div class="product-card" style="flex:0 0 calc(25% - 9px);min-width:200px;">
                    ${hasDiscount ? `<span class="product-card__discount">-${product.discount_percent}%</span>` : ''}
                    ${imageHtml}
                    <div class="product-card__body">
                        <div class="product-card__name">${product.name}</div>
                        <div class="product-card__meta">Завоз: ${new Date(product.delivery_date).toLocaleDateString('ru-RU')}</div>
                        <div class="product-card__meta">${product.category ? product.category.name : 'Без категории'}</div>
                        <div class="product-card__price">
                            ${hasDiscount ? `<span class="product-card__price--old">${price.toFixed(2)} ₽</span>` : ''}
                            ${discountedPrice.toFixed(2)} ₽
                        </div>
                        <div class="product-card__actions">
                            <input type="number" id="qty_${product.id}" value="1" min="1" max="99">
                            <button class="btn btn--primary btn--small" onclick="Cart.addToCart(${product.id}, parseInt(document.getElementById('qty_${product.id}').value))">В корзину</button>
                            ${isAdmin ? `
                                <button class="btn btn--admin btn--small" onclick="Admin.showDiscountModal(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.discount_percent})">Скидка</button>
                                <button class="btn btn--secondary btn--small" onclick="Admin.showEditModal(${product.id})">Ред.</button>
                                <button class="btn btn--danger btn--small" onclick="Admin.deleteProduct(${product.id})">X</button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
    }

    /**
     * Отображает пагинацию
     * 
     * @param {number} currentPage - текущая страница
     * @param {number} totalPages - всего страниц
     */
    static renderPagination(currentPage, totalPages) {
        const container = document.getElementById('pagination');

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        // кнопка "Назад"
        html += `<button class="pagination__btn" ${currentPage <= 1 ? 'disabled' : ''}
                         onclick="App.goToPage(${currentPage - 1})">← Назад</button>`;

        // номера страниц (показываем не все, если страниц много)
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<button class="pagination__btn" onclick="App.goToPage(1)">1</button>`;
            if (startPage > 2) {
                html += `<span class="pagination__btn" style="cursor: default;">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="pagination__btn ${i === currentPage ? 'pagination__btn--active' : ''}"
                             onclick="App.goToPage(${i})">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="pagination__btn" style="cursor: default;">...</span>`;
            }
            html += `<button class="pagination__btn" onclick="App.goToPage(${totalPages})">${totalPages}</button>`;
        }

        // кнопка "Вперёд"
        html += `<button class="pagination__btn" ${currentPage >= totalPages ? 'disabled' : ''}
                         onclick="App.goToPage(${currentPage + 1})">Вперёд →</button>`;

        container.innerHTML = html;
    }

    /**
     * Переходит на указанную страницу
     * 
     * @param {number} page - номер страницы
     */
    static goToPage(page) {
        this.state.page = page;
        this.loadProducts();
        // плавный скролл к товарам
        document.querySelector('.products-header').scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * Показывает уведомление пользователю
     * 
     * @param {string} message - текст сообщения
     * @param {string} type - тип: 'success', 'error', 'info'
     */
    static showNotification(message, type = 'info') {
        const container = document.getElementById('notifications');

        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button class="notification__close" onclick="this.parentElement.remove()">✕</button>
        `;

        container.prepend(notification);

        // автоматически удаляем через 5 секунд
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
}

// Запускаем приложение после загрузки DOM
document.addEventListener('DOMContentLoaded', () => App.init());
