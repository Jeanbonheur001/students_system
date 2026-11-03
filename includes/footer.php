    </main>
  </div>
</div>
<script>
// close the mobile sidebar when clicking outside of it
document.addEventListener('click', function (ev) {
  var sb = document.getElementById('sidebar');
  if (sb && sb.classList.contains('open') && !sb.contains(ev.target) && !ev.target.closest('.burger')) {
    sb.classList.remove('open');
  }
});
// confirm dialogs for dangerous actions
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (e) { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
});
// live table search
var q = document.getElementById('tableSearch');
if (q) q.addEventListener('input', function () {
  var term = q.value.toLowerCase();
  document.querySelectorAll('#dataTable tbody tr').forEach(function (tr) {
    tr.style.display = tr.textContent.toLowerCase().indexOf(term) > -1 ? '' : 'none';
  });
});
</script>
</body>
</html>
