<?php
// views/navbar.php
use App\Auth;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= baseUrl() ?>/index.php">
            📚 My Library
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= baseUrl() ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= baseUrl() ?>/ebooks.php">E-Books</a>
                </li>

                <?php if (Auth::check()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= baseUrl() ?>/history.php">My History</a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Admin Panel
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= baseUrl() ?>/admin/index.php">Manage Books</a></li>
                            <li><a class="dropdown-item" href="<?= baseUrl() ?>/admin/users.php">Manage Users</a></li>
                            <li><a class="dropdown-item" href="<?= baseUrl() ?>/admin/reports.php">Reports</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Dark Mode Toggle -->
                <li class="nav-item me-3">
                    <button id="themeToggle" class="btn btn-outline-light btn-sm" title="Toggle Dark Mode">
                        🌙
                    </button>
                </li>

                <?php if (Auth::check()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?= e(Auth::user()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            
                            <li><a class="dropdown-item" href="<?= baseUrl() ?>/profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= baseUrl() ?>/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= baseUrl() ?>/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= baseUrl() ?>/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
// Dark Mode Toggle with persistence
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('themeToggle');
    const html = document.documentElement;

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    toggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';

    toggle.addEventListener('click', () => {
        const current = html.getAttribute('data-bs-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        toggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
    });
});
</script>