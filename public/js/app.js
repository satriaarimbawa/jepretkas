document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle untuk Mobile
    const menuToggle = document.getElementById('mobile-toggle');
    const mobileMenuTrigger = document.getElementById('mobile-menu-trigger');
    const sidebar = document.getElementById('sidebar');

    if (sidebar) {
        const toggleSidebar = function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        };

        if (menuToggle) {
            menuToggle.addEventListener('click', toggleSidebar);
        }
        if (mobileMenuTrigger) {
            mobileMenuTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSidebar(e);
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024 && sidebar.classList.contains('active')) {
                const isTrigger = e.target === menuToggle || 
                                  e.target === mobileMenuTrigger || 
                                  (mobileMenuTrigger && mobileMenuTrigger.contains(e.target));
                if (!sidebar.contains(e.target) && !isTrigger) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }

    // 2. Flash Message Auto Dismiss
    const flashMessages = document.querySelectorAll('.toast');
    flashMessages.forEach(function(toast) {
        setTimeout(function() {
            toast.style.animation = 'fadeOut 0.4s forwards';
            setTimeout(function() {
                toast.remove();
            }, 400);
        }, 4000);

        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                toast.remove();
            });
        }
    });

    // 3. Modal Preview Struk / Receipt
    const viewReceiptBtns = document.querySelectorAll('.view-receipt-btn');
    const receiptModal = document.getElementById('receipt-modal');
    const receiptImg = document.getElementById('modal-receipt-img');
    const receiptCaption = document.getElementById('modal-receipt-caption');
    const modalClose = document.getElementById('modal-close');

    if (receiptModal && receiptImg && modalClose) {
        viewReceiptBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const src = this.getAttribute('data-src');
                const title = this.getAttribute('data-title') || 'Pratinjau Struk';
                receiptImg.src = src;
                if (receiptCaption) {
                    receiptCaption.textContent = title;
                }
                receiptModal.classList.add('active');
            });
        });

        const closeModal = function() {
            receiptModal.classList.remove('active');
            setTimeout(() => {
                receiptImg.src = '';
            }, 300);
        };

        modalClose.addEventListener('click', closeModal);
        receiptModal.addEventListener('click', function(e) {
            if (e.target === receiptModal) {
                closeModal();
            }
        });
    }

    // 4. Form Input Transaksi: Toggle Pemasukan/Pengeluaran & Filter Kategori
    const typeToggleBtns = document.querySelectorAll('.type-toggle-btn');
    const transactionTypeInput = document.getElementById('transaction-type');
    const categorySelect = document.getElementById('category-id');

    if (typeToggleBtns.length > 0 && transactionTypeInput && categorySelect) {
        const filterCategories = function(type) {
            const options = categorySelect.querySelectorAll('option');
            let hasVisibleOption = false;
            let firstVisibleOption = null;

            options.forEach(function(opt) {
                if (opt.value === "") {
                    // Placeholder option, always show
                    return;
                }
                const optType = opt.getAttribute('data-type');
                if (optType === type) {
                    opt.style.display = 'block';
                    if (!firstVisibleOption) firstVisibleOption = opt.value;
                    if (opt.selected) hasVisibleOption = true;
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) opt.selected = false;
                }
            });

            // Auto select first option or placeholder
            if (!hasVisibleOption) {
                categorySelect.value = firstVisibleOption || "";
            }
        };

        // Initialize filter
        filterCategories(transactionTypeInput.value);

        typeToggleBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                typeToggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const selectedType = this.getAttribute('data-type');
                transactionTypeInput.value = selectedType;
                filterCategories(selectedType);
            });
        });
    }

    // 4b. Manajemen Kategori: Toggle Checkbox Tetap & Modal Edit
    const categoryTypeSelect = document.getElementById('type');
    const fixedExpenseGroup = document.getElementById('fixed-expense-group');

    if (categoryTypeSelect && fixedExpenseGroup) {
        const toggleFixedGroup = function() {
            if (categoryTypeSelect.value === 'expense') {
                fixedExpenseGroup.style.display = 'block';
            } else {
                fixedExpenseGroup.style.display = 'none';
                const isFixedCheck = document.getElementById('is_fixed');
                if (isFixedCheck) isFixedCheck.checked = false;
            }
        };
        categoryTypeSelect.addEventListener('change', toggleFixedGroup);
        toggleFixedGroup(); // Jalankan saat awal dimuat
    }

    // Pengendalian Modal Edit Kategori
    const editCategoryBtns = document.querySelectorAll('.edit-category-btn');
    const editCategoryModal = document.getElementById('edit-category-modal');
    const editModalClose = document.getElementById('edit-modal-close');
    const editCategoryForm = document.getElementById('edit-category-form');

    if (editCategoryBtns.length > 0 && editCategoryModal && editCategoryForm && editModalClose) {
        const editNameInput = document.getElementById('edit-name');
        const editTypeSelect = document.getElementById('edit-type');
        const editIsFixedCheck = document.getElementById('edit-is-fixed');
        const editFixedGroup = document.getElementById('edit-fixed-expense-group');
        const editColorInput = document.getElementById('edit-color');

        const toggleEditFixedGroup = function() {
            if (editTypeSelect.value === 'expense') {
                editFixedGroup.style.display = 'block';
            } else {
                editFixedGroup.style.display = 'none';
                if (editIsFixedCheck) editIsFixedCheck.checked = false;
            }
        };

        editTypeSelect.addEventListener('change', toggleEditFixedGroup);

        editCategoryBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const type = this.getAttribute('data-type');
                const color = this.getAttribute('data-color');
                const fixed = parseInt(this.getAttribute('data-fixed')) === 1;

                // Isi data ke dalam form modal
                editNameInput.value = name;
                editTypeSelect.value = type;
                editIsFixedCheck.checked = fixed;
                editColorInput.value = color;

                // Tampilkan/sembunyikan opsi Pengeluaran Tetap di modal
                toggleEditFixedGroup();

                // Set form action ke /categories/update/{id} secara dinamis
                const storeForm = document.querySelector('form[action*="/categories/store"]');
                if (storeForm) {
                    const storeAction = storeForm.action;
                    editCategoryForm.action = storeAction.replace('/store', '/update/' + id);
                }

                // Tampilkan modal edit
                editCategoryModal.classList.add('active');
            });
        });

        // Event menutup modal
        const closeEditModal = function() {
            editCategoryModal.classList.remove('active');
        };

        editModalClose.addEventListener('click', closeEditModal);
        editCategoryModal.addEventListener('click', function(e) {
            if (e.target === editCategoryModal) {
                closeEditModal();
            }
        });
    }

    // 5. Drag and Drop + Preview File Upload
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('receipt-photo');
    const previewContainer = document.getElementById('preview-container');
    const previewImg = document.getElementById('preview-img');
    const removePreviewBtn = document.getElementById('remove-preview');

    if (uploadZone && fileInput && previewContainer && previewImg) {
        // Trigger click on file input
        uploadZone.addEventListener('click', function() {
            fileInput.click();
        });

        // File change
        fileInput.addEventListener('change', function() {
            handleFileSelect(this.files[0]);
        });

        // Drag events
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
            }, false);
        });

        uploadZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        function handleFileSelect(file) {
            if (!file) return;

            // Check file type
            if (!file.type.match('image.*')) {
                alert('Hanya diperbolehkan mengunggah file gambar (JPG, PNG).');
                fileInput.value = '';
                return;
            }

            // Check size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal adalah 2MB.');
                fileInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'block';
                uploadZone.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        if (removePreviewBtn) {
            removePreviewBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                previewImg.src = '';
                previewContainer.style.display = 'none';
                uploadZone.style.display = 'flex';
            });
        }
    }

    // 6. Format Input Nominal Uang (Rupiah)
    const amountInput = document.getElementById('amount-input');
    if (amountInput) {
        amountInput.addEventListener('input', function(e) {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            if (value !== '') {
                // Format thousand separators
                this.value = formatRupiah(value);
            } else {
                this.value = '';
            }
        });

        // Ensure raw number submitted
        const form = amountInput.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                // strip dot formatting before submit
                let rawValue = amountInput.value.replace(/\./g, '');
                amountInput.value = rawValue;
            });
        }
    }

    function formatRupiah(numberStr) {
        return parseInt(numberStr, 10).toLocaleString('id-ID');
    }

    // 7. Dashboard Chart.js
    const chartCanvas = document.getElementById('dashboardChart');
    if (chartCanvas) {
        const baseUrl = chartCanvas.getAttribute('data-base-url');
        fetch(baseUrl + '/dashboard/chartData')
            .then(response => response.json())
            .then(data => {
                const ctx = chartCanvas.getContext('2d');
                
                // Gradients for bars
                const incomeGradient = ctx.createLinearGradient(0, 0, 0, 300);
                incomeGradient.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
                incomeGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

                const expenseGradient = ctx.createLinearGradient(0, 0, 0, 300);
                expenseGradient.addColorStop(0, 'rgba(239, 68, 68, 0.45)');
                expenseGradient.addColorStop(1, 'rgba(239, 68, 68, 0.05)');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Pemasukan',
                                data: data.datasets.income,
                                backgroundColor: incomeGradient,
                                borderColor: '#10b981',
                                borderWidth: 2,
                                borderRadius: 6,
                                hoverBackgroundColor: 'rgba(16, 185, 129, 0.6)'
                            },
                            {
                                label: 'Pengeluaran',
                                data: data.datasets.expense,
                                backgroundColor: expenseGradient,
                                borderColor: '#ef4444',
                                borderWidth: 2,
                                borderRadius: 6,
                                hoverBackgroundColor: 'rgba(239, 68, 68, 0.6)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#94a3b8',
                                    font: { family: 'Inter', size: 12 }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1a1a2e',
                                titleColor: '#fff',
                                bodyColor: '#e2e8f0',
                                borderColor: 'rgba(255,255,255,0.08)',
                                borderWidth: 1,
                                padding: 12,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        label += 'Rp ' + parseInt(context.raw).toLocaleString('id-ID');
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.03)' },
                                ticks: { color: '#94a3b8' }
                            },
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.03)' },
                                ticks: {
                                    color: '#94a3b8',
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return 'Rp ' + (value / 1000000) + 'jt';
                                        } else if (value >= 1000) {
                                            return 'Rp ' + (value / 1000) + 'rb';
                                        }
                                        return 'Rp ' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(err => console.error('Gagal memuat chart data:', err));
    }
});
