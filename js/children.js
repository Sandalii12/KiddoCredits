// js/children.js — use SideCard for Add Child
document.addEventListener("DOMContentLoaded", () => {

    console.log("children.js loaded");

    const openBtn = document.getElementById("openAddChild");
    console.log("openAddChild button =", openBtn);

    if (!openBtn) {
        console.error("ERROR: #openAddChild not found in DOM at runtime.");
        return;
    }

    function getChildFormHTML() {
        return `
            <label>Child Name</label>
            <input type="text" name="child_name" required>

            <label>Username</label>
            <input type="text" name="child_username" required>

            <label>Password</label>
            <input type="password" name="child_password" required>
        `;
    }

    openBtn.addEventListener("click", () => {

        console.log("Add child clicked — opening SideCard");

        window.SideCard.open({
            title: "Add Child",
            mode: "add_child",
            entityId: 0,
            innerHTML: getChildFormHTML(),
            focusSelector: "input[name='child_name']"
        });
    });
});
