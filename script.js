// ===== TAB SWITCHING =====
const tabSignin = document.getElementById("tab-signin");
const tabCreate = document.getElementById("tab-create");
const indicator = document.querySelector(".tab-indicator");

/* ============================
   BACKGROUND IMAGE SLIDER
============================ */

// Background carousel
const backgrounds = [
  "images/previews/bg1.jpg",
  "images/previews/bg3.jpg",
  "images/previews/bg2.jpg"
];
let index = 0;
const bg = document.getElementById('background');
function swapBackground() {
  bg.style.backgroundImage = `url('${backgrounds[index]}')`;
  index = (index + 1) % backgrounds.length;
}
swapBackground();
setInterval(swapBackground, 5000);

/* === CLEAR ALL INPUT FIELDS + OTP === */
function clearAllForms() {
    // Clear all input fields
    document.querySelectorAll("input").forEach(input => input.value = "");

    // Clear OTP containers
    if (document.getElementById("regOtpContainer"))
        document.getElementById("regOtpContainer").innerHTML = "";

    if (document.getElementById("forgotOtpContainer"))
        document.getElementById("forgotOtpContainer").innerHTML = "";

    // Remove reset form inputs also
    if (document.getElementById("resetForm"))
        document.querySelectorAll("#resetForm input").forEach(input => input.value = "");
}

/* Helper to activate tab */
function switchTab(activeTab, inactiveTab, moveX) {
    indicator.style.transform = `translateX(${moveX}%)`;
    activeTab.classList.add("active");
    inactiveTab.classList.remove("active");
}

/* Sign-in tab click */
tabSignin.onclick = () => {
    clearAllForms();
    switchTab(tabSignin, tabCreate, 0); // Move to left
};

/* Create account click */
tabCreate.onclick = () => {
    clearAllForms();
    switchTab(tabCreate, tabSignin, 100); // Move to right
};

const signinForm = document.getElementById("signinForm");
const createForm = document.getElementById("createForm");
const forgotForm = document.getElementById("forgotForm");
const resetForm = document.getElementById("resetForm");

// Switch to Login
function showSignin(){
    clearAllForms();
    indicator.style.transform = "translateX(0)";
    signinForm.classList.add("active");
    createForm.classList.remove("active");
    forgotForm.classList.remove("active");
    resetForm.classList.remove("active");
}
tabSignin.onclick = showSignin;

// Switch to Create
function showCreate(){
    clearAllForms();
    indicator.style.transform = "translateX(100%)";
    createForm.classList.add("active");
    signinForm.classList.remove("active");
    forgotForm.classList.remove("active");
    resetForm.classList.remove("active");
}
tabCreate.onclick = showCreate;

// Forgot link
document.getElementById("forgotLink").onclick = ()=>{
    clearAllForms();
    signinForm.classList.remove("active");
    createForm.classList.remove("active");
    forgotForm.classList.add("active");
    resetForm.classList.remove("active");
};

// Back to login
function backToLogin(){
    clearAllForms();
    showSignin();
}
window.backToLogin = backToLogin;

// ===== PASSWORD EYE TOGGLE =====
function togglePassword(id) {
    const input = document.getElementById(id);
    if (!input) return;
    input.type = (input.type === "password") ? "text" : "password";
}

// ===== OTP FUNCTIONS =====
function renderOtpBoxes(id){
    const container = document.getElementById(id);
    container.innerHTML = "";
    for(let i=0;i<6;i++){
        let box = document.createElement("input");
        box.className = "otp-input";
        box.maxLength = 1;
        box.oninput = e=>{
            if(e.target.value.length===1 && e.target.nextElementSibling){
                e.target.nextElementSibling.focus();
            }
        };
        container.appendChild(box);
    }
}

function getOtp(id){
    return [...document.querySelectorAll(`#${id} .otp-input`)]
        .map(x=>x.value.trim())
        .join("");
}

// ===== LOGIN =====
function handleLogin(){
    let username = loginUsername.value.trim();
    let password = loginPassword.value;

    if(!username || !password){
        alert("Enter username and password.");
        return;
    }

    fetch("backend.php",{
        method:"POST",
        body:new URLSearchParams({
            action:"login",
            username,
            password
        })
    })
    .then(res=>res.text())
    .then(msg=>{
        if(msg==="OK"){
            clearAllForms();
            location.href="home.php";
        } 
        else if(msg==="USERNAME_WRONG"){
            alert("Incorrect Username.");
        }
        else if(msg==="PASSWORD_WRONG"){
            alert("Incorrect Password.");
        }
        else alert(msg);
    });
}
window.handleLogin = handleLogin;

