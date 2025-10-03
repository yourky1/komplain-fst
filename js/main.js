// Hamburger Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
            }
        });

        // Close menu when clicking nav link
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
            });
        });
    }

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444';
                    
                    // Reset border color on input
                    input.addEventListener('input', function() {
                        this.style.borderColor = '';
                    }, { once: true });
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
            }
        });
    });

    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileSize = this.files[0].size / 1024 / 1024; // in MB
                const fileName = this.files[0].name;
                
                // Check file size (max 5MB)
                if (fileSize > 5) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB');
                    this.value = '';
                    return;
                }

                // Show file name
                const fileLabel = this.nextElementSibling;
                if (fileLabel && fileLabel.tagName === 'SMALL') {
                    const originalText = fileLabel.textContent;
                    fileLabel.textContent = `File terpilih: ${fileName}`;
                    fileLabel.style.color = '#059669';
                    fileLabel.style.fontWeight = 'bold';
                }
            }
        });
    });

    // Confirmation dialogs for delete/important actions
    const dangerButtons = document.querySelectorAll('[data-confirm]');
    dangerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Apakah Anda yakin?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Table row highlight on hover
    const tableRows = document.querySelectorAll('.table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f3f4f6';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Search input real-time feedback
    const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.style.backgroundColor = '#eff6ff';
            } else {
                this.style.backgroundColor = '';
            }
        });
    });

    // Animate stats on scroll
    const statNumbers = document.querySelectorAll('.stat-number');
    if (statNumbers.length > 0) {
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const animateNumber = (element) => {
            const target = parseInt(element.textContent);
            const duration = 1000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 16);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateNumber(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        statNumbers.forEach(stat => observer.observe(stat));
    }

    // Password visibility toggle
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        toggleBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 1rem;
            padding: 5px;
        `;

        toggleBtn.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });

        wrapper.appendChild(toggleBtn);
        input.style.paddingRight = '40px';
    });

    // Loading state for forms
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.closest('form')?.addEventListener('submit', function() {
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            }, 5000);
        });
    });

    // Character counter for textarea
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        if (maxLength) {
            const counter = document.createElement('div');
            counter.style.cssText = 'text-align: right; color: #6b7280; font-size: 0.85rem; margin-top: 5px;';
            counter.textContent = `0 / ${maxLength}`;
            textarea.parentNode.appendChild(counter);

            textarea.addEventListener('input', function() {
                const length = this.value.length;
                counter.textContent = `${length} / ${maxLength}`;
                
                if (length >= maxLength * 0.9) {
                    counter.style.color = '#ef4444';
                } else {
                    counter.style.color = '#6b7280';
                }
            });
        }
    });

    // Print functionality
    window.printPage = function() {
        window.print();
    };

    // Export table to CSV
    window.exportTableToCSV = function(tableId, filename = 'data.csv') {
        const table = document.getElementById(tableId) || document.querySelector('.table');
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = [];
            cols.forEach(col => {
                csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
            });
            csv.push(csvRow.join(','));
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    console.log('Sistem Komplain FST - Ready!');
});