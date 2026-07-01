class Admin {
    static async showAddProductModal() {
        await this.loadCategoriesForModal();
    }

    static async loadCategoriesForModal(selectedId = null, product = null) {
        try {
            const categories = await Api.get('/categories');
            const modal = document.getElementById('modalContent');
            const isEdit = product !== null;

            let optionsHtml = '<option value="">Выберите категорию</option>';
            categories.forEach(cat => {
                const sel = selectedId == cat.id ? 'selected' : '';
                optionsHtml += `<option value="${cat.id}" ${sel}>${cat.name}</option>`;
            });

            const imagesHtml = isEdit ? this.renderImageGallery(product.images || []) : '';

            modal.innerHTML = `
                <h2>${isEdit ? 'Редактировать товар' : 'Добавить новый товар'}</h2>
                <form id="productForm">
                    <div class="form-group">
                        <label for="productName">Название товара</label>
                        <input type="text" id="productName" value="${isEdit ? product.name.replace(/"/g, '&quot;') : ''}" placeholder="Молоко 3.2%" required>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Цена (₽)</label>
                        <input type="number" id="productPrice" step="0.01" value="${isEdit ? product.price : ''}" placeholder="89.90" required>
                    </div>
                    <div class="form-group">
                        <label for="productDeliveryDate">Дата завоза</label>
                        <input type="date" id="productDeliveryDate" value="${isEdit ? product.delivery_date?.split('T')[0] || product.delivery_date : ''}" required>
                    </div>
                    <div class="form-group">
                        <label for="productCategory">Категория</label>
                        <select id="productCategory" required>${optionsHtml}</select>
                    </div>
                    <div class="form-group">
                        <label>Фотографии товара</label>
                        ${imagesHtml}
                        <div class="image-upload-area">
                            <input type="file" id="productImages" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                            <small class="image-upload-hint">Можно выбрать несколько файлов (JPEG, PNG, GIF, WebP)</small>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn--admin">${isEdit ? 'Сохранить' : 'Создать товар'}</button>
                        <button type="button" class="btn btn--secondary" onclick="Auth.closeModal()">Отмена</button>
                    </div>
                </form>
            `;
            document.getElementById('modalOverlay').style.display = 'flex';

            if (!isEdit) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                document.getElementById('productDeliveryDate').value =
                    tomorrow.toISOString().split('T')[0];
            }

            document.getElementById('productForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                if (isEdit) {
                    await this.updateProduct(product.id);
                } else {
                    await this.createProduct();
                }
            });

            if (isEdit) {
                document.querySelectorAll('.image-delete-btn').forEach(btn => {
                    btn.addEventListener('click', () => this.deleteImage(product.id, btn.dataset.imageId));
                });
                document.querySelectorAll('.image-primary-btn').forEach(btn => {
                    btn.addEventListener('click', () => this.setPrimaryImage(product.id, btn.dataset.imageId));
                });
            }

        } catch (error) {
            App.showNotification('Не удалось загрузить категории: ' + error.message, 'error');
        }
    }

    static renderImageGallery(images) {
        if (!images || images.length === 0) return '';
        let html = '<div class="image-gallery">';
        images.forEach(img => {
            const isPrimary = img.is_primary;
            html += `
                <div class="image-gallery__item${isPrimary ? ' image-gallery__item--primary' : ''}">
                    <img src="${img.url}" alt="${img.original_name}" class="image-gallery__thumb">
                    <div class="image-gallery__overlay">
                        ${isPrimary ? '<span class="image-gallery__badge">Основное</span>' : `<button type="button" class="image-primary-btn" data-image-id="${img.id}" title="Сделать основным">★</button>`}
                        <button type="button" class="image-delete-btn" data-image-id="${img.id}" title="Удалить">✕</button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    static async deleteImage(productId, imageId) {
        if (!confirm('Удалить это изображение?')) return;
        try {
            const result = await Api.delete(`/admin/products/${productId}/images/${imageId}`);
            App.showNotification('Изображение удалено', 'success');
            await this.loadCategoriesForModal(
                result.product.category?.id || null,
                result.product
            );
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static async setPrimaryImage(productId, imageId) {
        try {
            const result = await Api.put(`/admin/products/${productId}/images/${imageId}/primary`, {});
            App.showNotification('Основное изображение изменено', 'success');
            await this.loadCategoriesForModal(
                result.product.category?.id || null,
                result.product
            );
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static async createProduct() {
        const data = {
            name: document.getElementById('productName').value,
            price: document.getElementById('productPrice').value,
            deliveryDate: document.getElementById('productDeliveryDate').value,
            categoryId: parseInt(document.getElementById('productCategory').value),
        };

        try {
            const result = await Api.post('/admin/products', data);
            const productId = result.product.id;

            const fileInput = document.getElementById('productImages');
            if (fileInput && fileInput.files.length > 0) {
                const formData = new FormData();
                for (const file of fileInput.files) {
                    formData.append('images[]', file);
                }
                await Api.upload(`/admin/products/${productId}/images`, formData);
            }

            Auth.closeModal();
            App.showNotification('Товар создан!', 'success');
            App.loadProducts();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static async showEditModal(productId) {
        try {
            const product = await Api.get(`/products/${productId}`);
            const catId = product.category ? product.category.id : null;
            await this.loadCategoriesForModal(catId, product);
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static async updateProduct(productId) {
        const data = {
            name: document.getElementById('productName').value,
            price: document.getElementById('productPrice').value,
            deliveryDate: document.getElementById('productDeliveryDate').value,
            categoryId: parseInt(document.getElementById('productCategory').value),
        };

        try {
            await Api.put(`/admin/products/${productId}`, data);

            const fileInput = document.getElementById('productImages');
            if (fileInput && fileInput.files.length > 0) {
                const formData = new FormData();
                for (const file of fileInput.files) {
                    formData.append('images[]', file);
                }
                await Api.upload(`/admin/products/${productId}/images`, formData);
            }

            Auth.closeModal();
            App.showNotification('Товар обновлён!', 'success');
            App.loadProducts();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static async deleteProduct(productId) {
        if (!confirm('Удалить этот товар?')) return;

        try {
            await Api.delete(`/admin/products/${productId}`);
            App.showNotification('Товар удалён', 'success');
            App.loadProducts();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static showDiscountModal(productId, productName, currentDiscount = 0) {
        const modal = document.getElementById('modalContent');
        modal.innerHTML = `
            <h2>Назначить скидку</h2>
            <p>Товар: <strong>${productName}</strong></p>
            <form id="discountForm">
                <div class="form-group">
                    <label for="discountPercent">Скидка (%)</label>
                    <input type="number" id="discountPercent" min="0" max="100"
                           value="${currentDiscount}" placeholder="От 0 до 100" required>
                    <small style="color: var(--color-text-light);">0 — убрать скидку</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn--admin">Сохранить</button>
                    <button type="button" class="btn btn--secondary" onclick="Auth.closeModal()">Отмена</button>
                </div>
            </form>
        `;
        document.getElementById('modalOverlay').style.display = 'flex';

        document.getElementById('discountForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.setDiscount(productId);
        });
    }

    static async setDiscount(productId) {
        const discountPercent = parseInt(document.getElementById('discountPercent').value);

        try {
            await Api.post(`/admin/products/${productId}/discount`, { discountPercent });
            Auth.closeModal();
            App.showNotification(
                discountPercent > 0 ? `Скидка ${discountPercent}% назначена!` : 'Скидка убрана',
                'success'
            );
            App.loadProducts();
        } catch (error) {
            App.showNotification(error.message, 'error');
        }
    }

    static async seedData() {
        App.showNotification(
            'Выполните: docker exec foodmarket-php php /app/bin/console app:seed',
            'info'
        );
    }
}
