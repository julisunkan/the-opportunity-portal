// ... (previous code remains unchanged)

function initializeAdminDashboard() {
    const adminActions = $('#admin-actions');
    if (!adminActions) return;

    adminActions.addEventListener('click', async (e) => {
        if (e.target.classList.contains('delete-user') || e.target.classList.contains('delete-job')) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this item?')) return;

            const form = e.target.closest('form');
            try {
                const result = await fetchData(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });
                if (result.success) {
                    e.target.closest('tr').remove();
                    alert(result.message);
                } else {
                    alert(result.message || 'An error occurred. Please try again.');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        }
    });
}

// ... (rest of the code)