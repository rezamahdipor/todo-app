@include('partials.side-menu')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('time[data-fa]').forEach((t) => {
            const d = new Date(t.getAttribute('datetime') + 'T00:00:00');
            if (!isNaN(d)) {
                t.textContent = d.toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long', day: 'numeric' });
            }
        });
    });
</script>