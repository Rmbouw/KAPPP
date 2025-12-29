let files = JSON.parse(localStorage.getItem('uploadedFiles')) || [];

const mainPage = document.getElementById('main-page');
const fileManager = document.getElementById('file-manager');
const fileViewer = document.getElementById('file-viewer');
const loginBtn = document.getElementById('login-btn');
const uploadBtn = document.getElementById('upload-btn');
const fileInput = document.getElementById('file-input');
const fileList = document.getElementById('file-list');
const fileContent = document.getElementById('file-content');
const backBtn = document.getElementById('back-btn');
const downloadBtn = document.getElementById('download-btn');

loginBtn.addEventListener('click', () => {
    mainPage.style.display = 'none';
    fileManager.style.display = 'block';
    renderFileList();
});

uploadBtn.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    const selectedFiles = Array.from(e.target.files);
    selectedFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = (event) => {
            files.push({
                name: file.name,
                type: file.type,
                data: event.target.result
            });
            localStorage.setItem('uploadedFiles', JSON.stringify(files));
            renderFileList();
        };
        reader.readAsDataURL(file);
    });
});

function renderFileList() {
    fileList.innerHTML = '';
    files.forEach((file, index) => {
        const li = document.createElement('li');
        li.className = 'file-item';
        li.innerHTML = `
            <span class="file-name">${file.name}</span>
            <div class="file-actions">
                <button class="view-btn" onclick="viewFile(${index})">Lihat</button>
                <button class="delete-btn" onclick="deleteFile(${index})">Hapus</button>
            </div>
        `;
        fileList.appendChild(li);
    });
}

function viewFile(index) {
    const file = files[index];
    fileManager.style.display = 'none';
    fileViewer.style.display = 'block';

    if (file.type === 'application/pdf') {
        fileContent.innerHTML = `<embed src="${file.data}" type="application/pdf" class="pdf-viewer">`;
    } else if (file.type.startsWith('image/')) {
        fileContent.innerHTML = `<img src="${file.data}" class="image-viewer" style="width: auto; height: auto;">`;
    } else if (file.type.startsWith('video/')) {
        fileContent.innerHTML = `<video controls class="video-viewer" style="width: auto; height: auto;"><source src="${file.data}" type="${file.type}"></video>`;
    }
}

function deleteFile(index) {
    files.splice(index, 1);
    localStorage.setItem('uploadedFiles', JSON.stringify(files));
    renderFileList();
}

backBtn.addEventListener('click', () => {
    fileViewer.style.display = 'none';
    fileManager.style.display = 'block';
});

downloadBtn.addEventListener('click', () => {
    const fileData = fileContent.querySelector('embed, img, video').src;
    const link = document.createElement('a');
    link.href = fileData;
    link.download = files.find(f => f.data === fileData).name;
    link.click();
});
