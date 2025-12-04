class OrderTable {
    constructor() {
        this.table = document.getElementById('ordersTable');
        this.filterInput = document.getElementById('filterInput');
        this.sortLinks = document.querySelectorAll('[data-sort]');
        this.pagination = document.getElementById('pagination');
        this.statsContainer = document.getElementById('statsContainer');
        this.loadingIndicator = document.getElementById('loadingIndicator');

        this.currentFilters = {
            search: '',
            sort: 'orders.id',
            order: 'DESC',
            page: 1
        };

        this.debounceTimeout = null;
        this.init();
    }

    init() {
        this.loadData();

        if (this.filterInput) {
            this.filterInput.addEventListener('input', (e) => {
                this.currentFilters.search = e.target.value;
                this.currentFilters.page = 1;
                this.debounceLoad();
            });
        }

        if (this.sortLinks) {
            this.sortLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleSortClick(link);
                });
            });
        }

        if (this.pagination) {
            this.pagination.addEventListener('click', (e) => {
                if (e.target.classList.contains('page-link')) {
                    e.preventDefault();
                    const page = e.target.dataset.page;
                    if (page) {
                        this.currentFilters.page = parseInt(page);
                        this.loadData();
                    }
                }
            });
        }
    }

    async loadData() {
        this.showLoading(true);

        try {
            const queryString = new URLSearchParams(this.currentFilters).toString();
            const response = await fetch(`/api/orders.php?${queryString}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                this.renderTable(data.data.orders);
                this.renderPagination(data.data.pagination);
                this.renderStats(data.data.stats);
                this.updateURL();
                this.saveState();
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Error loading data:', error);
            this.showError(error.message);
        } finally {
            this.showLoading(false);
        }
    }

    renderTable(orders) {
        if (!this.table) return;

        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        if (orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p class="mt-2">Заказы не найдены</p>
                            ${this.currentFilters.search ? 
                                '<small>Попробуйте изменить поисковый запрос</small>' : 
                                '<small>Добавьте первый заказ</small>'}
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr>
                <td>${order.id}</td>
                <td>${this.escapeHtml(order.title)}</td>
                <td class="price">${order.formatted_cost}</td>
                <td>
                    <div class="customer">
                        <span>${this.escapeHtml(order.user.name)}</span>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(pagination) {
        if (!this.pagination) return;

        if (pagination.last_page <= 1) {
            this.pagination.innerHTML = '';
            return;
        }

        const paginator = new Paginator(
            pagination.total,
            pagination.per_page,
            pagination.current_page,
            pagination.last_page
        );

        this.pagination.innerHTML = `
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item ${paginator.hasPrevious() ? '' : 'disabled'}">
                        <a class="page-link" href="#" data-page="${pagination.current_page - 1}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    ${paginator.getLinks().map(link => `
                        <li class="page-item ${link.active ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${link.page}">
                                ${link.page}
                            </a>
                        </li>
                    `).join('')}

                    <li class="page-item ${paginator.hasNext() ? '' : 'disabled'}">
                        <a class="page-link" href="#" data-page="${pagination.current_page + 1}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted mt-2">
                Показано ${pagination.from}-${pagination.to} из ${pagination.total}
            </div>
        `;
    }

    renderStats(stats) {
        if (!this.statsContainer || !stats) return;

        this.statsContainer.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value">${stats.total_users || 0}</div>
                        <div class="stat-label">Клиентов</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="bi bi-cart"></i>
                        </div>
                        <div class="stat-value">${stats.total_orders || 0}</div>
                        <div class="stat-label">Заказов</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="bi bi-currency-exchange"></i>
                        </div>
                        <div class="stat-value">${this.formatCurrency(stats.total_revenue || 0)}</div>
                        <div class="stat-label">Общая сумма</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon avg">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="stat-value">${this.formatCurrency(stats.avg_order_value || 0)}</div>
                        <div class="stat-label">Средний заказ</div>
                    </div>
                </div>
            </div>
        `;
    }

    handleSortClick(link) {
        const sortBy = link.dataset.sort;
        const currentOrder = this.currentFilters.order;

        if (this.currentFilters.sort === sortBy) {
            this.currentFilters.order = currentOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            this.currentFilters.sort = sortBy;
            this.currentFilters.order = 'ASC';
        }

        this.loadData();

        this.updateSortIcons();
    }

    updateSortIcons() {
        this.sortLinks.forEach(link => {
            const icon = link.querySelector('.sort-icon');
            if (!icon) return;

            if (link.dataset.sort === this.currentFilters.sort) {
                icon.className = `sort-icon bi bi-chevron-${this.currentFilters.order === 'ASC' ? 'up' : 'down'}`;
            } else {
                icon.className = 'sort-icon bi bi-chevron-expand';
            }
        });
    }

    debounceLoad() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => this.loadData(), 300);
    }

    showLoading(show) {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = show ? 'block' : 'none';
        }
        if (this.table) {
            this.table.style.opacity = show ? '0.5' : '1';
        }
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.querySelector('.container').prepend(errorDiv);

        setTimeout(() => errorDiv.remove(), 5000);
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
    }

    updateURL() {
        const url = new URL(window.location);
        Object.entries(this.currentFilters).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
    }

    saveState() {
        localStorage.setItem('orderTableState', JSON.stringify(this.currentFilters));
    }

    loadState() {
        const saved = localStorage.getItem('orderTableState');
        if (saved) {
            this.currentFilters = { ...this.currentFilters, ...JSON.parse(saved) };
            if (this.filterInput && this.currentFilters.search) {
                this.filterInput.value = this.currentFilters.search;
            }
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getInitials(name) {
        return name.split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'BYN',
            minimumFractionDigits: 0
        }).format(amount);
    }
}

class Paginator {
    constructor(total, perPage, currentPage, lastPage) {
        this.total = total;
        this.perPage = perPage;
        this.currentPage = currentPage;
        this.lastPage = lastPage;
    }

    hasPrevious() {
        return this.currentPage > 1;
    }

    hasNext() {
        return this.currentPage < this.lastPage;
    }

    getLinks(maxLinks = 5) {
        const links = [];
        let start = Math.max(1, this.currentPage - Math.floor(maxLinks / 2));
        let end = Math.min(this.lastPage, start + maxLinks - 1);

        if (end - start + 1 < maxLinks) {
            start = Math.max(1, end - maxLinks + 1);
        }

        for (let i = start; i <= end; i++) {
            links.push({
                page: i,
                active: i === this.currentPage
            });
        }

        return links;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const orderTable = new OrderTable();
    window.orderTable = orderTable;
});