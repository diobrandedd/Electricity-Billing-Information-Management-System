            </main>
        </div>
    </div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar overlay control for mobile
document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !overlay) return;

    function openOverlay() {
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    function closeOverlay() {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    sidebar.addEventListener('show.bs.collapse', openOverlay);
    sidebar.addEventListener('hide.bs.collapse', closeOverlay);
    sidebar.addEventListener('shown.bs.collapse', function(){ sidebar.classList.add('show'); openOverlay(); });
    sidebar.addEventListener('hidden.bs.collapse', function(){ sidebar.classList.remove('show'); closeOverlay(); });

    overlay.addEventListener('click', function() {
        var collapse = bootstrap.Collapse.getOrCreateInstance(sidebar);
        collapse.hide();
    });
});
</script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Persist sidebar accordion state and rotate carets
        (function() {
            const storageKey = 'sidebarActiveGroup';
            const activeGroup = localStorage.getItem(storageKey);

            // Restore active group state
            if (activeGroup) {
                const collapse = document.getElementById('collapse-' + activeGroup);
                const toggle = document.querySelector('.sidebar-group-toggle[data-group-id="' + activeGroup + '"]');
                if (collapse && toggle && !collapse.classList.contains('show')) {
                    collapse.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                    const caret = toggle.querySelector('.caret-icon');
                    if (caret) caret.style.transform = 'rotate(90deg)';
                }
            }

            // Listen for accordion events
            document.querySelectorAll('.sidebar-group-toggle').forEach(function(toggle) {
                const groupId = toggle.getAttribute('data-group-id');
                const targetId = 'collapse-' + groupId;
                const target = document.getElementById(targetId);
                const caret = toggle.querySelector('.caret-icon');
                if (!target) return;

                target.addEventListener('shown.bs.collapse', function() {
                    // Store the active group
                    localStorage.setItem(storageKey, groupId);
                    // Rotate caret for this group
                    if (caret) caret.style.transform = 'rotate(90deg)';
                    // Reset all other carets
                    document.querySelectorAll('.sidebar-group-toggle .caret-icon').forEach(function(otherCaret) {
                        if (otherCaret !== caret) {
                            otherCaret.style.transform = 'rotate(0deg)';
                        }
                    });
                });
                target.addEventListener('hidden.bs.collapse', function() {
                    // Clear storage when group is closed
                    if (localStorage.getItem(storageKey) === groupId) {
                        localStorage.removeItem(storageKey);
                    }
                    if (caret) caret.style.transform = 'rotate(0deg)';
                });
            });
        })();

        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Confirm delete actions
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }
        
        // Format currency input
        function formatCurrencyInput(input) {
            let value = input.value.replace(/[^\d.]/g, '');
            if (value) {
                input.value = parseFloat(value).toFixed(2);
            }
        }
        
        // Print function
        function printPage() {
            window.print();
        }
        
        // Export to PDF function (placeholder)
        function exportToPDF() {
            alert('PDF export functionality will be implemented with a PDF library like TCPDF or FPDF');
        }
        
        // Export to Excel function (placeholder)
        function exportToExcel() {
            alert('Excel export functionality will be implemented with a library like PhpSpreadsheet');
        }
    </script>
    
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>
