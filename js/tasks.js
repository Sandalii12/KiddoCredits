// js/tasks.js — page-specific loader for the universal SideCard
document.addEventListener("DOMContentLoaded", () => {

  // Build dynamic form fields for tasks (child options passed in)
  function getTaskFormHTML(childrenOptionsHTML = "") {
    return `
      <label>Child</label>
      <select name="child_id" required>
        <option value="">Select child</option>
        ${childrenOptionsHTML}
      </select>

      <label>Task Title</label>
      <input type="text" name="task_title" required>

      <label>Task Description</label>
      <textarea name="task_desc" rows="3"></textarea>

      <label>Points</label>
      <input type="number" name="task_points" min="1" required>

      <label>Due Date</label>
      <input type="date" name="task_duedate" required>
    `;
  }

  // Read server-rendered children options
  const childrenOptionsEl = document.getElementById('childrenOptions');
  const childrenOptionsHTML = childrenOptionsEl ? childrenOptionsEl.innerHTML : '';

  // Open Assign Task (Add)
  const openAssignBtn = document.getElementById('openAssignModal');
  if (openAssignBtn) {
    openAssignBtn.addEventListener('click', () => {
      window.SideCard.open({
        title: "Assign Task",
        mode: "add_task",
        entityId: 0,
        innerHTML: getTaskFormHTML(childrenOptionsHTML),
        focusSelector: "input[name='task_title']"
      });
    });
  }

  // Update Task buttons
  document.querySelectorAll(".btn-update").forEach(btn => {
    btn.addEventListener("click", () => {
      const data = {
        taskid: btn.dataset.taskid || "0",
        childid: btn.dataset.childid || "",
        title: btn.dataset.title || "",
        desc: btn.dataset.desc || "",
        points: btn.dataset.points || "",
        duedate: btn.dataset.duedate || ""
      };

      window.SideCard.open({
        title: "Update Task",
        mode: "update_task",
        entityId: data.taskid,
        innerHTML: getTaskFormHTML(childrenOptionsHTML),
        focusSelector: "input[name='task_title']"
      });

      // Prefill values once fields are injected
      setTimeout(() => {
        const f = window.SideCard.formElement;
        if (!f) return;
        if (f.querySelector("input[name='task_id']")) f.querySelector("input[name='task_id']").value = data.taskid;
        // select
        const sel = f.querySelector("select[name='child_id']");
        if (sel) sel.value = data.childid;
        const titleInput = f.querySelector("input[name='task_title']");
        if (titleInput) titleInput.value = data.title;
        const ta = f.querySelector("textarea[name='task_desc']");
        if (ta) ta.value = data.desc;
        const pts = f.querySelector("input[name='task_points']");
        if (pts) pts.value = data.points;
        const dd = f.querySelector("input[name='task_duedate']");
        if (dd) dd.value = data.duedate;
        // set hidden mode/task id properly
        if (f.mode) f.mode.value = "update_task";
        if (f.task_id) f.task_id.value = data.taskid;
      }, 25);
    });
  });

});
