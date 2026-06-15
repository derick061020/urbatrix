@auth
<style>
    .logout-confirm-overlay {
        position: fixed;
        inset: 0;
        z-index: 400;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 17, 24, 0.48);
    }
    .logout-confirm-overlay.open { display: flex; }
    .logout-confirm-card {
        width: 100%;
        max-width: 420px;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 30px 80px -20px rgba(10, 13, 20, .4);
        padding: 24px;
        font-family: 'Poppins', sans-serif;
        animation: logoutConfirmIn .18s ease-out;
    }
    @keyframes logoutConfirmIn {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .logout-confirm-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff1f2;
        color: #dc2626;
        margin-bottom: 14px;
    }
    .logout-confirm-title {
        margin: 0;
        color: #171717;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }
    .logout-confirm-text {
        margin: 8px 0 22px;
        color: #667085;
        font-size: 13px;
        line-height: 1.55;
    }
    .logout-confirm-actions {
        display: flex;
        gap: 10px;
    }
    /* Cada acción (botón Cancelar y el form de Cerrar sesión) ocupa 50% del ancho */
    .logout-confirm-actions > * {
        flex: 1 1 0;
        min-width: 0;
    }
    .logout-confirm-btn {
        width: 100%;
        height: 40px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, border-color .15s, transform .15s;
    }
    .logout-confirm-btn:hover { transform: translateY(-1px); }
    .logout-confirm-cancel {
        background: #fff;
        color: #344054;
    }
    .logout-confirm-cancel:hover { background: #f9fafb; }
    .logout-confirm-submit {
        border-color: #dc2626;
        background: #dc2626;
        color: #fff;
    }
    .logout-confirm-submit:hover {
        border-color: #b91c1c;
        background: #b91c1c;
    }
    @media (max-width: 520px) {
        .logout-confirm-card { padding: 20px; }
        .logout-confirm-actions { flex-direction: column-reverse; }
        .logout-confirm-btn { width: 100%; }
    }
</style>

<div id="logoutConfirmModal" class="logout-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle" aria-describedby="logoutConfirmText">
    <div class="logout-confirm-card">
        <div class="logout-confirm-icon" aria-hidden="true">
            <i class="pi pi-sign-out"></i>
        </div>
        <h2 id="logoutConfirmTitle" class="logout-confirm-title">{{ __('¿Cerrar sesión?') }}</h2>
        <p id="logoutConfirmText" class="logout-confirm-text">
            Vas a salir de tu cuenta actual. Podrás volver a ingresar cuando lo necesites.
        </p>
        <div class="logout-confirm-actions">
            <button type="button" class="logout-confirm-btn logout-confirm-cancel" onclick="closeLogoutModal()">{{ __('Cancelar') }}</button>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="logout-confirm-btn logout-confirm-submit">{{ __('Cerrar sesión') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    const modal = document.getElementById('logoutConfirmModal');
    if (!modal) return;

    let previousOverflow = '';

    window.openLogoutModal = function() {
        previousOverflow = document.body.style.overflow;
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeLogoutModal = function() {
        modal.classList.remove('open');
        document.body.style.overflow = previousOverflow;
    };

    document.querySelectorAll('form[data-logout-confirm]').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            window.openLogoutModal();
        });
    });

    modal.addEventListener('click', function(event) {
        if (event.target === modal) window.closeLogoutModal();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('open')) {
            window.closeLogoutModal();
        }
    });
})();
</script>
@endauth
