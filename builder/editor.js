/* ==========================================================
   PREVIEW IFRAME
========================================================== */
const iframe = document.getElementById("previewFrame");
let liveSiteUrl = null;

/* TOP BUTTONS */
const desktopView = document.getElementById("desktopView");
const tabletView = document.getElementById("tabletView");
const mobileView = document.getElementById("mobileView");

const saveBtn = document.getElementById("saveBtn");
const publishBtn = document.getElementById("publishBtn");
const downloadBtn = document.getElementById("downloadBtn");

/* CHAT */
const chatBox = document.getElementById("chatBox");
const aiInput = document.getElementById("aiUserInput");
const aiSendBtn = document.getElementById("aiSendBtn");

function addUserMessage(t){let d=document.createElement("div");d.className="msg user-msg";d.textContent=t;chatBox.appendChild(d);chatBox.scrollTop=chatBox.scrollHeight}
function addAIMessage(t){let d=document.createElement("div");d.className="msg ai-msg";d.textContent=t;chatBox.appendChild(d);chatBox.scrollTop=chatBox.scrollHeight}

aiSendBtn.onclick = sendAIMessage;
aiInput.onkeypress = e => { if(e.key === "Enter") sendAIMessage(); };

/* SEND AI MESSAGE */
function sendAIMessage() {
    const text = aiInput.value.trim();
    if (!text) return;

    addUserMessage(text);
    aiInput.value = "";

    const loadingMsg = document.createElement("div");
    loadingMsg.className = "msg ai-msg";
    loadingMsg.id = "aiLoading";
    loadingMsg.textContent = "snapsite AI is generating your website...";
    chatBox.appendChild(loadingMsg);
    chatBox.scrollTop = chatBox.scrollHeight;

    const form = new FormData();
    form.append("message", text);
    form.append("project", projectName);

    fetch("ai_edit.php", { method: "POST", body: form })
        .then(r => r.json())
        .then(data => {
            const ld = document.getElementById("aiLoading");
            if (ld) ld.remove();

            if (!data.html) {
                addAIMessage("AI error generating update.");
                return;
            }

            loadNewHTML(data.html);
            addAIMessage("AI updated HTML, CSS, and JS successfully!\n" + data.reply);
        })
        .catch(() => {
            const ld = document.getElementById("aiLoading");
            if (ld) ld.remove();
            addAIMessage("AI error. Please try again.");
        });
}

// loading without the refresh the website 

function loadNewHTML(html) {

    const frame = document.getElementById("previewFrame");
    const iframeDoc = frame.contentWindow.document;

    // 🔥 STEP 1 — Reset iframe completely
    iframeDoc.open();
    iframeDoc.write("<!DOCTYPE html><html><head></head><body></body></html>");
    iframeDoc.close();

    // 🔥 STEP 2 — Add <base> again to fix CSS & JS paths
    const cleanProject = projectName.toLowerCase().replace(/ /g, "-");
    const baseURL = `http://localhost/snapsite/builder/projects/${username}/${cleanProject}`;

    html = html.replace("<head>", `<head><base href="${baseURL}/">`);

    // 🔥 STEP 3 — Write the NEW HTML
    iframeDoc.open();
    iframeDoc.write(html);
    iframeDoc.close();

    // 🔥 STEP 4 — Reload CSS (force refresh)
    const links = iframeDoc.querySelectorAll("link[rel='stylesheet']");
    links.forEach(link => {
        link.href = link.href.split("?")[0] + "?" + Date.now();
    });

    // 🔥 STEP 5 — Reload JS (force refresh)
    const scripts = iframeDoc.querySelectorAll("script");
    scripts.forEach(s => {
        const newScript = iframeDoc.createElement("script");
        newScript.src = s.src + "?" + Date.now();
        iframeDoc.body.appendChild(newScript);
        s.remove();
    });
}


/* GET FULL UPDATED HTML */

function applyAIUpdate(selector, newText) {
    const doc = iframe.contentDocument;
    let element = doc.querySelector(selector);

    if (element) {
        element.textContent = newText;
        element.style.background = "yellow";
        setTimeout(() => element.style.background = "none", 600);
    } else {
        addAIMessage("Element not found: " + selector);
    }
}


/* PUBLISH */
publishBtn.onclick = () => {
    // If already published once → open the live site
    if (publishBtn.dataset.mode === "view" && liveSiteUrl) {
        window.open(liveSiteUrl, "_blank");
        return;
    }

    // Get the latest HTML inside the iframe
    function getUpdatedHTML() {
    try {
        return iframe.contentDocument.documentElement.outerHTML;
    } catch (e) {
        console.error("HTML extraction error:", e);
        return "";
    }
}

    const form = new FormData();
    form.append("project", projectName);
    form.append("html", html);

    // Optional: message in left chat
    addAIMessage("Publishing your website...");

    fetch("publish.php", {
        method: "POST",
        body: form
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "ok") {
            liveSiteUrl = data.url;

            // Pop-up
            alert("Your website is live!");

            // Chat message
            addAIMessage("Your website is live! Click 'View site' to open it.");

            // Change button to 'View site'
            publishBtn.textContent = "View site";
            publishBtn.dataset.mode = "view";
        } else {
            addAIMessage("Publish failed: " + (data.message || "Unknown error"));
        }
    })
    .catch(err => {
        console.error(err);
        addAIMessage("Publish error. Please try again.");
    });
};

/* DOWNLOAD ZIP */
downloadBtn.onclick = () => {
    window.location.href = "download.php?project=" + projectName;
};

/* RESPONSIVE BUTTONS */
desktopView.onclick = ()=>{iframe.classList.remove("tablet","mobile")};
tabletView.onclick = ()=>{iframe.classList.add("tablet");iframe.classList.remove("mobile")};
mobileView.onclick = ()=>{iframe.classList.add("mobile");iframe.classList.remove("tablet")};

