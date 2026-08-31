document.addEventListener('DOMContentLoaded', () => {
    const send = async(data) => (await (await fetch((window.MONTHSARY_BASE_PATH || '') + '/pages/api_admin.php', { method: 'POST', body: data })).json());
    document.querySelectorAll('[data-admin-action="save"]').forEach(button => button.addEventListener('click', async() => {
        const row = button.closest('[data-admin-pet]');
        const data = new FormData();
        data.append('action', 'save');
        data.append('pet_id', row.dataset.adminPet);
        data.append('csrf_token', window.MONTHSARY_CSRF || '');
        row.querySelectorAll('input').forEach(input => data.append(input.name, input.value));
        button.disabled = true;
        const result = await send(data);
        button.disabled = false;
        if (!result.ok) { alert(result.message); return; }
        row.querySelector('[data-admin-mood]').textContent = result.pet.mood;
        button.textContent = 'Saved';
        setTimeout(() => button.textContent = 'Save', 1200);
    }));
    const resetButton = document.querySelector('[data-admin-action="reset"]');
    if (resetButton) resetButton.addEventListener('click', async event => {
        if (!confirm('Reset every pet to full stats?')) return;
        const data = new FormData();
        data.append('action', 'reset');
        data.append('csrf_token', window.MONTHSARY_CSRF || '');
        const result = await send(data);
        alert(result.message);
        if (result.ok) location.reload();
    });
});