/**
 * =========================
 * Import APP — FilePond + AJAX
 * =========================
 */

// ── Toast Notification Helper ──
function showToast(message, type = 'success') {
    // Remove any existing toast
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${type === 'success' ? '✓' : '✕'}</span>
        <span class="toast-message">${message}</span>
    `;
    document.body.appendChild(toast);

    // Trigger reflow then add visible class for animation
    requestAnimationFrame(() => toast.classList.add('toast-visible'));

    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        toast.classList.remove('toast-visible');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);
}

// ── FilePond Setup ──
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

const pond = FilePond.create(document.querySelector('.file-upload-multiple'), {
    allowMultiple: false,
    maxFiles: 1,
    acceptedFileTypes: ['.csv'],
    fileValidateTypeDetectType: (source, type) => new Promise((resolve, reject) => {
        if (source.name && source.name.toLowerCase().endsWith('.csv')) {
            resolve('.csv');
        } else {
            const ext = source.name ? '.' + source.name.split('.').pop().toLowerCase() : type;
            resolve(ext);
        }
    }),
    fileValidateTypeLabelExpectedTypes: 'Only .csv files are allowed',
    maxFileSize: '2MB',
    labelIdle: 'Drag & Drop your CSV file or <span class="filepond--label-action">Browse</span>',
});

// ── Import Button Logic ──
const importBtn = document.getElementById('import-btn');

pond.on('addfile', (error, file) => { 
    if (error) {
        importBtn.disabled = true;
    } else {
        importBtn.disabled = false; 
    }
});

pond.on('removefile', () => { 
    importBtn.disabled = pond.getFiles().length === 0 || !!pond.getFiles().find(f => f.status !== 2); 
    // Status 2 is completely loaded securely and ready.
});

importBtn.addEventListener('click', async () => {
    const file = pond.getFiles()[0];
    if (!file) return;

    importBtn.disabled = true;
    importBtn.textContent = 'Importing...';

    const formData = new FormData();
    formData.append('csv_file', file.file);

    try {
        const res = await fetch('/import-app', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            pond.removeFiles();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        importBtn.disabled = false;
        importBtn.textContent = 'Import';
    }
});
