// Custom Toast Notification for Filament
document.addEventListener("DOMContentLoaded", function () {
    // Listen for Livewire events
    window.addEventListener("show-toast", function (event) {
        const detail = event.detail[0] || event.detail;
        showFilamentToast(detail.message, detail.type || "success");
    });
});

function showFilamentToast(message, type = "success") {
    let toast = document.getElementById("filament-toast");

    if (!toast) {
        toast = document.createElement("div");
        toast.id = "filament-toast";
        toast.className = "filament-toast";
        document.body.appendChild(toast);
    }

    // Reset classes
    toast.classList.remove("success", "error", "warning", "info", "show");

    // Add type class
    toast.classList.add(type);

    // Set message
    toast.textContent = message;

    // Show toast
    setTimeout(() => {
        toast.classList.add("show");
    }, 10);

    // Hide after 5 seconds
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => {
        toast.classList.remove("show");
    }, 5000);
}

// Export untuk digunakan dari console (debugging)
window.showFilamentToast = showFilamentToast;
