<!-- 
    ADMIN GLOBAL LOADER 
    (Identical copy of Customer Mobile Loader for visual consistency)
-->

<style>
    /* Loader Overlay */
    .admin-global-loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 99999; /* Higher than modals */
        display: none; /* Hidden by default */
        align-items: center;
        justify-content: center;
        flex-direction: column;
        pointer-events: all; 
    }

    /* Spinner */
    .admin-loader-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #e0e0e0;
        border-top: 5px solid #000; /* Admin generic black or primary */
        border-radius: 50%;
        animation: admin-spin 1s linear infinite;
    }

    @keyframes admin-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .admin-loader-text {
        margin-top: 20px;
        font-weight: 600;
        color: #000;
        font-size: 14px;
        letter-spacing: 1px;
    }
</style>

<!-- HTML Structure -->
<div id="adminGlobalLoader" class="admin-global-loader-overlay">
    <div class="admin-loader-spinner"></div>
    <div class="admin-loader-text">Loading...</div>
</div>

<!-- Scripts -->
<script>
    let adminLoaderTimeout;

    function showGlobalLoader() {
        // Clear any existing timer
        clearTimeout(adminLoaderTimeout);
        
        // Slight delay to prevent flickering on instant actions
        adminLoaderTimeout = setTimeout(() => {
            const loader = document.getElementById('adminGlobalLoader');
            if(loader) loader.style.display = 'flex';
        }, 300);
    }

    function hideGlobalLoader() {
        clearTimeout(adminLoaderTimeout);
        const loader = document.getElementById('adminGlobalLoader');
        if(loader) loader.style.display = 'none';
    }

    // Safety: Hide on page show (bfcache)
    window.addEventListener('pageshow', function(event) {
        hideGlobalLoader();
    });
</script>
