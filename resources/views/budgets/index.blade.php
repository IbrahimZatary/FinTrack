@extends('layouts.app')

@section('title', 'Budgets')

@section('header-buttons')
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
    <i class="fas fa-plus me-1"></i> Add Budget
</button>
@endsection

@section('content')
 {{ $categories->count() }} 
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Budgets</h5>
            </div>
            <div class="card-body">
                <!-- Budgets Table -->
                @if($budgets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Budget Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($budgets as $budget)
                            <tr>
                                <td>
                                    @if($budget->category)
                                    <span style="display: inline-block; width: 15px; height: 15px; background-color: {{ $budget->category->color }}; border-radius: 50%; margin-right: 8px;"></span>
                                    {{ $budget->category->name }}
                                    @else
                                    <span class="text-muted">Uncategorized</span>
                                    @endif
                                </td>
                                <td>{{ $months[$budget->month] ?? $budget->month }}</td>
                                <td>{{ $budget->year }}</td>
                                <td class="fw-bold">${{ number_format($budget->amount, 2) }}</td>
                                <td>
                                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this budget?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <p class="text-muted">No budgets set yet</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                        <i class="fas fa-plus me-1"></i> Add Your First Budget
                    </button>
                </div>
                @endif
            </div>
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
            <form action="{{ route('budgets.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required id="budgetCategorySelect">
                            <option value="">Select Category</option>
                            @if(isset($categories) && $categories->count() > 0)
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No categories found. Please create categories first.</option>
                            @endif
                        </select>
                        <div id="budgetCategoryWarning" class="alert alert-warning mt-2 d-none">
                            <small><i class="fas fa-exclamation-triangle me-1"></i> 
                            No categories found. Please create categories first on the <a href="/categories">Categories</a> page.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-control" required>
                                @foreach($months as $key => $month)
                                    <option value="{{ $key }}" {{ $key == date('n') ? 'selected' : '' }}>
                                        {{ $month }}
                                    </option>
                                @endforeach
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
    //  warning if no categories
    const categoriesCount = {{ $categories->count() }};
    const categorySelect = document.getElementById('budgetCategorySelect');
    const categoryWarning = document.getElementById('budgetCategoryWarning');
    
    if (categoriesCount === 0) {
        categoryWarning.classList.remove('d-none');
        document.querySelector('#addBudgetModal button[type="submit"]').disabled = true;
        
        // clear dropdown 
        categorySelect.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No categories available';
        option.disabled = true;
        categorySelect.appendChild(option);
    }
});
</script>
@endsection
