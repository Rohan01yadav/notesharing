/**
 * Student Notes Sharing Platform
 * Client-Side JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Bootstrap Tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // 2. Dynamic Department -> Subject Cascading Dropdown
    const deptSelect = document.getElementById('department_select');
    const subjectSelect = document.getElementById('subject_select');

    if (deptSelect && subjectSelect) {
        deptSelect.addEventListener('change', function () {
            const deptId = this.value;
            const options = subjectSelect.querySelectorAll('option');

            let visibleCount = 0;
            options.forEach(opt => {
                const optDept = opt.getAttribute('data-department');
                if (!optDept || !deptId || optDept === deptId) {
                    opt.style.display = '';
                    visibleCount++;
                } else {
                    opt.style.display = 'none';
                }
            });

            // Reset selection to default if current is hidden
            const currentSelected = subjectSelect.options[subjectSelect.selectedIndex];
            if (currentSelected && currentSelected.style.display === 'none') {
                subjectSelect.value = '';
            }
        });
    }

    // 3. Drag and Drop File Upload Enhancement
    const dropzone = document.getElementById('note_dropzone');
    const fileInput = document.getElementById('note_file_input');
    const fileDetailsBox = document.getElementById('file_details_box');

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });

        function handleFileSelect(file) {
            if (!file) return;

            // Validate extension
            if (!file.name.toLowerCase().endsWith('.pdf')) {
                showToast('Invalid File Type', 'Only PDF files are permitted.', 'error');
                fileInput.value = '';
                if (fileDetailsBox) fileDetailsBox.classList.add('d-none');
                return;
            }

            // Validate size (25MB)
            if (file.size > 25 * 1024 * 1024) {
                showToast('File Too Large', 'Maximum allowed file size is 25MB.', 'error');
                fileInput.value = '';
                if (fileDetailsBox) fileDetailsBox.classList.add('d-none');
                return;
            }

            if (fileDetailsBox) {
                const nameEl = fileDetailsBox.querySelector('.file-name');
                const sizeEl = fileDetailsBox.querySelector('.file-size');
                if (nameEl) nameEl.textContent = file.name;
                if (sizeEl) sizeEl.textContent = formatBytes(file.size);
                fileDetailsBox.classList.remove('d-none');
            }
        }
    }

    // 4. AJAX Bookmark Toggle
    document.querySelectorAll('.btn-bookmark-toggle').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const noteId = this.getAttribute('data-note-id');
            const icon = this.querySelector('i');
            const self = this;

            if (!noteId) return;

            // Send POST request
            const formData = new FormData();
            formData.append('note_id', noteId);

            fetch(window.APP_BASE_URL + '/actions/bookmark.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'unauthorized') {
                    showToast('Login Required', 'Please login to bookmark notes.', 'warning');
                    setTimeout(() => {
                        window.location.href = window.APP_BASE_URL + '/login.php';
                    }, 1200);
                    return;
                }

                if (data.status === 'success') {
                    if (data.bookmarked) {
                        self.classList.add('active');
                        if (icon) {
                            icon.classList.remove('fa-regular');
                            icon.classList.add('fa-solid');
                        }
                        showToast('Bookmarked', 'Note saved to your bookmarks!', 'success');
                    } else {
                        self.classList.remove('active');
                        if (icon) {
                            icon.classList.remove('fa-solid');
                            icon.classList.add('fa-regular');
                        }
                        showToast('Removed', 'Note removed from your bookmarks.', 'info');
                    }
                } else {
                    showToast('Error', data.message || 'Action failed', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error', 'Unable to toggle bookmark. Try again.', 'error');
            });
        });
    });

    // 5. Delete Confirmation Modal/Prompt
    document.querySelectorAll('.btn-confirm-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            const title = this.getAttribute('data-title') || 'this item';
            if (!confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });

    // Utility: Format Bytes
    function formatBytes(bytes, decimals = 1) {
        if (!+bytes) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }

    // Global Toast Notification Creator
    window.showToast = function (title, message, type = 'info') {
        let container = document.getElementById('global-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'global-toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const iconMap = {
            success: 'fa-circle-check text-success',
            error: 'fa-circle-exclamation text-danger',
            warning: 'fa-triangle-exclamation text-warning',
            info: 'fa-circle-info text-primary'
        };

        const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="background:#ffffff; border-radius:12px;">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fa-solid ${iconMap[type] || iconMap.info} fs-5"></i>
                        <div>
                            <div class="fw-bold text-dark small">${title}</div>
                            <div class="text-muted small">${message}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }
    };
});
