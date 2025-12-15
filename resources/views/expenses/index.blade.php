@extends('layouts.app')

@section('title', 'Expenses')

@section('header-buttons')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="fas fa-plus me-1"></i> Add Expense
    </button>
@endsection

@section('content')
<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    <!-- Categories will be loaded by JS -->
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Expenses Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Expenses</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="expenses-table">
                    <tr><td colspan="5" class="text-center">Loading expenses...</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav id="pagination" class="mt-4">
            <!-- Pagination will be loaded here -->
        </nav>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required id="categorySelect">
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let categories = [];
    let currentPage = 1;
    
    // Load initial data
    loadCategories();
    loadExpenses();
    
    // Form submissions
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadExpenses(1);
    });
    
    document.getElementById('addExpenseForm').addEventListener('submit', addExpense);
    
    function loadCategories() {
        fetch('/categories')
            .then(response => response.json())
            .then(data => {
                categories = data.categories || [];
                updateCategoryDropdowns();
            })
            .catch(error => console.error('Error loading categories:', error));
    }
    
    function updateCategoryDropdowns() {
        const categorySelect = document.getElementById('categorySelect');
        const filterSelect = document.querySelector('select[name="category_id"]');
        
        // Clear existing options except first
        [categorySelect, filterSelect].forEach(select => {
            while (select.options.length > 1) {
                select.remove(1);
            }
        });
        
        // Add categories
        categories.forEach(category => {
            const option = `<option value="${category.id}">${category.name}</option>`;
            categorySelect.innerHTML += option;
            filterSelect.innerHTML += option;
        });
    }
    
    function loadExpenses(page = 1) {
        currentPage = page;
        
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        
        let url = `/expenses?page=${page}`;
        if (formData.get('start_date')) url += `&start_date=${formData.get('start_date')}`;
        if (formData.get('end_date')) url += `&end_date=${formData.get('end_date')}`;
        if (formData.get('category_id')) url += `&category_id=${formData.get('category_id')}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => renderExpenses(data))
            .catch(error => {
                console.error('Error loading expenses:', error);
                showAlert('danger', 'Error loading expenses');
            });
    }
    
    function renderExpenses(data) {
        const expenses = data.expenses?.data || data.expenses || [];
        const tbody = document.getElementById('expenses-table');
        
        if (expenses.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No expenses found</td></tr>';
            document.getElementById('pagination').innerHTML = '';
            return;
        }
        
        let html = '';
        expenses.forEach(expense => {
            const category = categories.find(c => c.id === expense.category_id);
            html += `
                <tr>
                    <td>${expense.date}</td>
                    <td>${expense.description || '-'}</td>
                    <td>
                        ${category ? `
                            <span class="category-color" style="background-color: ${category.color}"></span>
                            ${category.name}
                        ` : 'Uncategorized'}
                    </td>
                    <td class="fw-bold">${formatCurrency(expense.amount)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editExpense(${expense.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteExpense(${expense.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
        // Render pagination if available
        if (data.expenses?.links) {
            renderPagination(data.expenses.links);
        }
    }
    
    function renderPagination(links) {
        const container = document.getElementById('pagination');
        if (links.length <= 3) {
            container.innerHTML = '';
            return;
        }
        
        let html = '<ul class="pagination justify-content-center">';
        links.forEach(link => {
            if (link.url) {
                const page = new URL(link.url).searchParams.get('page') || 1;
                html += `
                    <li class="page-item ${link.active ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadExpenses(${page}); return false;">
                            ${link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                        </a>
                    </li>
                `;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">${link.label}</span></li>`;
            }
        });
        html += '</ul>';
        container.innerHTML = html;
    }
    
    function addExpense(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        fetch('/expenses', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Expense added successfully');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addExpenseModal')).hide();
                loadExpenses(currentPage);
                loadCategories(); // Refresh categories
            } else {
                showAlert('danger', data.message || 'Error adding expense');
            }
        })
        .catch(error => {
            showAlert('danger', 'Error adding expense');
        });
    }
    
    window.editExpense = function(id) {
        alert('Edit feature will be added in next version. For now, delete and recreate.');
    };
    
    window.deleteExpense = function(id) {
        if (confirm('Are you sure you want to delete this expense?')) {
            fetch(`/expenses/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Expense deleted successfully');
                    loadExpenses(currentPage);
                } else {
                    showAlert('danger', 'Error deleting expense');
                }
            })
            .catch(error => {
                showAlert('danger', 'Error deleting expense');
            });
        }
    };
});
</script>
@endsection