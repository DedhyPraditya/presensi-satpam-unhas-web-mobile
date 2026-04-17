</div> <!-- end main -->

<script>
// Initialize Lucide
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Sidebar Toggle
function toggleSidebar(){
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(sidebar) sidebar.classList.toggle('active');
    if(overlay) overlay.classList.toggle('active');
}

window.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Image Preview Logic
function viewImage(src) {
    const modal = document.getElementById('globalImageModal');
    const modalImg = document.getElementById('globalModalImg');
    if (modal && modalImg) {
        modalImg.src = src;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scroll
    }
}

function closeImageModal() {
    const modal = document.getElementById('globalImageModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restore scroll
    }
}
</script>

<!-- Global Image Preview Modal -->
<div id="globalImageModal" class="image-modal" onclick="closeImageModal()">
    <div class="close-btn">
        <i data-lucide="x"></i>
    </div>
    <img id="globalModalImg" src="" onclick="event.stopPropagation()">
</div>

<?php if(isset($extra_js) && is_array($extra_js)): ?>
    <?php foreach($extra_js as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
