// js/tasks.js
document.addEventListener('DOMContentLoaded', function () {
    // tabs
    const pills = document.querySelectorAll('.tasks-pill');
    pills.forEach(p => p.addEventListener('click', function () {
        pills.forEach(x => x.classList.remove('active'));
        this.classList.add('active');

        const target = this.getAttribute('data-target');
        document.querySelectorAll('.tasks-section').forEach(s => s.style.display = 'none');
        const el = document.getElementById(target);
        if (el) el.style.display = 'block';
    }));

    // modal
    const modal = document.getElementById('taskModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalDesc = document.getElementById('modalDesc');
    const modalPoints = document.getElementById('modalPoints');
    const modalDue = document.getElementById('modalDue');
    const modalStatus = document.getElementById('modalStatus');
    const closeBtn = modal ? modal.querySelector('.modal-close') : null;

    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', function () {
            const title = this.dataset.title || '';
            const desc = this.dataset.desc || '';
            const points = this.dataset.points || '';
            const due = this.dataset.due || '';
            const status = this.dataset.status || '';

            modalTitle.textContent = title;
            modalDesc.innerHTML = desc ? desc.replace(/\n/g,'<br>') : '<em>No description</em>';
            modalPoints.textContent = points ? (points + ' pts') : '';
            modalDue.textContent = due ? ('Due: ' + due) : '';
            modalStatus.innerHTML = status ? ('<span class="task-status ' + (status === 'completed' ? 'status-completed' : (status === 'waiting_for_parent' ? 'status-waiting' : 'status-pending')) + '">' + (status === 'completed' ? 'Completed' : (status === 'waiting_for_parent' ? 'Waiting Approval' : 'Pending')) + '</span>') : '';

            if (modal) modal.style.display = 'flex';
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', function () {
        if (modal) modal.style.display = 'none';
    });

    // close modal when clicking outside content
    if (modal) modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.style.display = 'none';
    });
});
