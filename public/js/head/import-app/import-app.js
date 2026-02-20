/**
 * =========================
 * Import APP — FilePond + AJAX
 * =========================
 */

// Register plugins
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// Create single-file FilePond instance (CSV only)
const pond = FilePond.create(document.querySelector('.file-upload-multiple'), {
    allowMultiple: false,
    maxFiles: 1,
    allowFileTypeValidation: false, // OS native picker handles '.csv' filter and backend validates securely
    maxFileSize: '2MB',
    labelIdle: 'Drag & Drop your CSV file or <span class="filepond--label-action">Browse</span>',
});

// Import button reference
const importBtn = document.getElementById('import-btn');

// Enable / disable Import button based on file state
pond.on('addfile', () => { importBtn.disabled = false; });
pond.on('removefile', () => { importBtn.disabled = pond.getFiles().length === 0; });

// Handle Import button click
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
            alert(data.message);
            pond.removeFiles();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (err) {
        alert('Network error. Please try again.');
    } finally {
        importBtn.disabled = false;
        importBtn.textContent = 'Import';
    }
});
