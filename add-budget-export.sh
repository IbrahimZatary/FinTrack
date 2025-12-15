#!/bin/bash

echo "Adding export button to budgets view..."

BUDGET_EXPORT='                <a href="/budgets/export/pdf" class="btn btn-sm btn-outline-danger ms-3">\
                    <i class="fas fa-file-pdf me-1"></i> Export PDF\
                </a>'

# Insert export button after the h5 title in budgets
sed -i '/<h5 class="mb-0">Monthly Budgets<\/h5>/a\
'"$BUDGET_EXPORT"'' resources/views/budgets/index.blade.php

echo "✅ Added export button to budgets view"
