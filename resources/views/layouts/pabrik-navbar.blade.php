<style>
    body {
        background-color: #2a2a2a; /* Dark background for the body */
    }

    .navbar * {
        color: #fff !important;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background-color: #000000; /* Black background for navbar */
        border-bottom: 1px solid #2a2a2a;
        z-index: 1000;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: center; /* Center the whole navbar container */
    }

    .navbar-container {
        display: flex;
        align-items: center;
        justify-content: space-between; /* Distribute space between left, center, and right sections */
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .navbar-brand-left {
        display: flex;
        align-items: center;
        color: #fff !important; /* Pastikan warna putih */
        font-weight: bold;
        font-size: 24px;
    }

    .logo-placeholder {
        width: 30px;
        height: 30px;
        margin-right: 10px;
        transform: rotate(45deg);
        background: linear-gradient(to bottom right, #007bff, #00c6ff); /* Blue gradient for diamond */
        border-radius: 4px; /* Slight rounding for diamond shape */
    }

    .navbar-links-center {
        flex-grow: 1; /* Allows this section to take up available space */
        display: flex !important;
        justify-content: center; /* Centers the content (ul.navbar-nav) within this section */
        align-items: center;
        width: auto !important;
    }

    .navbar-nav {
        display: flex !important;
        flex-direction: row !important; /* Pastikan horizontal */
        align-items: center; /* Align items vertically in the center */
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 25px; /* Increased gap for better spacing */
        width: auto !important;
    }

    .nav-item {
        /* Ensure items don't shrink */
        flex-shrink: 0;
        margin: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        text-decoration: none;
        color: #fff !important; /* Pastikan warna putih */
        border-radius: 4px;
        transition: all 0.3s ease;
        font-size: 16px;
        font-weight: 500; /* Tambah ketebalan agar lebih jelas */
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1); /* Subtle hover effect */
        color: #fff !important;
    }

    .nav-link.active {
        background-color: rgba(0, 123, 255, 0.3); /* Slightly transparent blue for active link */
        color: #fff !important;
    }

    .nav-icon {
        margin-right: 8px;
    }

    .navbar-actions-right {
        display: flex;
        align-items: center;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        padding: 10px 20px; /* Larger padding for button */
        text-decoration: none;
        color: #fff !important; /* White text for button */
        border-radius: 5px; /* Rounded corners for button */
        transition: all 0.3s ease;
        background-color: #007bff; /* Blue background for button */
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    .logout-btn:hover {
        background-color: #0056b3; /* Darker blue on hover */
        color: #fff !important;
    }

    .content {
        margin-top: 70px;
        padding: 20px;
        background-color: #ffffff; /* White background for content area */
        min-height: calc(100vh - 70px);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand-left">
            <div class="logo-placeholder"></div>
            <span>Pabrik App</span> <!-- Placeholder for app name -->
        </div>
        
        <div class="navbar-links-center">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="{{ route('pabrik.po-jual') }}"
                       class="nav-link {{ Request::is('pabrik/po-jual*') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span> PO Jual
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pabrik.po-beli') }}"
                       class="nav-link {{ Request::is('pabrik/po-beli*') ? 'active' : '' }}">
                        <span class="nav-icon">🛒</span> PO Beli
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pabrik.scheduler') }}"
                       class="nav-link {{ Request::is('pabrik/scheduler*') ? 'active' : '' }}">
                        <span class="nav-icon">📅</span> Scheduler
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pabrik.item') }}"
                       class="nav-link {{ Request::is('pabrik/item*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span> Item
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pabrik.pelanggan') }}"
                       class="nav-link {{ Request::is('pabrik/pelanggan*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span> Pelanggan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon">📝</span> Rekap Penjualan
                    </a>
                </li>
            </ul>
        </div>

        <div class="navbar-actions-right">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-btn">
                    <span class="nav-icon">🚪</span> Logout
                </button>
            </form>
        </div>
    </div>
</nav>