<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="uploadFileModalLabel" style="font-size: 28px; color: #3b3f5c;">Upload File</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 px-4 pb-4">
                <p class="mb-4" style="color: #888ea8; font-size: 18px;">Add your files or documents here</p>
                
                <div class="upload-drop-zone d-flex flex-column align-items-center justify-content-center py-5 mb-4" id="dropZone" style="border: 2px dashed #d14d4d; border-radius: 15px; background: rgba(209, 77, 77, 0.02); transition: all 0.3s ease;">
                    <div class="upload-icon-wrapper mb-3">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#d14d4d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2V8H20" stroke="#d14d4d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 18V12M12 12L9 15M12 12L15 15" stroke="#d14d4d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <p class="mb-0" style="color: #3b3f5c; font-weight: 500; font-size: 18px;">Drop your files here, or <span class="cursor-pointer" id="browseFiles" style="color: #d14d4d; text-decoration: underline; font-weight: 600;">click to browse</span></p>
                    <input type="file" id="fileInput" class="d-none" multiple>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5 px-1">
                    <span class="text-muted d-flex align-items-center" style="font-size: 14px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info me-2 text-muted"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        Supported files: .docx, .png, .cvs, .pdf
                    </span>
                    <span class="text-muted" style="font-size: 14px;">Maximum size: 10MB</span>
                </div>

                <button type="button" class="btn w-100 py-3 fw-bold text-white shadow-none" style="background-color: #d14d4d; border-radius: 12px; font-size: 20px; transition: all 0.3s ease;">Upload</button>
            </div>
        </div>
    </div>
</div>
