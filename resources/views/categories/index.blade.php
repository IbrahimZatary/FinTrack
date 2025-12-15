@extends('layouts.app')

@section('title', 'Categories')

@section('header-buttons')
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
    <i class="fas fa-plus me-1"></i> Add Category
</button>
@endsection

@section('content')
<!-- Display success/error messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row" id="categories-container">
    @if($categories->count() > 0)
        @foreach($categories as $category)
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-0 d-flex align-items-center">
                                <span class="category-color me-2" style="background-color: {{ $category->color }}"></span>
                                {{ $category->name }}
                            </h5>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" 
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('Delete this category? Expenses will become uncategorized.')">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Expenses</span>
                            <span class="fw-bold">{{ $category->expenses_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-12 text-center py-5">
            <i class="fas fa-tags fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Categories Yet</h4>
            <p class="text-muted">Create your first category to organize expenses</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-1"></i> Create Your First Category
            </button>
        </div>
    @endif
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="e.g., Food, Transportation, Bills" 
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color *</label>
                        <div class="d-flex align-items-center">
                            <input type="color" name="color" class="form-control form-control-color me-3" 
                                   value="{{ old('color', '#9361ee') }}" 
                                   title="Choose color" style="width: 60px; height: 40px;">
                            <input type="text" name="color_text" class="form-control" 
                                   value="{{ old('color', '#9361ee') }}" 
                                   placeholder="#9361ee" style="max-width: 120px;" readonly>
                        </div>
                        @error('color')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update text input when color picker changes
    const colorPicker = document.querySelector('input[name="color"]');
    const colorText = document.querySelector('input[name="color_text"]');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });
    
    // Pre-fill modal with old values if there was an error
    @if($errors->any())
        const modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
        modal.show();
    @endif
    
    // Handle form submission
    document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    });
});
</script>

<style>
.category-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
}
</style>
@endsection
