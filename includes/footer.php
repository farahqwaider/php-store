    </main>
    <footer class="border-top bg-white py-4 mt-auto">
        <div class="container<?= $adminLayout ? '-fluid' : '' ?> text-center text-secondary small">
            &copy; <?= date('Y') ?> My Store. Built with PHP and MySQL.
        </div>
    </footer>
    <?php if ($adminLayout): ?>
        </div> <!-- End Main Content Column -->
    </div> <!-- End Row -->
</div> <!-- End Container Fluid -->
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
