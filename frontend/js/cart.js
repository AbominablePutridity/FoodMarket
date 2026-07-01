/**
 * FoodMarket - Модуль корзины
 * ============================
 * Управляет корзиной: просмотр, добавление, удаление, оплата.
 * 
 * Что делает:
 * - загружает содержимое корзины с сервера
 * - отображает товары в корзине
 * - добавляет товары через кнопку на карточке
 * - удаляет позиции из корзины
 * - отправляет запрос на оплату
 * - скачивает электронный чек
 */

class Cart {
    /**
     * Загружает корзину с сервера
     */
    static async loadCart() {
        if (!Api.isAuthenticated()) return;

        try {
            const data = await Api.get('/cart');
            this.renderCart(data);
        } catch (error) {
            // если ошибка — просто показываем пустую корзину
            this.renderCart({ items: [], total: '0.00' });
        }
    }

    /**
     * Отображает корзину на странице
     */
    static renderCart(cart) {
        const container = document.getElementById('cartContent');
        const checkoutBtn = document.getElementById('btnCheckout');

        const items = cart.items || [];

        if (items.length === 0) {
            container.innerHTML = '<p>Корзина пуста</p>';
            checkoutBtn.style.display = 'none';
            return;
        }

        checkoutBtn.style.display = 'inline-block';

        let html = '';
        items.forEach(item => {
            html += `
                <div class="cart-item" data-item-id="${item.id}">
                    <div class="cart-item__info">
                        <div class="cart-item__name">${item.product.name}</div>
                        <div class="cart-item__meta">
                            ${Number(item.price_at_time).toFixed(2)} ₽ × ${item.quantity} шт.
                        </div>
                    </div>
                    <div class="cart-item__total">${Number(item.subtotal).toFixed(2)} ₽</div>
                    <button class="btn btn--danger btn--small" onclick="Cart.removeItem(${item.id})">X</button>
                </div>
            `;
        });

        html += `<div class="cart-total">Итого: ${Number(cart.total).toFixed(2)} ₽</div>`;

        container.innerHTML = html;
    }

    /**
     * Добавляет товар в корзину
     * 
     * @param {number} productId - ID товара
     * @param {number} quantity - количество
     */
    static async addToCart(productId, quantity = 1) {
        if (!Api.isAuthenticated()) {
            App.showNotification('Для добавления товаров необходимо войти в систему', 'error');
            return;
        }

        try {
            await Api.post('/cart/add', { productId, quantity });
            App.showNotification('Товар добавлен в корзину', 'success');
            this.loadCart();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    /**
     * Удаляет позицию из корзины
     * 
     * @param {number} itemId - ID позиции
     */
    static async removeItem(itemId) {
        try {
            await Api.delete(`/cart/remove/${itemId}`);
            App.showNotification('Товар удалён из корзины', 'info');
            this.loadCart();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    /**
     * Оплачивает корзину
     */
    static async checkout() {
        try {
            const data = await Api.post('/cart/pay');
            App.showNotification(`Корзина #${data.cartId} оплачена! Чек готов к скачиванию.`, 'success');
            this.loadCart();
            // показываем кнопку для скачивания чека
            this.showReceiptButton(data.cartId);
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    /**
     * Показывает кнопку для скачивания чека после оплаты
     */
    static showReceiptButton(cartId) {
        const container = document.getElementById('cartContent');
        container.innerHTML += `
            <div style="margin-top: 12px; text-align: center;">
                <button class="btn btn--primary" onclick="Cart.downloadReceipt(${cartId})">
                    Скачать электронный чек
                </button>
            </div>
        `;
    }

    /**
     * Скачивает электронный чек как текстовый файл
     */
    static async downloadReceipt(cartId) {
        try {
            const url = `${Api.BASE_URL}/cart/receipt/${cartId}`;
            const token = Api.getToken();

            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'text/plain',
                },
            });

            const text = await response.text();

            if (!response.ok) {
                throw new Error(text || 'Ошибка получения чека');
            }

            // создаём Blob и скачиваем
            const blob = new Blob([text], { type: 'text/plain; charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `receipt_${cartId}.txt`;
            link.click();
            URL.revokeObjectURL(link.href);

            App.showNotification('Чек скачан!', 'success');
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }
}
