<?php
// views/footer.php
?>
    </div> <!-- End container -->

    <!-- Bootstrap JS Bundle -->
    <script src="<?= baseUrl() ?>/public/js/bootstrap.bundle.min.js"></script>

    <footer class="mt-5 py-4 bg-dark text-white-50">
        <div class="container text-center">
            <p class="mb-1">
                &copy; <?= date('Y') ?> <strong>My Book Library System</strong> • Built with ❤️ using Pure PHP OOP
            </p>
            <small>
                <a href="<?= baseUrl() ?>/admin/index.php" class="text-white-50">Admin</a> •
                <a href="https://github.com/yourusername/book-library-management-system" target="_blank" class="text-white-50">GitHub</a>
            </small>
        </div>
    </footer>
</body>
</html>