document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("authModal");
    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");
    const switchToSignup = document.getElementById("switchToSignup");
    const switchToLogin = document.getElementById("switchToLogin");

    // Buka modal
    openModal.onclick = () => modal.style.display = "flex";

    // Tutup modal
    closeModal.onclick = () => modal.style.display = "none";

    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = "none";
    };

    // Pindah ke form signup
    switchToSignup.onclick = (e) => {
        e.preventDefault();
        loginForm.style.display = "none";
        signupForm.style.display = "block";
    };

    // Pindah ke form login
    switchToLogin.onclick = (e) => {
        e.preventDefault();
        signupForm.style.display = "none";
        loginForm.style.display = "block";
    };

    // Toggle visibility password
    const togglePasswordVisibility = (toggleId, inputId) => {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);

        toggle.addEventListener("click", () => {
            const type = input.type === "password" ? "text" : "password";
            input.type = type;
            toggle.textContent = type === "password" ? "🙈" : "👁️";
        });
    };

    togglePasswordVisibility("togglePassword", "password");
    togglePasswordVisibility("toggleConfirmPassword", "confirmPassword");
    togglePasswordVisibility("toggleLoginPassword", "loginPassword");

    // Form login validation
    document.getElementById("formLogin").onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        fetch("index2.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(alert);
    };

    // Form signup validation
    document.getElementById("formSignup").onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        const umur = parseInt(formData.get("umur"), 10);
        if (umur < 15 || umur > 21) {
            alert("Umur tidak sesuai dengan usia SMA/SMK!");
            return;
        }

        fetch("index2.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(alert);
    };
});
