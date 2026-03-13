<?php
// Helper to check active state
$current_page = $current_page ?? 'dashboard';
?>
<div class="bottom-nav">
    <a href="<?= BASE_URL ?>admin/dashboard" class="nav-item <?= $current_page == 'dashboard' ? 'active' : '' ?>" onclick="showGlobalLoader()">
        <img src="<?= BASE_URL ?>assets/icons/dashboard.png" class="nav-icon-img" alt="Dash">
        <span>Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>product/index" class="nav-item <?= $current_page == 'products' ? 'active' : '' ?>" onclick="showGlobalLoader()">
        <img src="<?= BASE_URL ?>assets/icons/products.png" class="nav-icon-img" alt="Prod">
        <span>Products</span>
    </a>
    <a href="<?= BASE_URL ?>feedback/index" class="nav-item <?= $current_page == 'feedback' ? 'active' : '' ?>" onclick="showGlobalLoader()">
        <img src="<?= BASE_URL ?>assets/icons/feedback.png" class="nav-icon-img" alt="Feed">
        <span>Feedback</span>
    </a>
    <a href="<?= BASE_URL ?>order/manage" class="nav-item <?= $current_page == 'orders' ? 'active' : '' ?>" onclick="showGlobalLoader()">
        <img src="<?= BASE_URL ?>assets/icons/dashboard.png" class="nav-icon-img" alt="Orders">
        <span>Orders</span>
    </a>
    <a href="<?= BASE_URL ?>myShop/index" class="nav-item <?= $current_page == 'myshop' ? 'active' : '' ?>" onclick="showGlobalLoader()">
        <!-- Using Dashboard icon as placeholder as requested -->
        <img src="<?= BASE_URL ?>assets/icons/Myshop.png" class="nav-icon-img" alt="Shop">
        <span>My Shop</span>
    </a>
    <a href="<?= BASE_URL ?>settings/index" class="nav-item <?= $current_page == 'settings' ? 'active' : '' ?>" onclick="showGlobalLoader()">
        <img src="<?= BASE_URL ?>assets/icons/settings.png" class="nav-icon-img" alt="Set">
        <span>Settings</span>
    </a>
</div>

<style>
    /* Icon Styles */
    .nav-icon-img {
        width: 24px;
        height: 24px;
        display: block;
        margin: 0 auto 4px auto;
        object-fit: contain;
        opacity: 0.6;
    }

    .nav-item {
        padding: 5px 4px;
        border-radius: 12px;
        transition: background-color 0.2s;
    }

    .nav-item.active,
    .nav-item:hover {
        background-color: #e1f0ff;
    }

    .nav-item.active .nav-icon-img {
        opacity: 1;
    }
</style>
