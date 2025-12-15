@extends('layouts.app')

@section('title', 'Categories')

@section('header-buttons')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus me-1"></i> Add Category
    </button>
@endsection

@section('content')
<div class="row" id="categories-container">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading categories...</p>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#4361ee" required>
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
    loadCategories();
    
    document.getElementById('addCategoryForm').addEventListener('submit', addCategory);
    
    function loadCategories() {
        fetch('/categories')
            .then(response => response.json())
            .then(data => renderCategories(data.categories || []))
            .catch(error => {
                console.error('Error loading categories:', error);
                showAlert('danger', 'Error loading categories');
            });
    }
    
    function renderCategories(categories) {
        const container = document.getElementById('categories-container');
        
        if (categories.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No categories yet</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-1"></i> Add Your First Category
                    </button>
                </div>
            `;
            return;
        }
        
        let html = '';
        categories.forEach(category => {
            const expenseCount = category.expenses_count || 0;
            const totalSpent = category.total_spent || 0;
            
            html += `
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <span class="category-color me-2" style="background-color: ${category.color}"></span>
                                        ${category.name}
                                    </h5>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteCategory(${category.id})">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Expenses</span>
                                    <span class="fw-bold">${expenseCount}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Spent</span>
                                    <span class="fw-bold">${formatCurrency(totalSpent)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function addCategory(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        fetch('/categories', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Category added successfully');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                loadCategories();
            } else {
                showAlert('danger', data.message || 'Error adding category');
            }
        })
        .catch(error => {
            showAlert('danger', 'Error adding category');
        });
    }
    
    window.deleteCategory = function(categoryId) {
        if (confirm('Are you sure you want to delete this category? Expenses in this category will become uncategorized.')) {
            fetch(`/categories/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Category deleted successfully');
                    loadCategories();
                } else {
                    showAlert('danger', data.message || 'Error deleting category');
                }
            })
            .catch(error => {
                showAlert('danger', 'Error deleting category');
            });
        }
    };
});
</script>
@endsection