<div id="flash-container" style="
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
"></div>

<script>
    // Create the container if it doesn't exist
    if (!document.getElementById('flash-container')) {
        const container = document.createElement('div');
        container.id = 'flash-container';
        container.style = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }

    function flash(message, type = 'success', duration = 5000) {
        const container = document.getElementById('flash-container');

        const messageEl = document.createElement('div');
        messageEl.textContent = message;
        messageEl.style.cssText = `
        padding: 12px 20px;
        margin-bottom: 10px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
        font-weight: 500;
        max-width: 300px;
    `;

        container.appendChild(messageEl);

        // Animate in
        setTimeout(() => {
            messageEl.style.transform = 'translateX(0)';
            messageEl.style.opacity = '1';
        }, 10);

        // Auto remove
        const removeMessage = () => {
            messageEl.style.transform = 'translateX(100%)';
            messageEl.style.opacity = '0';
            setTimeout(() => messageEl.remove(), 300);
        };

        if (duration > 0) {
            setTimeout(removeMessage, duration);
        }

        // Click to close
        messageEl.addEventListener('click', removeMessage);
    }

    // Create global functions
    window.flash = flash;
    window.flashSuccess = (msg, dur) => flash(msg, 'success', dur);
    window.flashError = (msg, dur) => flash(msg, 'error', dur);
    window.flashWarning = (msg, dur) => flash(msg, 'warning', dur);
    window.flashInfo = (msg, dur) => flash(msg, 'info', dur);

    console.log('Flash functions ready! Try: flashSuccess("Hello World")');

    // Handle Laravel session messages
    @if (session()->has('success'))
        flash('{{ session('success') }}', 'success');
    @endif
    @if (session()->has('error'))
        flash('{{ session('error') }}', 'error');
    @endif
    @if (session()->has('warning'))
        flash('{{ session('warning') }}', 'warning');
    @endif
    @if (session()->has('info'))
        flash('{{ session('info') }}', 'info');
    @endif

    // Handle form errors - show first error as flash message
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            flash('{{ $error }}', 'error', 7000);
            @break

            {{-- Only show first error --}}
        @endforeach
    @endif
</script>

{{-- Show all errors in form --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
