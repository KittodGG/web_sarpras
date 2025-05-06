/**
 * JavaScript Utama Aplikasi Peminjaman Sarpras SMKN 1 Cimahi
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toggle Sidebar
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            content.classList.toggle('content-expanded');
            
            // Save state to localStorage
            const sidebarState = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', sidebarState);
        });
        
        // Check localStorage for sidebar state
        const sidebarState = localStorage.getItem('sidebarCollapsed');
        if (sidebarState === 'true') {
            sidebar.classList.add('sidebar-collapsed');
            content.classList.add('content-expanded');
        }
    }
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 5000);
    });
    
    // Tooltips initialization
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Popovers initialization
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    // Datepickers initialization
    const datepickers = document.querySelectorAll('.datepicker');
    if (datepickers.length > 0) {
        datepickers.forEach(function(datepicker) {
            new Datepicker(datepicker, {
                format: 'dd/mm/yyyy',
                autohide: true,
                todayBtn: true,
                todayBtnMode: 1,
                todayHighlight: true,
                weekStart: 1
            });
        });
    }
    
    // Add active class to sidebar menu based on current page
    const currentPage = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    
    sidebarLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        if (currentPage.indexOf(href) !== -1) {
            link.classList.add('active');
        }
    });
    
    // DataTables initialization
    const dataTables = document.querySelectorAll('.datatable');
    if (dataTables.length > 0) {
        dataTables.forEach(function(table) {
            try {
                // Skip tables that have been marked as initialized (from inline scripts)
                if (table.classList.contains('dt-initialized')) {
                    console.log('Table #' + table.id + ' already initialized (marked), skipping');
                    return;
                }
                
                // Also check using the DataTable API
                if (typeof $.fn.dataTable !== 'undefined' && 
                    $.fn.dataTable.isDataTable('#' + table.id)) {
                    // Table is already initialized, skip it
                    console.log('Table #' + table.id + ' already initialized (detected), skipping');
                    // Mark it as initialized
                    table.classList.add('dt-initialized');
                } else {
                    // Initialize the table
                    new DataTable(table, {
                        responsive: true,
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                            infoFiltered: "(disaring dari _MAX_ total data)",
                            zeroRecords: "Tidak ada data yang cocok",
                            emptyTable: "Tidak ada data tersedia",
                            paginate: {
                                first: "Pertama",
                                previous: "Sebelumnya",
                                next: "Selanjutnya",
                                last: "Terakhir"
                            }
                        }
                    });
                    // Mark it as initialized
                    table.classList.add('dt-initialized');
                }
            } catch (error) {
                console.error('Error initializing DataTable:', error);
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Image preview on file input change
    const imageInputs = document.querySelectorAll('.image-input');
    if (imageInputs.length > 0) {
        imageInputs.forEach(function(input) {
            input.addEventListener('change', function(e) {
                const previewId = this.dataset.preview;
                const preview = document.getElementById(previewId);
                
                if (preview && this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    }
    
    // Confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Apakah Anda yakin?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Charts initialization
    initCharts();
    
    // Add animation classes
    addAnimations();
});

// Function to initialize charts
function initCharts() {
    // Dashboard stats chart
    const statsChart = document.getElementById('statsChart');
    if (statsChart) {
        const ctx = statsChart.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Peminjaman',
                    data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 5],
                    backgroundColor: 'rgba(78, 56, 43, 0.2)',
                    borderColor: 'rgba(78, 56, 43, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Pie chart for item categories
    const categoriesChart = document.getElementById('categoriesChart');
    if (categoriesChart) {
        const ctx = categoriesChart.getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Elektronik', 'Furniture', 'Alat Olahraga', 'Alat Musik', 'Lainnya'],
                datasets: [{
                    data: [12, 19, 8, 5, 2],
                    backgroundColor: [
                        'rgba(147, 122, 102, 0.7)',
                        'rgba(78, 56, 43, 0.7)',
                        'rgba(84, 40, 39, 0.7)',
                        'rgba(58, 16, 28, 0.7)',
                        'rgba(116, 112, 113, 0.7)'
                    ],
                    borderColor: [
                        'rgba(147, 122, 102, 1)',
                        'rgba(78, 56, 43, 1)',
                        'rgba(84, 40, 39, 1)',
                        'rgba(58, 16, 28, 1)',
                        'rgba(116, 112, 113, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Bar chart for item status
    const statusChart = document.getElementById('statusChart');
    if (statusChart) {
        const ctx = statusChart.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Tersedia', 'Dipinjam', 'Rusak', 'Perbaikan'],
                datasets: [{
                    label: 'Status Sarpras',
                    data: [42, 19, 8, 5],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(23, 162, 184, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(23, 162, 184, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Function to add animation classes to elements
function addAnimations() {
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(function(card, index) {
        // Add animation with delay based on index
        setTimeout(function() {
            card.classList.add('fade-in');
        }, 100 * index);
    });
    
    // Add slide-in-left animation to sidebar items
    const sidebarItems = document.querySelectorAll('.sidebar-menu-item');
    sidebarItems.forEach(function(item, index) {
        setTimeout(function() {
            item.classList.add('slide-in-left');
        }, 50 * index);
    });
    
    // Add slide-in-up animation to tables
    const tables = document.querySelectorAll('.table-container');
    tables.forEach(function(table) {
        table.classList.add('slide-in-up');
    });
}

// Function to show/hide password in login form
function togglePassword(buttonId, inputId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    
    if (button && input) {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }
}

// Function to handle search filter on tables
function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('dataTable');
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 0; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        if (found) {
            tr[i].style.display = '';
        } else {
            if (i > 0) { // Skip header row
                tr[i].style.display = 'none';
            }
        }
    }
}

// Function to print element
function printElement(elementId) {
    const element = document.getElementById(elementId);
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = element.innerHTML;
    window.print();
    document.body.innerHTML = originalContent;
    
    // Reinitialize scripts after printing
    document.addEventListener('DOMContentLoaded', function() {
        // Your initialization code here
    });
}

// Function to export table to Excel
function exportTableToExcel(tableID, filename = '') {
    const table = document.getElementById(tableID);
    const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet JS' });
    XLSX.writeFile(wb, filename + '.xlsx');
}

// Function to export table to PDF
function exportTableToPDF(tableID, filename = '') {
    const table = document.getElementById(tableID);
    html2canvas(table).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(filename + '.pdf');
    });
}