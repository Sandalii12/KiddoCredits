// js/tasks.js
document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('openAssignModal');
    const assignModal = document.getElementById('assignModal');
    const closeModalBtn = document.getElementById('closeAssignModal');
    const cancelAssignBtn = document.getElementById('cancelAssign');
    const assignForm = document.getElementById('assignForm');
    const modalTitle = document.getElementById('modalTitle');

    function openModal() {
        assignModal.style.display = 'flex';
        assignModal.setAttribute('aria-hidden', 'false');
        // reset form to add by default
        assignForm.mode.value = 'add_task';
        assignForm.task_id.value = '0';
        assignForm.reset();
        modalTitle.innerText = 'Assign Task';
    }
    function closeModal() {
        assignModal.style.display = 'none';
        assignModal.setAttribute('aria-hidden', 'true');
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (cancelAssignBtn) cancelAssignBtn.addEventListener('click', closeModal);
    // close on overlay click
    assignModal.addEventListener('click', function(e){
        if (e.target === assignModal) closeModal();
    });

    // Attach update buttons (they exist on page load)
    function attachUpdateButtons() {
        const updateBtns = document.querySelectorAll('.btn-update');
        updateBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                // read data attributes from button
                const taskId = this.getAttribute('data-taskid') || '0';
                const childId = this.getAttribute('data-childid') || '';
                const title = this.getAttribute('data-title') || '';
                const desc = this.getAttribute('data-desc') || '';
                const points = this.getAttribute('data-points') || '';
                const duedate = this.getAttribute('data-duedate') || '';

                // fill form
                assignForm.mode.value = 'update_task';
                assignForm.task_id.value = taskId;
                assignForm.child_id.value = childId;
                assignForm.task_title.value = title;
                assignForm.task_desc.value = desc;
                assignForm.task_points.value = points;
                assignForm.task_duedate.value = duedate;

                modalTitle.innerText = 'Update Task';
                assignModal.style.display = 'flex';
                assignModal.setAttribute('aria-hidden', 'false');
            });
        });
    }

    attachUpdateButtons();
});
