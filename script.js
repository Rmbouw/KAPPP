const pageHome = document.getElementById("pageHome");
const pageUpload = document.getElementById("pageUpload");
const klikSini = document.getElementById("klikSini");
const loading = document.getElementById("loading");

const fileInput = document.getElementById("fileInput");
const btnUpload = document.getElementById("btnUpload");
const fileList = document.getElementById("fileList");
const preview = document.getElementById("preview");
const logout = document.getElementById("logout");

let db;
let currentURL = null;
let currentVideo = null;

const request = indexedDB.open("FILE_TUGAS_DB", 1);

request.onupgradeneeded = e => {
    db = e.target.result;
    db.createObjectStore("files", { keyPath: "id", autoIncrement: true });
};

request.onsuccess = e => {
    db = e.target.result;
    loadFiles();
};

klikSini.onclick = e => {
    e.preventDefault();
    loading.style.display = "block";
    setTimeout(() => {
        pageHome.classList.remove("show");
        pageUpload.classList.add("show");
    }, 1000);
};

btnUpload.onclick = () => {
    const tx = db.transaction("files", "readwrite");
    const store = tx.objectStore("files");

    for (let f of fileInput.files) {

        if (f.size > 1024 * 1024 * 1024) {
            alert("Ukuran maksimal 1GB");
            continue;
        }

        store.add({
            file: f,
            time: new Date(),
            sizeMB: (f.size / (1024 * 1024)).toFixed(2)
        });
    }

    fileInput.value = "";
    tx.oncomplete = loadFiles;
};

function loadFiles() {
    fileList.innerHTML = "";
    const tx = db.transaction("files", "readonly");
    const store = tx.objectStore("files");

    store.openCursor(null, "prev").onsuccess = e => {
        const cursor = e.target.result;
        if (!cursor) return;

        const data = cursor.value;
        const t = new Date(data.time);

        fileList.innerHTML += `
        <li>
            <div>
                <strong><i class="fa-solid fa-file"></i> ${data.file.name}</strong><br>
                <small>
                    ${t.toLocaleDateString("id-ID")}
                    ${t.toLocaleTimeString("id-ID")}
                    ${data.sizeMB} MB
                </small>
            </div>
            <div class="actions">
                <a href="#" onclick="lihat(${cursor.key})">
                    <i class="fa-solid fa-eye"></i> Lihat
                </a>
                <a href="#" onclick="hapus(${cursor.key})">
                    <i class="fa-solid fa-trash"></i> Hapus
                </a>
            </div>
        </li>`;

        cursor.continue();
    };
}

function lihat(id) {
    const tx = db.transaction("files", "readonly");
    const store = tx.objectStore("files");

    store.get(id).onsuccess = e => {
        const data = e.target.result;
        currentURL = URL.createObjectURL(data.file);

        preview.style.display = "block";
        preview.innerHTML = `
            <a href="#" onclick="kembali()">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <a href="${currentURL}" download="${data.file.name}">
                <i class="fa-solid fa-download"></i> Unduh
            </a>
            <hr>
            ${tampil(data.file, currentURL)}
        `;

        currentVideo = document.getElementById("videoPlayer");
    };
}

function tampil(f, url) {
    if (f.type.includes("pdf"))
        return `<iframe src="${url}" width="100%" height="500"></iframe>`;

    if (f.type.includes("image"))
        return `<img src="${url}" style="max-width:100%">`;

    if (f.type.includes("video"))
        return `
        <video id="videoPlayer" controls style="max-width:100%">
            <source src="${url}">
        </video>`;

    return "Tidak bisa ditampilkan";
}
function kembali() {
    if (currentVideo) {
        currentVideo.pause();
        currentVideo.src = "";
        currentVideo.load();
        currentVideo = null;
    }

    if (currentURL) {
        URL.revokeObjectURL(currentURL);
        currentURL = null;
    }

    preview.style.display = "none";
    preview.innerHTML = "";
}
function hapus(id) {
    if (!confirm("Hapus file ini?")) return;

    const tx = db.transaction("files", "readwrite");
    const store = tx.objectStore("files");

    store.delete(id);
    tx.oncomplete = () => {
        kembali();
        loadFiles();
    };
}

logout.onclick = e => {
    e.preventDefault();
    location.reload();
};
