<!-- Sidebar -->
<aside class="sidebar">
    <ul class="sidebar-menu">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="sidebar-item">
            <a href="admin_dashboard.php" class="sidebar-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="sidebar-item">
            <a href="admin_manage_professors.php" class="sidebar-link <?php echo ($current_page == 'admin_manage_professors.php') ? 'active' : ''; ?>"><i class="fas fa-user-tie"></i> Manage Professors</a>
        </li>
        <li class="sidebar-item">
            <a href="admin_manage_students.php" class="sidebar-link <?php echo ($current_page == 'admin_manage_students.php') ? 'active' : ''; ?>"><i class="fas fa-user-graduate"></i> Manage Students</a>
        </li>
        <li class="sidebar-item">
            <a href="admin_manage_schedule.php" class="sidebar-link <?php echo ($current_page == 'admin_manage_schedule.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Manage Schedule</a>
        </li>
    </ul>
</aside>

<script>
// Hamburger menu toggle
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
    // Optionally add overlay for mobile
    if (window.innerWidth <= 900) {
        document.body.classList.toggle('sidebar-open');
    }
});

// Optional: Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (window.innerWidth <= 900 && sidebar.classList.contains('show')) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    }
});


</script>
