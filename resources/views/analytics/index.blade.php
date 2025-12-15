@extends('layouts.app')

@section('title', 'Analytics')

@section('header-buttons')
<div class="d-flex gap-2">
    <select id="analyticsPeriod" class="form-select form-select-sm" style="width: 150px;">
        <option value="monthly">This Month</option>
        <option value="last_month">Last Month</option>
        <option value="quarterly">This Quarter</option>
        <option value="yearly">This Year</option>
        <option value="all">All Time</option>
    </select>
    
    <button class="btn btn-sm btn-outline-primary" onclick="refreshAnalytics()">
        <i class="fas fa-sync-alt me-1"></i> Refresh
    </button>
</div>
@endsection

@section('content')
<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Spent</h6>
                        <h3 id="total-spent-analytics" class="mb-0">$0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded-circle p-3 me-3">
                        <i class="fas fa-tags fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Categories</h6>
                        <h3 id="total-categories" class="mb-0">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning text-white rounded-circle p-3 me-3">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Monthly Avg</h6>
                        <h3 id="monthly-average" class="mb-0">$0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded-circle p-3 me-3">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Expenses</h6>
                        <h3 id="total-expenses" class="mb-0">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Spending Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Categories</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Additional Charts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daily Spending Pattern</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyPatternChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Budget vs Actual</h5>
            </div>
            <div class="card-body">
                <canvas id="budgetVsActualChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let monthlyTrendChart = null;
    let categoryChart = null;
    let dailyPatternChart = null;
    let budgetVsActualChart = null;
    

    loadAnalyticsData();
    
    // Period selector 
    document.getElementById('analyticsPeriod').addEventListener('change', loadAnalyticsData);
    
    function loadAnalyticsData() {
        const period = document.getElementById('analyticsPeriod').value;
        
        showLoading();
        
      
        setTimeout(() => {
            const mockData = generateMockAnalyticsData(period);
            renderAnalytics(mockData);
            hideLoading();
        }, 500);
    }
    
    function renderAnalytics(data) {
        // Update stats
        document.getElementById('total-spent-analytics').textContent = formatCurrency(data.totalSpent);
        document.getElementById('total-categories').textContent = data.totalCategories;
        document.getElementById('monthly-average').textContent = formatCurrency(data.monthlyAverage);
        document.getElementById('total-expenses').textContent = data.totalExpenses;
        
        // Render charts
        renderMonthlyTrendChart(data.monthlyTrend);
        renderCategoryChart(data.topCategories);
        renderDailyPatternChart(data.dailyPattern);
        renderBudgetVsActualChart(data.budgetVsActual);
    }
    
    function renderMonthlyTrendChart(data) {
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        
        if (monthlyTrendChart) {
            monthlyTrendChart.destroy();
        }
        
        monthlyTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Spending ($)',
                    data: data.amounts,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function renderCategoryChart(data) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        
        if (categoryChart) {
            categoryChart.destroy();
        }
        
        categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.amounts,
                    backgroundColor: data.colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function renderDailyPatternChart(data) {
        const ctx = document.getElementById('dailyPatternChart').getContext('2d');
        
        if (dailyPatternChart) {
            dailyPatternChart.destroy();
        }
        
        dailyPatternChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Average Daily Spending',
                    data: data.amounts,
                    backgroundColor: '#06D6A0',
                    borderColor: '#06D6A0',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function renderBudgetVsActualChart(data) {
        const ctx = document.getElementById('budgetVsActualChart').getContext('2d');
        
        if (budgetVsActualChart) {
            budgetVsActualChart.destroy();
        }
        
        budgetVsActualChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Budget',
                        data: data.budgets,
                        backgroundColor: '#FFD166',
                        borderColor: '#FFD166',
                        borderWidth: 1
                    },
                    {
                        label: 'Actual',
                        data: data.actuals,
                        backgroundColor: '#EF476F',
                        borderColor: '#EF476F',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function generateMockAnalyticsData(period) {
        // Mock data for demonstration
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const categories = ['Food', 'Transport', 'Shopping', 'Bills', 'Entertainment', 'Healthcare'];
        const colors = ['#FF6B6B', '#4ECDC4', '#FFD166', '#06D6A0', '#118AB2', '#EF476F'];
        
        // Generate monthly trend
        const monthlyTrend = {
            labels: months.slice(0, 6),
            amounts: Array.from({length: 6}, () => Math.floor(Math.random() * 2000) + 500)
        };
        
        // Generate top categories
        const topCategories = {
            labels: categories,
            amounts: Array.from({length: 6}, () => Math.floor(Math.random() * 1000) + 200),
            colors: colors
        };
        
        // Generate daily pattern
        const dailyPattern = {
            labels: days,
            amounts: Array.from({length: 7}, () => Math.floor(Math.random() * 100) + 20)
        };
        
        // Generate budget vs actual
        const budgetVsActual = {
            labels: categories.slice(0, 4),
            budgets: Array.from({length: 4}, () => Math.floor(Math.random() * 800) + 200),
            actuals: Array.from({length: 4}, () => Math.floor(Math.random() * 1000) + 150)
        };
        
        return {
            totalSpent: 12500.75,
            totalCategories: 8,
            monthlyAverage: 1041.73,
            totalExpenses: 156,
            monthlyTrend: monthlyTrend,
            topCategories: topCategories,
            dailyPattern: dailyPattern,
            budgetVsActual: budgetVsActual
        };
    }
    
    function showLoading() {
        // You can add a loading indicator here
    }
    
    function hideLoading() {
        // Hide loading indicator
    }
    
    window.refreshAnalytics = function() {
        loadAnalyticsData();
        showAlert('success', 'Analytics refreshed');
    };
});
</script>

<style>
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
</style>
@endsection