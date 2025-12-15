@extends('layouts.app')

@section('title', 'Expenses')

@section('header-buttons')
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
    <i class="fas fa-plus me-1"></i> Add Expense
</button>
@endsection

@section('content')
{{ $categories->count() }} -->
 {{ Auth::id() }} -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Expenses</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('expenses.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-01')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
                
                <!-- Expenses Table -->
                @if($expenses->count() > 0)
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
                        <tbody>
                            @foreach($expenses as $expense)
                            <tr>
                                <td>{{ $expense->date->format('Y-m-d') }}</td>
                                <td>{{ $expense->description ?? '-' }}</td>
                                <td>
                                    @if($expense->category)
                                        <span style="display: inline-block; width: 12px; height: 12px; background-color: {{ $expense->category->color }}; border-radius: 50%; margin-right: 6px;"></span>
                                        {{ $expense->category->name }}
                                    @else
                                        <span class="text-muted">Uncategorized</span>
                                    @endif
                                </td>
                                <td class="fw-bold">${{ number_format($expense->amount, 2) }}</td>
                                <td>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this expense?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $expenses->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <p class="text-muted">No expenses found</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="fas fa-plus me-1"></i> Add Your First Expense
                    </button>
                </div>
                @endif
            </div>
        </div>
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
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
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
//  debug to check categories
console.log('Expenses page loaded');
console.log('Categories count from PHP: {{ $categories->count() }}');

// JavaScript fallback in case PHP rendering fails
document.addEventListener('DOMContentLoaded', function() {
    const categorySelects = document.querySelectorAll('select[name="category_id"]');
    let hasOptions = false;
    
    categorySelects.forEach(select => {
        if (select.options.length > 1) {
            hasOptions = true;
        }
    });
    
    if (!hasOptions) {
        console.log('No categories found in dropdowns, trying JavaScript load...');
        loadCategoriesViaJS();
    }
    
    function loadCategoriesViaJS() {
        fetch('/api/categories')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.categories && data.categories.length > 0) {
                    console.log('Loaded categories via JS:', data.categories.length);
                    updateDropdowns(data.categories);
                }
            })
            .catch(error => console.error('Error loading categories:', error));
    }
    
    function updateDropdowns(categories) {
        document.querySelectorAll('select[name="category_id"]').forEach(select => {
            
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        });
    }
});
</script>
@endsection

{{-- Export functionality JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle export button clicks
    document.querySelectorAll('.export-pdf, .export-excel, .export-csv').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const exportType = this.getAttribute('data-type');
            exportExpenses(exportType);
        });
    });
    
    function exportExpenses(type) {
        // Get current filter values
        const startDate = document.querySelector('input[name="start_date"]')?.value || '';
        const endDate = document.querySelector('input[name="end_date"]')?.value || '';
        const categoryId = document.querySelector('select[name="category_id"]')?.value || '';
        
        // Build query string
        let queryParams = [];
        if (startDate) queryParams.push(`start_date=${startDate}`);
        if (endDate) queryParams.push(`end_date=${endDate}`);
        if (categoryId) queryParams.push(`category_id=${categoryId}`);
        
        const queryString = queryParams.length > 0 ? '?' + queryParams.join('&') : '';
        
        // Define export URLs
        const exportUrls = {
            'pdf': `/expenses/export/pdf${queryString}`,
            'excel': `/expenses/export/excel${queryString}`,
            'csv': `/expenses/export/csv${queryString}`
        };
        
        // Show loading
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Exporting...';
        event.target.disabled = true;
        
        // Trigger download
        const link = document.createElement('a');
        link.href = exportUrls[type];
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Restore button
        setTimeout(() => {
            event.target.innerHTML = originalText;
            event.target.disabled = false;
        }, 1000);
    }
    
    // Add export success/error handling
    document.addEventListener('export:success', function(e) {
        showAlert('success', 'Export completed successfully!');
    });
    
    document.addEventListener('export:error', function(e) {
        showAlert('danger', 'Export failed: ' + e.detail.message);
    });
    
    function showAlert(type, message) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        alertDiv.innerHTML = `
            <strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
