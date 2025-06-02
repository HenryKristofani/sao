<div id="sidebar" class="sidebar">
    <button id="sidebarToggle" class="sidebar-toggle">
        <span class="toggle-icon">◀</span>
    </button>
    <div class="sidebar-content">
        <div style="padding: 15px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; margin-right: 10px;"></div>
                <div>
                    <p style="margin: 0; font-weight: bold;">{{ Auth::user()->email }}</p>
                    <small style="color: #6c757d;">{{ Auth::user()->role }}</small>
                </div>
            </div>
        </div>

        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="margin: 5px 0;">
                <a href="{{ route('pabrik.po-jual') }}"
                   class="d-flex align-items-center px-3 py-2 text-decoration-none {{ Request::is('pabrik/po-jual*') ? 'fw-bold text-primary' : 'text-dark' }}">
                    <span class="me-2">📊</span> PO Jual
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="{{ route('pabrik.po-beli') }}"
                   class="d-flex align-items-center px-3 py-2 text-decoration-none {{ Request::is('pabrik/po-beli*') ? 'fw-bold text-primary' : 'text-dark' }}">
                    <span class="me-2">🛒</span> PO Beli
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="{{ route('pabrik.scheduler') }}"
                   class="d-flex align-items-center px-3 py-2 text-decoration-none {{ Request::is('pabrik/scheduler*') ? 'fw-bold text-primary' : 'text-dark' }}">
                    <span class="me-2">📅</span> Scheduler
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="{{ route('pabrik.item') }}"
                   class="d-flex align-items-center px-3 py-2 text-decoration-none {{ Request::is('pabrik/item*') ? 'fw-bold text-primary' : 'text-dark' }}">
                    <span class="me-2">📦</span> Item
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="{{ route('pabrik.pelanggan') }}"
                   class="d-flex align-items-center px-3 py-2 text-decoration-none {{ Request::is('pabrik/pelanggan*') ? 'fw-bold text-primary' : 'text-dark' }}">
                    <span class="me-2">👥</span> Pelanggan
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="#"
                   class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark">
                    <span class="me-2">📝</span> Rekap Penjualan
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .sidebar {
        position: fixed;
        top: 60px;
        left: 0;
        width: 230px;
        background-color: #f5f5f5;
        height: calc(100vh - 60px);
        border-right: 1px solid #e0e0e0;
        z-index: 999;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed {
        width: 50px;
    }

    .sidebar-content {
        width: 100%;
        overflow-x: hidden;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed .sidebar-content {
        opacity: 0;
        visibility: hidden;
    }

    .sidebar-toggle {
        position: absolute;
        right: -15px;
        top: 20px;
        width: 30px;
        height: 30px;
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed .sidebar-toggle .toggle-icon {
        transform: rotate(180deg);
    }

    .content {
        margin-left: 230px;
        padding: 20px;
        transition: all 0.3s ease;
        width: calc(100% - 230px);
    }

    .content.expanded {
        margin-left: 50px;
        width: calc(100% - 50px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    // Check if sidebar state is saved in localStorage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
    }

    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
});
</script>