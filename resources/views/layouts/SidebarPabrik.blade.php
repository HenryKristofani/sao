<div id="sidebar" class="sidebar">
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
            <a href="#"
               class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark">
                <span class="me-2">🛒</span> PO Beli
            </a>
        </li>
        <li style="margin: 5px 0;">
            <a href="#"
               class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark">
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
    }

    .content {
        margin-left: 230px;
        padding: 20px;
    }
</style>