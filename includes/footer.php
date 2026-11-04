<!-- Site Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h2 class="h5 fw-bold text-dark">NoteShareHub</h2>
                <p class="text-muted small mb-0">
                    <?= e(APP_TAGLINE) ?> A student project for sharing and finding study notes.
                </p>
            </div>
            <div class="col-md-3">
                <h3 class="footer-heading">About</h3>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= BASE_URL ?>/about.php" class="footer-link">About this project</a></li>
                    <li><a href="<?= BASE_URL ?>/explore.php" class="footer-link">Explore notes</a></li>
                    <li><a href="<?= BASE_URL ?>/setup.php" class="footer-link">Database setup</a></li>
                </ul>
            </div>
            <div class="col-md-4" id="contact">
                <h3 class="footer-heading">Contact</h3>
                <p class="text-muted small mb-0">
                    For help with this college project, contact your course instructor or project administrator.
                </p>
            </div>
        </div>
        <div class="border-top mt-4 pt-3 small text-muted">
            &copy; <?= date('Y') ?> <?= e(APP_SHORT_NAME) ?>. All rights reserved.
        </div>
    </div>
</footer>

<!-- Global Toast Container for Dynamic Notifications -->
<div id="global-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>

<!-- Bootstrap 5 JavaScript Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Application JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/script.js"></script>

</body>
</html>
