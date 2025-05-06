import { Chart } from "@/components/ui/chart"
import * as bootstrap from "bootstrap"
import Datepicker from "vanillajs-datepicker"
import * as $ from "jquery"
import DataTable from "datatables.net-dt"
import "datatables.net-responsive-dt"
import "datatables.net-dt/css/jquery.dataTables.min.css"
import * as XLSX from "xlsx"
import html2canvas from "html2canvas"
import jsPDF from "jspdf"

/**
 * Enhanced JavaScript for Aplikasi Peminjaman Sarpras SMKN 1 Cimahi
 */

document.addEventListener("DOMContentLoaded", () => {
  // Toggle Sidebar with enhanced animation
  const sidebarToggle = document.querySelector(".sidebar-toggle")
  const sidebar = document.querySelector(".sidebar")
  const content = document.querySelector(".content")
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      // Toggle sidebar collapsed state
      sidebar.classList.toggle("sidebar-collapsed")
      content.classList.toggle("content-expanded")

      // Add rotation animation to toggle icon
      sidebarToggle.querySelector("i").classList.toggle("rotate-180")

      // Save state to localStorage
      const sidebarState = sidebar.classList.contains("sidebar-collapsed")
      localStorage.setItem("sidebarCollapsed", sidebarState)
    })

    // Check localStorage for sidebar state on page load
    const sidebarState = localStorage.getItem("sidebarCollapsed")
    if (sidebarState === "true") {
      sidebar.classList.add("sidebar-collapsed")
      content.classList.add("content-expanded")
      sidebarToggle.querySelector("i")?.classList.add("rotate-180")
    }
  }

  // Mobile menu toggle with enhanced animation
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      toggleMobileSidebar();
    })
  }

  // Enhanced navbar scroll effect
  const navbar = document.querySelector(".navbar")
  window.addEventListener("scroll", () => {
    if (window.scrollY > 10) {
      navbar.classList.add("scrolled")
    } else {
      navbar.classList.remove("scrolled")
    }
  })

  // Auto-dismiss alerts after 5 seconds with fade out animation
  const alerts = document.querySelectorAll(".alert-dismissible")
  alerts.forEach((alert) => {
    setTimeout(() => {
      const closeBtn = alert.querySelector(".btn-close")
      if (closeBtn) {
        alert.classList.add("fade-out")
        setTimeout(() => {
          closeBtn.click()
        }, 500)
      }
    }, 5000)
  })

  // Enhanced tooltips initialization
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) =>
      new bootstrap.Tooltip(tooltipTriggerEl, {
        animation: true,
        delay: { show: 100, hide: 100 },
      }),
  )

  // Enhanced popovers initialization
  const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
  const popoverList = [...popoverTriggerList].map(
    (popoverTriggerEl) =>
      new bootstrap.Popover(popoverTriggerEl, {
        animation: true,
        trigger: "hover focus",
      }),
  )

  // Enhanced datepickers initialization
  const datepickers = document.querySelectorAll(".datepicker")
  if (datepickers.length > 0) {
    datepickers.forEach((datepicker) => {
      new Datepicker(datepicker, {
        format: "dd/mm/yyyy",
        autohide: true,
        todayBtn: true,
        todayBtnMode: 1,
        todayHighlight: true,
        weekStart: 1,
        buttonClass: "btn",
        animation: true,
      })
    })
  }

  // Add active class to sidebar menu based on current page with enhanced animation
  const currentPage = window.location.pathname
  const sidebarLinks = document.querySelectorAll(".sidebar-link")

  sidebarLinks.forEach((link, index) => {
    const href = link.getAttribute("href")
    if (currentPage.indexOf(href) !== -1) {
      link.classList.add("active")

      // Add staggered animation delay
      link.style.animationDelay = `${index * 0.05}s`
      link.classList.add("slide-in-left")
    }
  })

  // Enhanced DataTables initialization
  const dataTables = document.querySelectorAll(".datatable")
  if (dataTables.length > 0) {
    dataTables.forEach((table) => {
      try {
        // Skip tables that have been marked as initialized (from inline scripts)
        if (table.classList.contains("dt-initialized")) {
          console.log("Table #" + table.id + " already initialized (marked), skipping")
          return
        }

        // Also check using the DataTable API
        if (typeof $.fn.dataTable !== "undefined" && $.fn.dataTable.isDataTable("#" + table.id)) {
          // Table is already initialized, skip it
          console.log("Table #" + table.id + " already initialized (detected), skipping")
          // Mark it as initialized
          table.classList.add("dt-initialized")
        } else {
          // Initialize the table with enhanced styling
          new DataTable(table, {
            responsive: true,
            language: {
              search: "Cari:",
              lengthMenu: "Tampilkan _MENU_ data",
              info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
              infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
              infoFiltered: "(disaring dari _MAX_ total data)",
              zeroRecords: "Tidak ada data yang cocok",
              emptyTable: "Tidak ada data tersedia",
              paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir",
              },
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            // Enhanced styling
            initComplete: function () {
              const api = this.api()
              $(".dataTables_filter input")
                .off(".DT")
                .on("input.DT", function () {
                  api.search(this.value).draw()
                })
                .addClass("form-control form-control-sm")
                .attr("placeholder", "Cari...")

              $(".dataTables_length select").addClass("form-select form-select-sm")
            },
          })
          // Mark it as initialized
          table.classList.add("dt-initialized")
        }
      } catch (error) {
        console.error("Error initializing DataTable:", error)
      }
    })
  }

  // Enhanced form validation with animations
  const forms = document.querySelectorAll(".needs-validation")
  Array.prototype.slice.call(forms).forEach((form) => {
    form.addEventListener(
      "submit",
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()

          // Add shake animation to invalid fields
          const invalidFields = form.querySelectorAll(":invalid")
          invalidFields.forEach((field) => {
            field.classList.add("shake-animation")
            setTimeout(() => {
              field.classList.remove("shake-animation")
            }, 600)
          })
        }

        form.classList.add("was-validated")
      },
      false,
    )
  })

  // Enhanced image preview on file input change
  const imageInputs = document.querySelectorAll(".image-input")
  if (imageInputs.length > 0) {
    imageInputs.forEach((input) => {
      input.addEventListener("change", function (e) {
        const previewId = this.dataset.preview
        const preview = document.getElementById(previewId)

        if (preview && this.files && this.files[0]) {
          const reader = new FileReader()

          reader.onload = (e) => {
            preview.src = e.target.result
            // Add fade-in animation
            preview.classList.add("fade-in")
          }

          reader.readAsDataURL(this.files[0])
        }
      })
    })
  }

  // Enhanced confirmation dialogs
  const confirmButtons = document.querySelectorAll("[data-confirm]")
  confirmButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const message = this.dataset.confirm || "Apakah Anda yakin?"
      const title = this.dataset.confirmTitle || "Konfirmasi"
      const confirmText = this.dataset.confirmText || "Ya"
      const cancelText = this.dataset.cancelText || "Batal"

      e.preventDefault()

      // Create custom modal instead of using browser confirm
      const modal = document.createElement("div")
      modal.className = "modal fade"
      modal.id = "confirmModal"
      modal.setAttribute("tabindex", "-1")
      modal.setAttribute("aria-labelledby", "confirmModalLabel")
      modal.setAttribute("aria-hidden", "true")

      modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="confirmModalLabel">${title}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>${message}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
              <button type="button" class="btn btn-primary" id="confirmBtn">${confirmText}</button>
            </div>
          </div>
        </div>
      `

      document.body.appendChild(modal)

      const confirmModal = new bootstrap.Modal(modal)
      confirmModal.show()

      document.getElementById("confirmBtn").addEventListener("click", () => {
        confirmModal.hide()
        // Continue with the original action
        if (this.tagName === "A") {
          window.location.href = this.getAttribute("href")
        } else if (this.form) {
          this.form.submit()
        }
      })

      modal.addEventListener("hidden.bs.modal", () => {
        modal.remove()
      })
    })
  })

  // Enhanced charts initialization with animations
  initCharts()

  // Add animation classes with staggered delay
  addAnimations()
})

// Function to initialize charts with enhanced styling
function initCharts() {
  // Dashboard stats chart
  const statsChart = document.getElementById("statsChart")
  if (statsChart) {
    const ctx = statsChart.getContext("2d")
    new Chart(ctx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
        datasets: [
          {
            label: "Peminjaman",
            data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 5],
            backgroundColor: "rgba(78, 56, 43, 0.2)",
            borderColor: "rgba(78, 56, 43, 1)",
            borderWidth: 2,
            tension: 0.4,
            pointBackgroundColor: "rgba(78, 56, 43, 1)",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
            },
          },
          x: {
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
            },
          },
        },
        plugins: {
          legend: {
            labels: {
              font: {
                family: "'Poppins', sans-serif",
                size: 12,
              },
            },
          },
          tooltip: {
            backgroundColor: "rgba(78, 56, 43, 0.8)",
            titleFont: {
              family: "'Poppins', sans-serif",
              size: 14,
            },
            bodyFont: {
              family: "'Poppins', sans-serif",
              size: 13,
            },
            padding: 10,
            cornerRadius: 6,
            caretSize: 6,
          },
        },
        animation: {
          duration: 2000,
          easing: "easeOutQuart",
        },
      },
    })
  }

  // Pie chart for item categories with enhanced styling
  const categoriesChart = document.getElementById("categoriesChart")
  if (categoriesChart) {
    const ctx = categoriesChart.getContext("2d")
    new Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Elektronik", "Furniture", "Alat Olahraga", "Alat Musik", "Lainnya"],
        datasets: [
          {
            data: [12, 19, 8, 5, 2],
            backgroundColor: [
              "rgba(147, 122, 102, 0.8)",
              "rgba(78, 56, 43, 0.8)",
              "rgba(84, 40, 39, 0.8)",
              "rgba(58, 16, 28, 0.8)",
              "rgba(116, 112, 113, 0.8)",
            ],
            borderColor: [
              "rgba(147, 122, 102, 1)",
              "rgba(78, 56, 43, 1)",
              "rgba(84, 40, 39, 1)",
              "rgba(58, 16, 28, 1)",
              "rgba(116, 112, 113, 1)",
            ],
            borderWidth: 2,
            hoverOffset: 10,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              font: {
                family: "'Poppins', sans-serif",
                size: 12,
              },
            },
          },
          tooltip: {
            backgroundColor: "rgba(78, 56, 43, 0.8)",
            titleFont: {
              family: "'Poppins', sans-serif",
              size: 14,
            },
            bodyFont: {
              family: "'Poppins', sans-serif",
              size: 13,
            },
            padding: 10,
            cornerRadius: 6,
            caretSize: 6,
          },
        },
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: 2000,
          easing: "easeOutQuart",
        },
      },
    })
  }

  // Bar chart for item status with enhanced styling
  const statusChart = document.getElementById("statusChart")
  if (statusChart) {
    const ctx = statusChart.getContext("2d")
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Tersedia", "Dipinjam", "Rusak", "Perbaikan"],
        datasets: [
          {
            label: "Status Sarpras",
            data: [42, 19, 8, 5],
            backgroundColor: [
              "rgba(40, 167, 69, 0.8)",
              "rgba(255, 193, 7, 0.8)",
              "rgba(220, 53, 69, 0.8)",
              "rgba(23, 162, 184, 0.8)",
            ],
            borderColor: [
              "rgba(40, 167, 69, 1)",
              "rgba(255, 193, 7, 1)",
              "rgba(220, 53, 69, 1)",
              "rgba(23, 162, 184, 1)",
            ],
            borderWidth: 2,
            borderRadius: 6,
            hoverBorderWidth: 3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: "rgba(78, 56, 43, 0.8)",
            titleFont: {
              family: "'Poppins', sans-serif",
              size: 14,
            },
            bodyFont: {
              family: "'Poppins', sans-serif",
              size: 13,
            },
            padding: 10,
            cornerRadius: 6,
            caretSize: 6,
          },
        },
        animation: {
          duration: 2000,
          easing: "easeOutQuart",
          delay: (context) => context.dataIndex * 100,
        },
      },
    })
  }
}

// Function to add animation classes to elements with staggered delay
function addAnimations() {
  // Add fade-in animation to cards with staggered delay
  const cards = document.querySelectorAll(".card")
  cards.forEach((card, index) => {
    // Add animation with delay based on index
    setTimeout(() => {
      card.classList.add("fade-in")
      card.style.animationDelay = `${index * 0.1}s`
    }, 100)
  })

  // Add slide-in-left animation to sidebar items with staggered delay
  const sidebarItems = document.querySelectorAll(".sidebar-menu-item")
  sidebarItems.forEach((item, index) => {
    setTimeout(() => {
      item.classList.add("slide-in-left")
      item.style.animationDelay = `${index * 0.05}s`
    }, 50)
  })

  // Add slide-in-up animation to tables
  const tables = document.querySelectorAll(".table-container")
  tables.forEach((table, index) => {
    table.classList.add("slide-in-up")
    table.style.animationDelay = `${index * 0.1}s`
  })

  // Add slide-in-right animation to stat cards
  const statCards = document.querySelectorAll(".stat-card")
  statCards.forEach((card, index) => {
    card.classList.add("slide-in-right")
    card.style.animationDelay = `${index * 0.1}s`
  })
}

// Function to show/hide password in login form with enhanced animation
function togglePassword(buttonId, inputId) {
  const button = document.getElementById(buttonId)
  const input = document.getElementById(inputId)

  if (button && input) {
    button.addEventListener("click", function () {
      const icon = this.querySelector("i")

      if (input.type === "password") {
        input.type = "text"
        icon.classList.remove("bi-eye")
        icon.classList.add("bi-eye-slash")

        // Add subtle animation
        input.classList.add("pulse")
        setTimeout(() => {
          input.classList.remove("pulse")
        }, 500)
      } else {
        input.type = "password"
        icon.classList.remove("bi-eye-slash")
        icon.classList.add("bi-eye")

        // Add subtle animation
        input.classList.add("pulse")
        setTimeout(() => {
          input.classList.remove("pulse")
        }, 500)
      }
    })
  }
}

// Enhanced function to handle search filter on tables
function filterTable() {
  const input = document.getElementById("searchInput")
  const filter = input.value.toUpperCase()
  const table = document.getElementById("dataTable")
  const tr = table.getElementsByTagName("tr")

  for (let i = 0; i < tr.length; i++) {
    const td = tr[i].getElementsByTagName("td")
    let found = false

    for (let j = 0; j < td.length; j++) {
      if (td[j]) {
        const txtValue = td[j].textContent || td[j].innerText
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          found = true
          break
        }
      }
    }

    if (found) {
      tr[i].style.display = ""
      // Add subtle highlight animation
      tr[i].classList.add("highlight-row")
      setTimeout(() => {
        tr[i].classList.remove("highlight-row")
      }, 500)
    } else {
      if (i > 0) {
        // Skip header row
        tr[i].style.display = "none"
      }
    }
  }
}

// Enhanced function to print element with custom styling
function printElement(elementId) {
  const element = document.getElementById(elementId)
  const originalContent = document.body.innerHTML

  // Create print-specific styles
  const style = document.createElement("style")
  style.innerHTML = `
    @media print {
      body { font-family: 'Poppins', sans-serif; color: #000; }
      .table { border-collapse: collapse; width: 100%; }
      .table th { background-color: #f2f2f2 !important; color: #000 !important; }
      .table th, .table td { border: 1px solid #ddd; padding: 8px; }
      .badge { border: 1px solid #000; padding: 2px 5px; border-radius: 3px; }
      .badge-success { background-color: #dff0d8 !important; color: #3c763d !important; }
      .badge-warning { background-color: #fcf8e3 !important; color: #8a6d3b !important; }
      .badge-danger { background-color: #f2dede !important; color: #a94442 !important; }
      .badge-info { background-color: #d9edf7 !important; color: #31708f !important; }
      .badge-primary { background-color: #d9edf7 !important; color: #31708f !important; }
      .badge-secondary { background-color: #e9ecef !important; color: #495057 !important; }
      .header-print { text-align: center; margin-bottom: 20px; }
      .footer-print { text-align: center; font-size: 12px; margin-top: 20px; }
    }
  `

  // Create header and footer for print
  const header = document.createElement("div")
  header.className = "header-print"
  header.innerHTML = `
    <h2>SMKN 1 Cimahi - Sistem Peminjaman Sarpras</h2>
    <p>Tanggal Cetak: ${new Date().toLocaleDateString("id-ID", { weekday: "long", year: "numeric", month: "long", day: "numeric" })}</p>
  `

  const footer = document.createElement("div")
  footer.className = "footer-print"
  footer.innerHTML = `
    <p>Dicetak oleh: ${document.querySelector(".user-name")?.textContent || "Admin"}</p>
    <p>© ${new Date().getFullYear()} SMKN 1 Cimahi - Sistem Peminjaman Sarpras</p>
  `

  // Combine everything
  const printContent = header.outerHTML + element.innerHTML + footer.outerHTML

  document.body.innerHTML = printContent
  document.head.appendChild(style)
  window.print()
  document.body.innerHTML = originalContent

  // Reinitialize scripts after printing
  document.addEventListener("DOMContentLoaded", () => {
    // Your initialization code here
    initCharts()
    addAnimations()
  })
}

// Enhanced function to export table to Excel with styling
function exportTableToExcel(tableID, filename = "") {
  const table = document.getElementById(tableID)

  // Add loading indicator
  const loadingOverlay = document.createElement("div")
  loadingOverlay.className = "loading-overlay"
  loadingOverlay.innerHTML = `
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Memproses ekspor data...</p>
  `
  document.body.appendChild(loadingOverlay)

  setTimeout(() => {
    try {
      const wb = XLSX.utils.table_to_book(table, {
        sheet: "Data",
        dateNF: "dd/mm/yyyy",
        cellStyles: true,
      })
      XLSX.writeFile(wb, filename + ".xlsx")

      // Show success message
      const successToast = document.createElement("div")
      successToast.className = "toast-notification success"
      successToast.innerHTML = `
        <div class="toast-icon"><i class="bi bi-check-circle"></i></div>
        <div class="toast-content">
          <div class="toast-title">Berhasil</div>
          <div class="toast-message">File Excel berhasil diunduh</div>
        </div>
      `
      document.body.appendChild(successToast)

      setTimeout(() => {
        successToast.classList.add("show")
      }, 100)

      setTimeout(() => {
        successToast.classList.remove("show")
        setTimeout(() => {
          successToast.remove()
        }, 300)
      }, 3000)
    } catch (error) {
      console.error("Error exporting to Excel:", error)

      // Show error message
      const errorToast = document.createElement("div")
      errorToast.className = "toast-notification error"
      errorToast.innerHTML = `
        <div class="toast-icon"><i class="bi bi-x-circle"></i></div>
        <div class="toast-content">
          <div class="toast-title">Gagal</div>
          <div class="toast-message">Terjadi kesalahan saat mengekspor data</div>
        </div>
      `
      document.body.appendChild(errorToast)

      setTimeout(() => {
        errorToast.classList.add("show")
      }, 100)

      setTimeout(() => {
        errorToast.classList.remove("show")
        setTimeout(() => {
          errorToast.remove()
        }, 300)
      }, 3000)
    } finally {
      // Remove loading overlay
      loadingOverlay.remove()
    }
  }, 500)
}

// Enhanced function to export table to PDF with better styling
function exportTableToPDF(tableID, filename = "") {
  const table = document.getElementById(tableID)

  // Add loading indicator
  const loadingOverlay = document.createElement("div")
  loadingOverlay.className = "loading-overlay"
  loadingOverlay.innerHTML = `
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Memproses ekspor data...</p>
  `
  document.body.appendChild(loadingOverlay)

  setTimeout(() => {
    try {
      // Create custom styles for PDF
      const style = `
        <style>
          table { border-collapse: collapse; width: 100%; }
          th { background-color: #4e382b; color: white; font-weight: bold; }
          th, td { border: 1px solid #ddd; text-align: left; padding: 8px; }
          tr:nth-child(even) { background-color: #f2f2f2; }
          .header { text-align: center; margin-bottom: 20px; }
          .footer { text-align: center; font-size: 12px; margin-top: 20px; }
        </style>
      `

      // Create header and footer
      const header = `
        <div class="header">
          <h2>SMKN 1 Cimahi - Sistem Peminjaman Sarpras</h2>
          <p>Tanggal Cetak: ${new Date().toLocaleDateString("id-ID", { weekday: "long", year: "numeric", month: "long", day: "numeric" })}</p>
        </div>
      `

      const footer = `
        <div class="footer">
          <p>Dicetak oleh: ${document.querySelector(".user-name")?.textContent || "Admin"}</p>
          <p>© ${new Date().getFullYear()} SMKN 1 Cimahi - Sistem Peminjaman Sarpras</p>
        </div>
      `

      // Combine everything
      const content = `<html><head>${style}</head><body>${header}${table.outerHTML}${footer}</body></html>`

      html2canvas(table, {
        scale: 2,
        useCORS: true,
        logging: false,
      })
        .then((canvas) => {
          const imgData = canvas.toDataURL("image/png")
          const pdf = new jsPDF("p", "mm", "a4")

          // Add header
          pdf.setFontSize(18)
          pdf.setTextColor(78, 56, 43)
          pdf.text("SMKN 1 Cimahi - Sistem Peminjaman Sarpras", pdf.internal.pageSize.getWidth() / 2, 15, {
            align: "center",
          })

          pdf.setFontSize(12)
          pdf.setTextColor(0, 0, 0)
          pdf.text(
            `Tanggal Cetak: ${new Date().toLocaleDateString("id-ID")}`,
            pdf.internal.pageSize.getWidth() / 2,
            22,
            { align: "center" },
          )

          // Add table image
          const imgProps = pdf.getImageProperties(imgData)
          const pdfWidth = pdf.internal.pageSize.getWidth() - 20
          const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width
          pdf.addImage(imgData, "PNG", 10, 30, pdfWidth, pdfHeight)

          // Add footer
          const footerY = 30 + pdfHeight + 10
          pdf.setFontSize(10)
          pdf.setTextColor(100, 100, 100)
          pdf.text(
            `Dicetak oleh: ${document.querySelector(".user-name")?.textContent || "Admin"}`,
            pdf.internal.pageSize.getWidth() / 2,
            footerY,
            { align: "center" },
          )
          pdf.text(
            `© ${new Date().getFullYear()} SMKN 1 Cimahi - Sistem Peminjaman Sarpras`,
            pdf.internal.pageSize.getWidth() / 2,
            footerY + 5,
            { align: "center" },
          )

          // Save PDF
          pdf.save(filename + ".pdf")

          // Show success message
          const successToast = document.createElement("div")
          successToast.className = "toast-notification success"
          successToast.innerHTML = `
          <div class="toast-icon"><i class="bi bi-check-circle"></i></div>
          <div class="toast-content">
            <div class="toast-title">Berhasil</div>
            <div class="toast-message">File PDF berhasil diunduh</div>
          </div>
        `
          document.body.appendChild(successToast)

          setTimeout(() => {
            successToast.classList.add("show")
          }, 100)

          setTimeout(() => {
            successToast.classList.remove("show")
            setTimeout(() => {
              successToast.remove()
            }, 300)
          }, 3000)
        })
        .catch((error) => {
          console.error("Error exporting to PDF:", error)

          // Show error message
          const errorToast = document.createElement("div")
          errorToast.className = "toast-notification error"
          errorToast.innerHTML = `
          <div class="toast-icon"><i class="bi bi-x-circle"></i></div>
          <div class="toast-content">
            <div class="toast-title">Gagal</div>
            <div class="toast-message">Terjadi kesalahan saat mengekspor data</div>
          </div>
        `
          document.body.appendChild(errorToast)

          setTimeout(() => {
            errorToast.classList.add("show")
          }, 100)

          setTimeout(() => {
            errorToast.classList.remove("show")
            setTimeout(() => {
              errorToast.remove()
            }, 300)
          }, 3000)
        })
        .finally(() => {
          // Remove loading overlay
          loadingOverlay.remove()
        })
    } catch (error) {
      console.error("Error in PDF export process:", error)
      loadingOverlay.remove()
    }
  }, 500)
}

// Add custom toast notification system
class ToastNotification {
  constructor(type, title, message, duration = 3000) {
    this.type = type // 'success', 'error', 'warning', 'info'
    this.title = title
    this.message = message
    this.duration = duration
    this.element = null

    this.create()
    this.show()
  }

  create() {
    // Create toast element
    this.element = document.createElement("div")
    this.element.className = `toast-notification ${this.type}`

    // Set icon based on type
    let icon = "info-circle"
    if (this.type === "success") icon = "check-circle"
    if (this.type === "error") icon = "x-circle"
    if (this.type === "warning") icon = "exclamation-triangle"

    this.element.innerHTML = `
      <div class="toast-icon"><i class="bi bi-${icon}"></i></div>
      <div class="toast-content">
        <div class="toast-title">${this.title}</div>
        <div class="toast-message">${this.message}</div>
      </div>
      <button class="toast-close"><i class="bi bi-x"></i></button>
    `

    // Add close button functionality
    const closeBtn = this.element.querySelector(".toast-close")
    closeBtn.addEventListener("click", () => this.hide())

    // Add to DOM
    document.body.appendChild(this.element)
  }

  show() {
    // Trigger animation after a small delay
    setTimeout(() => {
      this.element.classList.add("show")
    }, 10)

    // Auto hide after duration
    if (this.duration > 0) {
      this.hideTimeout = setTimeout(() => this.hide(), this.duration)
    }
  }

  hide() {
    clearTimeout(this.hideTimeout)
    this.element.classList.remove("show")

    // Remove from DOM after animation
    setTimeout(() => {
      if (this.element && this.element.parentNode) {
        this.element.parentNode.removeChild(this.element)
      }
    }, 300)
  }

  static success(title, message, duration) {
    return new ToastNotification("success", title, message, duration)
  }

  static error(title, message, duration) {
    return new ToastNotification("error", title, message, duration)
  }

  static warning(title, message, duration) {
    return new ToastNotification("warning", title, message, duration)
  }

  static info(title, message, duration) {
    return new ToastNotification("info", title, message, duration)
  }
}

// Add CSS for toast notifications
document.addEventListener("DOMContentLoaded", () => {
  const style = document.createElement("style")
  style.textContent = `
    .toast-notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      display: flex;
      align-items: center;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      padding: 12px 16px;
      min-width: 300px;
      max-width: 450px;
      z-index: 9999;
      transform: translateY(100px);
      opacity: 0;
      transition: transform 0.3s ease, opacity 0.3s ease;
    }
    
    .toast-notification.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .toast-notification.success {
      border-left: 4px solid #28a745;
    }
    
    .toast-notification.error {
      border-left: 4px solid #dc3545;
    }
    
    .toast-notification.warning {
      border-left: 4px solid #ffc107;
    }
    
    .toast-notification.info {
      border-left: 4px solid #17a2b8;
    }
    
    .toast-icon {
      margin-right: 12px;
      font-size: 24px;
    }
    
    .toast-notification.success .toast-icon {
      color: #28a745;
    }
    
    .toast-notification.error .toast-icon {
      color: #dc3545;
    }
    
    .toast-notification.warning .toast-icon {
      color: #ffc107;
    }
    
    .toast-notification.info .toast-icon {
      color: #17a2b8;
    }
    
    .toast-content {
      flex: 1;
    }
    
    .toast-title {
      font-weight: 600;
      margin-bottom: 4px;
    }
    
    .toast-message {
      font-size: 14px;
      color: #666;
    }
    
    .toast-close {
      background: none;
      border: none;
      color: #999;
      cursor: pointer;
      font-size: 16px;
      padding: 4px;
      margin-left: 8px;
      transition: color 0.2s;
    }
    
    .toast-close:hover {
      color: #333;
    }
    
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      color: white;
    }
    
    @keyframes shake-animation {
      0% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      50% { transform: translateX(5px); }
      75% { transform: translateX(-5px); }
      100% { transform: translateX(0); }
    }
    
    .shake-animation {
      animation: shake-animation 0.6s ease;
    }
    
    @keyframes highlight-row {
      0% { background-color: rgba(147, 122, 102, 0.3); }
      100% { background-color: transparent; }
    }
    
    .highlight-row {
      animation: highlight-row 1s ease;
    }
    
    .rotate-180 {
      transform: rotate(180deg);
    }
    
    .sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 998;
      animation: fadeIn 0.3s ease;
    }
  `

  document.head.appendChild(style)
})

// Function to toggle mobile sidebar
function toggleMobileSidebar() {
  sidebar.classList.toggle("show")

  // Add overlay when sidebar is shown on mobile
  if (sidebar.classList.contains("show")) {
    const overlay = document.createElement("div")
    overlay.className = "sidebar-overlay"
    overlay.addEventListener("click", () => {
      toggleMobileSidebar();
    })
    document.body.appendChild(overlay)
  } else {
    removeSidebarOverlay();
  }
}

// Helper function to remove sidebar overlay
function removeSidebarOverlay() {
  const overlay = document.querySelector(".sidebar-overlay")
  if (overlay) {
    overlay.classList.add("fade-out")
    setTimeout(() => {
      overlay.remove()
    }, 300)
  }
}

// Close sidebar on ESC key press in mobile view
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && sidebar.classList.contains('show')) {
    toggleMobileSidebar();
  }
});

// Close sidebar when clicking outside in mobile view
document.addEventListener('click', (e) => {
  const windowWidth = window.innerWidth;
  if (windowWidth <= 576 && 
      sidebar.classList.contains('show') && 
      !sidebar.contains(e.target) && 
      !mobileMenuToggle.contains(e.target)) {
    toggleMobileSidebar();
  }
});