// ===== SEND REGISTER OTP =====
function sendRegisterOTP(){
    let username = regUsername.value.trim();
    let email = regEmail.value.trim();

    if(!username || !email){
        alert("Enter username and email.");
        return;
    }

    fetch("backend.php",{
        method:"POST",
        body:new URLSearchParams({
            action:"sendRegOTP",
            username,
            email
        })
    })
    .then(res=>res.text())
    .then(msg=>{
        if(msg==="USERNAME_EXISTS") alert("Username already exists.");
        else if(msg==="EMAIL_EXISTS") alert("Email already registered.");
        else if(msg==="OK"){
            renderOtpBoxes("regOtpContainer");
            alert("OTP sent to your Gmail.");
        }
        else alert(msg);
    });
}
window.sendRegisterOTP = sendRegisterOTP;

// ===== CREATE ACCOUNT =====
function handleRegister(){
    let username = regUsername.value.trim();
    let email = regEmail.value.trim();
    let pass = regPassword.value;
    let pass2 = regPassword2.value;
    let otp = getOtp("regOtpContainer");

    if(otp.length!==6){
        alert("Enter 6 digit OTP.");
        return;
    }

    if(pass!==pass2){
        alert("Passwords do not match.");
        return;
    }

    const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
    if(!regex.test(pass)){
        alert("Must include 1 capital letter, 1 number, 1 symbol, min 6 characters.");
        return;
    }

    fetch("backend.php",{
        method:"POST",
        body:new URLSearchParams({
            action:"register",
            username,
            email,
            password:pass,
            otp
        })
    })
    .then(res=>res.text())
    .then(msg=>{
        if(msg==="OK") {
            clearAllForms();
            location.href="home.php";
        }
        else alert(msg);
    });
}
window.handleRegister = handleRegister;

// ===== SEND FORGOT OTP =====
function sendForgotOTP(){
    let email = forgotEmail.value.trim();
    if(!email){
        alert("Enter Gmail.");
        return;
    }

    fetch("backend.php",{
        method:"POST",
        body:new URLSearchParams({
            action:"sendForgotOTP",
            email
        })
    })
    .then(res=>res.text())
    .then(msg=>{
        if(msg==="EMAIL_NOT_FOUND"){
            alert("Gmail not registered.");
        }
        else if(msg==="OK"){
            renderOtpBoxes("forgotOtpContainer");
            if(!document.getElementById("forgotVerifyBtn")){
                let btn=document.createElement("button");
                btn.id="forgotVerifyBtn";
                btn.textContent="Verify OTP";
                btn.className="primary-btn";
                btn.onclick=verifyForgotOTP;
                forgotForm.appendChild(btn);
            }
        }
        else alert(msg);
    });
}
window.sendForgotOTP = sendForgotOTP;

// ===== VERIFY FORGOT OTP =====
function verifyForgotOTP(){
    let otp = getOtp("forgotOtpContainer");

    fetch("backend.php",{
        method:"POST",
        body:new URLSearchParams({
            action:"verifyForgotOTP",
            otp
        })
    })
    .then(res=>res.text())
    .then(msg=>{
        if(msg==="OK"){
            forgotForm.classList.remove("active");
            resetForm.classList.add("active");
        } else alert("Invalid OTP.");
    });
}
window.verifyForgotOTP = verifyForgotOTP;

// ===== RESET PASSWORD =====
function handleReset(){
    const p1 = newPassword.value;
    const p2 = newPassword2.value;

    const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
    if(!regex.test(p1)){
        alert("Invalid password format.");
        return;
    }

    if(p1!==p2){
        alert("Passwords do not match.");
        return;
    }

    fetch("backend.php",{
        method:"POST",
        body:new URLSearchParams({
            action:"resetPassword",
            password:p1
        })
    })
    .then(res=>res.text())
    .then(msg=>{
        if(msg==="OK"){
            alert("Password updated.");
            clearAllForms();
            showSignin();
        } else alert(msg);
    });
}
window.handleReset = handleReset;
