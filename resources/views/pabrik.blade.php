@extends('layouts.app')

@section('content')
<style>
    .sidebar {
        width: 230px;
        background-color: #f5f5f5;
        min-height: calc(100vh - 60px);
        border-right: 1px solid #e0e0e0;
        transition: margin-left 0.3s ease;
    }

    .sidebar.hidden {
        margin-left: -230px;
    }

    .content {
        flex: 1;
        padding: 20px;
        transition: margin-left 0.3s ease;
    }

    .toggle-btn {
        background-color: #f5f5f5;
        border: none;
        border-radius: 0 5px 5px 0;
        padding: 10px;
        position: fixed;
        left: 230px;
        top: 70px;
        z-index: 1000;
        cursor: pointer;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        transition: left 0.3s ease;
    }

    .toggle-btn.hidden {
        left: 0;
    }
</style>

<div style="display: flex; width: 100%; margin-top: 60px; min-height: calc(100vh - 60px);">
    <!-- Sidebar -->
    @include('layouts.SidebarPabrik')    

    <!-- Main Content -->
    <div id="content" class="content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>Welcome!</h1>
                <p>This is your main content area.</p>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleBtn');

            sidebar.classList.toggle('hidden');
            toggleBtn.classList.toggle('hidden');

            // Store the sidebar state in localStorage
            const isHidden = sidebar.classList.contains('hidden');
            localStorage.setItem('sidebarHidden', isHidden);
        }

        // Check sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const isHidden = localStorage.getItem('sidebarHidden') === 'true';
            if (isHidden) {
                document.getElementById('sidebar').classList.add('hidden');
                document.getElementById('toggleBtn').classList.add('hidden');
            }
        });
    </script>
    @endsection