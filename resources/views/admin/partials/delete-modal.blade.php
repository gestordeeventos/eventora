<div id="admin-delete-modal" class="admin-modal" hidden>
    <div class="admin-modal-backdrop" data-close-modal></div>
    <div class="admin-modal-dialog" role="dialog" aria-labelledby="delete-modal-title">
        <h2 id="delete-modal-title" class="font-display admin-modal-title">Eliminar evento</h2>
        <p class="admin-modal-text">¿Estás segura de que deseas eliminar este evento permanentemente? Esta acción no se puede deshacer.</p>
        <p class="admin-modal-event-name" id="delete-modal-event-name"></p>
        <form id="admin-delete-form" method="POST" class="admin-modal-actions">
            @csrf
            @method('DELETE')
            <button type="button" class="btn-admin-secondary" data-close-modal>Cancelar</button>
            <button type="submit" class="btn-admin-danger">Eliminar permanentemente</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('admin-delete-modal');
    const form = document.getElementById('admin-delete-form');
    const nameEl = document.getElementById('delete-modal-event-name');

    document.querySelectorAll('[data-delete-event]').forEach(btn => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.deleteUrl;
            nameEl.textContent = btn.dataset.eventTitle || '';
            modal.hidden = false;
            document.body.classList.add('modal-open');
        });
    });

    modal.querySelectorAll('[data-close-modal]').forEach(el => {
        el.addEventListener('click', () => {
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        });
    });
});
</script>
