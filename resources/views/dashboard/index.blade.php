@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Spent</h6>
                        <h3 id="total-spent" class="mb-0">$0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded-circle p-3 me-3">
                        <i class="fas fa-piggy-bank fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Remaining</h6>
                        <h3 id="remaining" class="mb-0">$0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning text-white rounded-circle p-3 me-3">
                        <i class="fas fa-chart-pie fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Budget</h6>
                        <h3 id="budget" class="mb-0">$0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded-circle p-3 me-3">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Categories</h6>
                        <h3 id="categories-count" class="mb-0">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Expenses -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Expenses</h5>
                <a href="/expenses" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Expense
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
                        <tbody id="recent-expenses">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    function loadDashboardData() {
        fetch('/dashboard')
            .then(response => response.json())
            .then(data => {
                // Update stats
                document.getElementById('total-spent').textContent = formatCurrency(data.monthly_spent || 0);
                document.getElementById('remaining').textContent = formatCurrency(data.remaining || 0);
                document.getElementById('budget').textContent = formatCurrency(data.monthly_budget || 0);
                document.getElementById('categories-count').textContent = data.summary?.total_categories || 0;
                
                // Update recent expenses
                const expensesBody = document.getElementById('recent-expenses');
                const expenses = data.recent_expenses || [];
                
                if (expenses.length === 0) {
                    expensesBody.innerHTML = '<tr><td colspan="4" class="text-center">No expenses yet</td></tr>';
                    return;
                }
                
                let html = '';
                expenses.slice(0, 5).forEach(expense => {
                    html += `
                        <tr>
                            <td>${expense.date}</td>
                            <td>${expense.description || '-'}</td>
                            <td>
                                <span class="category-color" style="background-color: ${expense.color || '#ccc'}"></span>
                                ${expense.category || 'Uncategorized'}
                            </td>
                            <td class="fw-bold">${formatCurrency(expense.amount)}</td>
                        </tr>
                    `;
                });
                
                expensesBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading dashboard:', error);
                showAlert('danger', 'Error loading dashboard data');
            });
    }
    
    // Auto-refresh every 30 seconds
    setInterval(loadDashboardData, 30000);
});
</script>
@endsection