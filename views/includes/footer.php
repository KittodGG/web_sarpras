<?php if (isset($_SESSION['user_id']) && !isset($hide_sidebar)): ?>
            </div> <!-- End content-wrapper -->
        </div> <!-- End content -->
    </div> <!-- End d-flex -->
<?php endif; ?>

<!-- Footer -->
<footer class="footer bg-danger">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0"><?php echo $app_footer; ?></p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/responsive.bootstrap5.min.js"></script>

<!-- Datepicker JS -->
<script src="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.2.0/dist/js/datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.2.0/dist/js/locales/id.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Excel Export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

<!-- PDF Export -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
    // Define jsPDF in the window scope
    window.jsPDF = window.jspdf.jsPDF;
</script>

<!-- Custom JS -->
<script src="<?php echo ROOT_URL; ?>/assets/js/sidebar.js"></script>
<script src="<?php echo ROOT_URL; ?>/assets/js/main.js"></script>
</body>
</html>
