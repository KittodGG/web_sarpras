/**
 * Sidebar functionality for Aplikasi Peminjaman Sarpras SMKN 1 Cimahi
 */

document.addEventListener("DOMContentLoaded", () => {
  // Toggle Sidebar with enhanced animation
  const sidebarToggle = document.querySelector(".sidebar-toggle");
  const sidebar = document.querySelector(".sidebar");
  const content = document.querySelector(".content");
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");

  if (sidebarToggle) {
    console.log("Sidebar toggle button found"); // Debug
    
    sidebarToggle.addEventListener("click", () => {
      console.log("Sidebar toggle clicked"); // Debug
      
      // Toggle sidebar collapsed state
      sidebar.classList.toggle("sidebar-collapsed");
      
      if (content) {
        content.classList.toggle("content-expanded");
      }

      // Add rotation animation to toggle icon
      const icon = sidebarToggle.querySelector("i");
      if (icon) {
        icon.classList.toggle("rotate-180");
      }

      // Save state to localStorage
      const sidebarState = sidebar.classList.contains("sidebar-collapsed");
      localStorage.setItem("sidebarCollapsed", sidebarState);
    });

    // Check localStorage for sidebar state on page load
    const sidebarState = localStorage.getItem("sidebarCollapsed");
    if (sidebarState === "true") {
      sidebar.classList.add("sidebar-collapsed");
      if (content) {
        content.classList.add("content-expanded");
      }
      const icon = sidebarToggle.querySelector("i");
      if (icon) {
        icon.classList.add("rotate-180");
      }
    }
  } else {
    console.log("Sidebar toggle button not found"); // Debug
  }

  // Mobile menu toggle functionality
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      toggleMobileSidebar();
    });
  }
});

// Function to toggle mobile sidebar
function toggleMobileSidebar() {
  const sidebar = document.querySelector(".sidebar");
  
  if (sidebar) {
    sidebar.classList.toggle("show");

    // Add overlay when sidebar is shown on mobile
    if (sidebar.classList.contains("show")) {
      const overlay = document.createElement("div");
      overlay.className = "sidebar-overlay";
      overlay.addEventListener("click", () => {
        toggleMobileSidebar();
      });
      document.body.appendChild(overlay);
    } else {
      removeSidebarOverlay();
    }
  }
}

// Helper function to remove sidebar overlay
function removeSidebarOverlay() {
  const overlay = document.querySelector(".sidebar-overlay");
  if (overlay) {
    overlay.classList.add("fade-out");
    setTimeout(() => {
      overlay.remove();
    }, 300);
  }
} 