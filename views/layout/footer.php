  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- JS commun -->
  <script src="/public/js/main.js"></script>
  <?php if (isset($extraJs)): ?>
    <script src="<?= $extraJs ?>"></script>
  <?php endif; ?>
</body>
</html>
