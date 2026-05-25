import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const AVATAR_SIZE = 400;

export function initAvatarCropper() {
    const fileInput = document.getElementById('avatar_file');
    const cropWrap = document.getElementById('avatar-crop-wrap');
    const cropImage = document.getElementById('avatar-crop-image');
    const hiddenData = document.getElementById('avatar_data');
    const removeFlag = document.getElementById('avatar_remove');
    const btnApply = document.getElementById('avatar-aplicar-recorte');
    const btnClear = document.getElementById('avatar-quitar');
    const avatarDisplay = document.getElementById('perfil-avatar-display');
    const avatarInitial = document.getElementById('perfil-avatar-initial');
    const btnChange = document.getElementById('avatar-cambiar-btn');

    if (!fileInput || !avatarDisplay) {
        return;
    }

    let cropper = null;
    const initialUrl = avatarDisplay.dataset.url || '';

    function destroyCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (cropWrap) {
            cropWrap.hidden = true;
        }
        if (cropImage) {
            cropImage.src = '';
        }
    }

    function mostrarAvatar(src) {
        if (src) {
            avatarDisplay.src = src;
            avatarDisplay.hidden = false;
            if (avatarInitial) {
                avatarInitial.hidden = true;
            }
        } else {
            avatarDisplay.hidden = true;
            avatarDisplay.removeAttribute('src');
            if (avatarInitial) {
                avatarInitial.hidden = false;
            }
        }
    }

    btnChange?.addEventListener('click', () => fileInput.click());

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
            cropWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };
        reader.readAsDataURL(file);
    });

    btnApply?.addEventListener('click', () => {
        if (!cropper) {
            return;
        }
        const canvas = cropper.getCroppedCanvas({
            width: AVATAR_SIZE,
            height: AVATAR_SIZE,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        hiddenData.value = dataUrl;
        mostrarAvatar(dataUrl);
        destroyCropper();
        fileInput.value = '';
    });

    btnClear?.addEventListener('click', () => {
        destroyCropper();
        fileInput.value = '';
        hiddenData.value = '';
        removeFlag.value = '1';
        mostrarAvatar(null);
    });

    if (initialUrl) {
        mostrarAvatar(initialUrl);
    }
}
