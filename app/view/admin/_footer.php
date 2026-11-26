  </div>
</div>
<script>
document.addEventListener('submit', function (e) {
  var f = e.target;
  if (f.dataset.confirm && !confirm(f.dataset.confirm)) { e.preventDefault(); }
});
</script>
</body>
</html>
