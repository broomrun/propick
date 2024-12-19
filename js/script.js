document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("authModal");
    const openModal = document.getElementById("openAuthModal");
    const closeModal = document.getElementById("closeModal");
    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");
    const switchToSignup = document.getElementById("switchToSignup");
    const switchToLogin = document.getElementById("switchToLogin");

    // Open the modal
    openModal.onclick = () => modal.style.display = "flex";

    // Close the modal
    closeModal.onclick = () => modal.style.display = "none";

    // Close modal if clicked outside of it
    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = "none";
    };

    // Switch to the Sign-Up form
    switchToSignup.onclick = (e) => {
        e.preventDefault();
        loginForm.style.display = "none";
        signupForm.style.display = "block";
    };

    // Switch to the Login form
    switchToLogin.onclick = (e) => {
        e.preventDefault();
        signupForm.style.display = "none";
        loginForm.style.display = "block";
    };

    // Toggle visibility of the password fields
    const togglePasswordVisibility = (toggleId, inputId) => {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);

        toggle.addEventListener("click", () => {
            const type = input.type === "password" ? "text" : "password";
            input.type = type;
            toggle.textContent = type === "password" ? "🙈" : "👁️";
        });
    };

    // Apply password visibility toggling for each input
    togglePasswordVisibility("togglePassword", "password");
    togglePasswordVisibility("toggleConfirmPassword", "confirmPassword");
    togglePasswordVisibility("toggleLoginPassword", "loginPassword");

    // Form login validation and submission
    const formLogin = document.getElementById("formLogin");
    if (formLogin) {
        formLogin.onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            fetch("index2.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.text())
            .then((message) => {
                alert(message); // Alert the response message after submission
                if (message.includes("Login berhasil")) {
                    location.reload(); // Reload page to reflect login
                }
            });
        };
    }

    // Form signup validation and submission
    const formSignup = document.getElementById("formSignup");
    if (formSignup) {
        formSignup.onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            const umur = parseInt(formData.get("umur"), 10);
            if (umur < 15 || umur > 21) {
                alert("Umur tidak sesuai dengan usia SMA/SMK!");
                return;
            }

            fetch("index.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.text())
            .then((message) => {
                alert(message); // Alert the response message after submission
                if (message.includes("Pendaftaran berhasil")) {
                    location.reload(); // Reload page to reflect sign-up success
                }
            });
        };
    }
});
