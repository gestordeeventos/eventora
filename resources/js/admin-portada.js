import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const PORTADA_SIZE = 800;

export function initEventoPortada() {
    const fileInput = document.getElementById('portada_file');
    const cropWrap = document.getElementById('portada-crop-wrap');
    const cropImage = document.getElementById('portada-crop-image');
    const previewWrap = document.getElementById('portada-preview-wrap');
    const previewImg = document.getElementById('portada-preview');
    const hiddenData = document.getElementById('portada_data');
    const btnApply = document.getElementById('portada-aplicar-recorte');
    const btnClear = document.getElementById('portada-quitar');
    const removeFlag = document.getElementById('portada_remove');

    if (!fileInput || !cropImage) {
        return;
    }

    let cropper = null;

    function destroyCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropWrap.hidden = true;
        cropImage.src = '';
    }

    function setPreview(src) {
        if (src) {
            previewImg.src = src;
            previewWrap.hidden = false;
        } else {
            previewImg.removeAttribute('src');
            previewWrap.hidden = true;
        }
    }

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) {
            return;
        }

        const okTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!okTypes.includes(file.type)) {
            alert('Solo se permiten imágenes JPG o PNG.');
            fileInput.value = '';
            return;
        }

        if (file.size > 8 * 1024 * 1024) {
            alert('La imagen original no puede superar 8 MB.');
            fileInput.value = '';
            return;
        }

        removeFlag.value = '0';
        hiddenData.value = '';

        const reader = new FileReader();
        reader.onload = () => {
            destroyCropper();
            cropImage.src = reader.result;
            cropWrap.hidden = false;
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                guides: true,
                background: false,
                zoomable: true,
                scalable: false,
                rotatable: false,
            });
        };
        reader.readAsDataURL(file);
    });

    btnApply?.addEventListener('click', () => {
        if (!cropper) {
            return;
        }
        const canvas = cropper.getCroppedCanvas({
            width: PORTADA_SIZE,
            height: PORTADA_SIZE,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        hiddenData.value = dataUrl;
        setPreview(dataUrl);
        destroyCropper();
        fileInput.value = '';
    });

    btnClear?.addEventListener('click', () => {
        destroyCropper();
        fileInput.value = '';
        hiddenData.value = '';
        removeFlag.value = '1';
        setPreview(null);
    });

    window.eventoPortada = {
        loadExisting(url) {
            if (url) {
                setPreview(url);
                removeFlag.value = '0';
                hiddenData.value = '';
            } else {
                setPreview(null);
            }
        },
        reset() {
            destroyCropper();
            fileInput.value = '';
            hiddenData.value = '';
            removeFlag.value = '0';
            setPreview(null);
        },
    };

    const initial = previewImg?.dataset?.initial;
    if (initial) {
        window.eventoPortada.loadExisting(initial);
    }
}

document.addEventListener('DOMContentLoaded', initEventoPortada);
