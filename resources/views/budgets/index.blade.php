@extends('layouts.app')

@section('title', 'Budgets')

@section('header-buttons')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
        <i class="fas fa-plus me-1"></i> Add Budget
    </button>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Monthly Budgets</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Month</th>
                        <th>Budget Amount</th>
                        <th>Spent</th>
                        <th>Remaining</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="budgets-table">
                    <tr><td colspan="6" class="text-center">Loading budgets...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Budget Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBudgetForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required id="budgetCategorySelect">
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-control" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" min="2000" max="2100" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let categories = [];
    
    // Load initial data
    loadCategories();
    loadBudgets();
    
    document.getElementById('addBudgetForm').addEventListener('submit', addBudget);
    
    function loadCategories() {
        fetch('/categories')
            .then(response => response.json())
            .then(data => {
                categories = data.categories || [];
                updateCategorySelect();
            })
            .catch(error => console.error('Error loading categories:', error));
    }
    
    function updateCategorySelect() {
        const select = document.getElementById('budgetCategorySelect');
        while (select.options.length > 1) select.remove(1);
        
        categories.forEach(category => {
            select.innerHTML += `<option value="${category.id}">${category.name}</option>`;
        });
    }
    
    function loadBudgets() {
        fetch('/budgets')
            .then(response => response.json())
            .then(data => renderBudgets(data.budgets || []))
            .catch(error => {
                console.error('Error loading budgets:', error);
                showAlert('danger', 'Error loading budgets');
            });
    }
    
    function renderBudgets(budgets) {
        const tbody = document.getElementById('budgets-table');
        
        if (budgets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <p class="text-muted">No budgets set yet</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                            <i class="fas fa-plus me-1"></i> Add Your First Budget
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        budgets.forEach(budget => {
            const category = categories.find(c => c.id === budget.category_id);
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            html += `
                <tr>
                    <td>
                        ${category ? `
                            <span class="category-color" style="background-color: ${category.color}"></span>
                            ${category.name}
                        ` : 'Uncategorized'}
                    </td>
                    <td>${monthNames[budget.month - 1]} ${budget.year}</td>
                    <td class="fw-bold">${formatCurrency(budget.amount)}</td>
                    <td>Loading...</td>
                    <td>Loading...</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteBudget(${budget.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
        // Load spent amounts for each budget
        budgets.forEach((budget, index) => {
            fetch(`/budgets/${budget.id}`)
                .then(response => response.json())
                .then(data => {
                    const spent = data.budget?.spent || 0;
                    const remaining = budget.amount - spent;
                    const rows = tbody.querySelector