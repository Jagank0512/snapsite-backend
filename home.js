

/* ============================================
   ACCOUNT MODAL (PROFILE)
============================================ */
// =============== PROFILE POPUP ===============

const navUser = document.getElementById("navUser");
const accountModal = document.getElementById("accountModal");
const closeAccountModal = document.getElementById("closeAccountModal");

navUser.addEventListener("click", () => {
    accountModal.classList.add("open");
});

closeAccountModal.addEventListener("click", () => {
    accountModal.classList.remove("open");
});

accountModal.addEventListener("click", (e) => {
    if (e.target === accountModal) {
        accountModal.classList.remove("open");
    }
});


// =============== PROFILE IMAGE UPLOAD ===============

const profileInput = document.getElementById("profileImageInput");
const profilePreview = document.getElementById("profileImagePreview");
const navbarProfilePic = document.getElementById("navbarProfilePic");

document.querySelector(".avatar-icon").addEventListener("click", () => {
    profileInput.click();
});

profileInput.addEventListener("change", () => {
    const file = profileInput.files[0];
    if (!file) return;

    // Preview in modal
    profilePreview.src = URL.createObjectURL(file);

    // Upload to server
    const formData = new FormData();
    formData.append("profile_image", file);

    fetch("upload_profile.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        console.log("Upload:", res);

        // Update navbar instantly
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.className = "nav-profile-img";

        navbarProfilePic.innerHTML = "";
        navbarProfilePic.appendChild(img);
    });
});


// =============== SIGN OUT ===============

document.getElementById("logoutBtn").addEventListener("click", () => {
    window.location.href = "logout.php";
});


// =============== DELETE ACCOUNT ===============

document.getElementById("deleteAccountBtn").addEventListener("click", () => {
    if (!confirm("Are you sure you want to delete your account?")) return;

    fetch("delete_account.php", { method: "POST" })
    .then(res => res.text())
    .then(txt => {
        alert("Account deleted.");
        window.location.href = "index.html";
    });
});

/* ============================================
   TYPING EFFECT
============================================ */
document.addEventListener("DOMContentLoaded", () => {
    const text = "Create your website in a minutes !";
    const typing = document.querySelector(".typing-text");
    let idx = 0;

    function type() {
        if (idx < text.length) {
            typing.textContent += text.charAt(idx);
            idx++;
            setTimeout(type, 55);
        } else {
            typing.classList.add("cursor-hide");
        }
    }
    type();
});


/* ============================================
   WELCOME TOAST
============================================ */
window.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("welcomeToast");

    if (!toast) return;

    setTimeout(() => {
        toast.classList.add("welcome-toast--visible");
    }, 400);

    setTimeout(() => {
        toast.classList.remove("welcome-toast--visible");
    }, 3500);
});


/* ============================================
   AI PROMPT → PROJECT NAME POPUP → GENERATE
============================================ */

const generateBtn = document.getElementById("generateBtn");
const loadingOverlay = document.getElementById("loadingOverlay");

/* STEP 1 — Validate Prompt & Ask Project Name */
generateBtn.addEventListener("click", () => {
    let userPrompt = document.getElementById("aiPrompt").value.trim();

    if (!userPrompt) {
        alert("Please describe your website.");
        return;
    }

    // Ask user for project name
    let projectName = prompt("Enter your project name:");

    if (!projectName || projectName.trim() === "") {
        alert("Project name is required.");
        return;
    }

    // Clean project name
    projectName = projectName.toLowerCase().replace(/[^a-z0-9\- ]/g, "").replace(/\s+/g, "-");

    sendPromptToAI(userPrompt, projectName);
});


/* STEP 2 — Send Prompt + Project Name to Backend */
function sendPromptToAI(prompt, projectName) {

    loadingOverlay.style.display = "flex";

    const formData = new FormData();
    formData.append("prompt", prompt);
    formData.append("project_name", projectName);

    fetch("builder/generate.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        loadingOverlay.style.display = "none";

        if (data.status === "success") {
            window.location.href = data.editor_url;
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        loadingOverlay.style.display = "none";
        alert("Request failed. Check console.");
        console.error(err);
    });
}


/* ==========================
   GUIDE STEP DROPDOWN
========================== */

document.addEventListener("DOMContentLoaded", () => {
    const steps = document.querySelectorAll(".guide-step");

    steps.forEach(step => {
        step.addEventListener("click", () => {
            
            // Close other steps
            steps.forEach(s => {
                if (s !== step) s.classList.remove("active");
            });

            // Toggle current step
            step.classList.toggle("active");
        });
    });
});

/* ==========================
   DELETE PROJECT
========================== */

function deleteProject(projectName) {
    if (!confirm("Are you sure you want to delete this project?")) return;

    fetch("builder/delete_project.php", {
        method: "POST",
        body: new URLSearchParams({ project: projectName })
    })
    .then(res => res.text())
    .then(res => {
        if (res === "DELETED") {
            alert("Project deleted successfully.");
            location.reload();
        } else {
            alert("Delete failed: " + res);
        }
    });
}


