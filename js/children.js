// js/children.js
document.addEventListener('DOMContentLoaded', function() {

    const openBtn = document.getElementById('openAddChildModal');
    const closeBtn = document.getElementById('closeAddChildModal');
    const cancelBtn = document.getElementById('cancelAddChild');
    const modal = document.getElementById('addChildModal');

    function openModal(){
        modal.style.display = 'flex';
        // focus first input
        const firstInput = modal.querySelector('input[name="child_name"]');
        if(firstInput) firstInput.focus();
    }
    function closeModal(){
        modal.style.display = 'none';
    }

    if(openBtn) openBtn.addEventListener('click', openModal);
    if(closeBtn) closeBtn.addEventListener('click', closeModal);
    if(cancelBtn) cancelBtn.addEventListener('click', closeModal);

    // close on overlay click
    modal.addEventListener('click', function(e){
        if(e.target === modal){
            closeModal();
        }
    });
});

// confirm delete
function confirmDeleteChild(e, childId) {
    e.preventDefault();
    const ok = confirm("Are you sure you want to remove this child? This action cannot be undone.");
    if (ok) {
        // submit the form
        e.target.submit();
    }
    return false;
}
