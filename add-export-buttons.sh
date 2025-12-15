#!/bin/bash

echo "Adding export buttons to expenses view..."

# Create a temporary file with the export buttons section
EXPORT_BUTTONS='                <div class="btn-group ms-3">\
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">\
                        <i class="fas fa-download me-1"></i> Export\
                    </button>\
                    <ul class="dropdown-menu">\
                        <li><a class="dropdown-item export-pdf" href="#" data-type="pdf">\
                            <i class="fas fa-file-pdf text-danger me-2"></i> Export as PDF\
                        </a></li>\
                        <li><a class="dropdown-item export-excel" href="#" data-type="excel">\
                            <i class="fas fa-file-excel text-success me-2"></i> Export as Excel\
                        </a></li>\
                        <li><a class="dropdown-item export-csv" href="#" data-type="csv">\
                            <i class="fas fa-file-csv text-primary me-2"></i> Export as CSV\
                        </a></li>\
                    </ul>\
                </div>'

# Insert export buttons after the h5 title
sed -i '/<h5 class="mb-0">All Expenses<\/h5>/a\
'"$EXPORT_BUTTONS"'' resources/views/expenses/index.blade.php

echo "✅ Added export buttons to expenses view"
