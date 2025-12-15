@extends('layouts.app')

@section('title', 'Dashboard')

@section('header-buttons')
<div class="d-flex gap-2">
    <select id="monthSelect" class="form-select form-select-sm" style="width: 120px;">
        @for($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                {{ date('F', mktime(0, 0, 0, $i, 10)) }}
            </option>
        @endfor
    </select>
    
    <select id="yearSelect" class="form-select form-select-sm" style="width: 100px;">
        @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>
                {{ $i }}
            </option>
        @endfor
    </select>
    
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="fas fa-plus me-1"></i> Add Expense
    </button>
</div>
@endsection

@section('content')
<div id="dashboard-content">
    <!-- loading indicator -->
    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading dashboard data...</p>
    </div>
</div>

<!-- here add Expense Modal -->
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
                            <option value="">Loading categories...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
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
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const dashboardContent = document.getElementById('dashboard-content');
    const loading = document.getElementById('loading');
    
   
    loadDashboardData();
    

    loadCategories();
    
  
    monthSelect.addEventListener('change', loadDashboardData);
    yearSelect.addEventListener('change', loadDashboardData);
    
    // handle expense 
    document.getElementById('addExpenseForm').addEventListener('submit', addExpense);
    
    function loadCategories() {
        fetch('/api/categories')
            .then(response => response.json())
            .then(data => {
                populateCategoryDropdown(data.categories || []);
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                populateCategoryDropdown([]);
            });
    }
    
    function populateCategoryDropdown(categories) {
        const select = document.getElementById('categorySelect');
        
        // clear loading message
        select.innerHTML = '';
        











        // add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select Category';
        select.appendChild(defaultOption);
        
        if (categories.length > 0) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        } else {
            const noCatOption = document.createElement('option');
            noCatOption.value = '';
            noCatOption.textContent = 'No categories found. Create categories first.';
            noCatOption.disabled = true;
            select.appendChild(noCatOption);
            
            // disable submit button if no categories
            document.querySelector('#addExpenseForm button[type="submit"]').disabled = true;
        }
    }
    
    function loadDashboardData() {
        const month = monthSelect.value;
        const year = yearSelect.value;
        
        //  loading
        loading.style.display = 'block';
        dashboardContent.innerHTML = '';
        
        // Fetch the  data for the dashboard
        fetch(`/dashboard/data?month=${month}&year=${year}`)
            .then(response => response.json())
            .then(data => {
                renderDashboard(data);
                loading.style.display = 'none';
            })
            .catch(error => {
                console.error('Error loading dashboard:', error);
                loading.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading dashboard data. Please try again.
                    </div>
                `;
            });
    }
    
    function renderDashboard(data) {
        // create dashboard HTML
        const dashboardHTML = `
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Monthly Spent</h6>
                                    <h3 class="mb-0">${formatCurrency(data.monthly_spent || 0)}</h3>
                                </div>
                                <div class="bg-primary text-white rounded-circle p-3">
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge ${(data.month_over_month || 0) > 0 ? 'bg-danger' : 'bg-success'}">
                                    ${(data.month_over_month || 0) > 0 ? '+' : ''}${data.month_over_month || 0}%
                                </span>
                                <small class="text-muted"> vs last month</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Monthly Budget</h6>
                                    <h3 class="mb-0">${formatCurrency(data.monthly_budget || 0)}</h3>
                                </div>
                                <div class="bg-warning text-white rounded-circle p-3">
                                    <i class="fas fa-chart-pie fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge ${(data.budget_utilization || 0) > 90 ? 'bg-danger' : (data.budget_utilization || 0) > 70 ? 'bg-warning' : 'bg-info'}">
                                    ${data.budget_utilization || 0}%
                                </span>
                                <small class="text-muted"> utilization</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Remaining</h6>
                                    <h3 class="mb-0">${formatCurrency(data.remaining || 0)}</h3>
                                </div>
                                <div class="bg-success text-white rounded-circle p-3">
                                    <i class="fas fa-piggy-bank fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-secondary">${formatCurrency(data.daily_average || 0)}/day</span>
                                <small class="text-muted"> daily average</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Transactions</h6>
                                    <h3 class="mb-0">${data.summary?.total_expenses || 0}</h3>
                                    <small class="text-muted">this month</small>
                                </div>
                                <div class="bg-info text-white rounded-circle p-3">
                                    <i class="fas fa-list fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark">${data.summary?.days_remaining || 0} days left</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Expenses -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Expenses</h5>
                            <a href="/expenses" class="btn btn-sm btn-outline-primary">
                                View All Expenses
                            </a>
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
                                        </tr>
                                    </thead>
                                    <tbody id="recent-expenses-table">
                                        ${renderRecentExpenses(data.recent_expenses || [])}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        dashboardContent.innerHTML = dashboardHTML;
    }
    
    function renderRecentExpenses(expenses) {
        if (expenses.length === 0) {
            return `
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <p class="text-muted">No recent expenses</p>
                    </td>
                </tr>
            `;
        }
        
        let html = '';
        expenses.slice(0, 5).forEach(expense => {
            html += `
                <tr>
                    <td>${expense.date}</td>
                    <td>${expense.description || '-'}</td>
                    <td>
                        <span class="category-badge" style="background-color: ${expense.color || '#CCCCCC'}"></span>
                        ${expense.category || 'Uncategorized'}
                    </td>
                    <td class="fw-bold">${formatCurrency(expense.amount)}</td>
                </tr>
            `;
        });
        
        return html;
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
                showAlert('success', 'Expense added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addExpenseModal')).hide();
                loadDashboardData(); // Refresh dashboard
            } else {
                showAlert('danger', data.message || 'Error adding expense');
            }
        })
        .catch(error => {
            console.error('Error adding expense:', error);
            showAlert('danger', 'Error adding expense');
        });
    }
    
    // Utility functions
    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid .col-md-10 .p-4').prepend(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
});

// here is the global global styles
const style = document.createElement('style');
style.textContent = `
.stat-card {
    border-left: 4px solid #1361ee;
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.category-badge {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}
`;
document.head.appendChild(style);
</script>
@endsection
