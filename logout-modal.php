<?php
/**
 * logout-modal.php
 * Reusable logout confirmation modal.
 * 
 * HOW TO USE:
 * 1. Include this file at the BOTTOM of any page (before </body>):
 *    <?php include 'path/to/logout-modal.php'; ?>
 *
 * 2. On your logout button/link, call:
 *    onclick="openLogoutModal()"
 *
 * 3. Make sure logout.php is at the correct path below.
 *    Adjust LOGOUT_PATH if needed.
 */

define('LOGOUT_PATH', '/balikgamit/logout.php'); // ← change if needed
?>

<!-- ════════════════════════════════════════════════
     LOGOUT MODAL — matches BalikGamit design system
     ════════════════════════════════════════════════ -->
<style>
  /* ── Import only if Poppins not already loaded on the page ── */
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

  /* ── Overlay backdrop ── */
  #logout-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(1, 28, 57, 0.55);   /* --primary at 55% */
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    animation: lo-fade-in 0.2s ease both;
  }

  #logout-overlay.active {
    display: flex;
  }

  @keyframes lo-fade-in {
    from { opacity: 0; }
    to   { opacity: 1; }
  }

  /* ── Modal card ── */
  .lo-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 40px 36px 28px;
    width: 100%;
    max-width: 400px;
    margin: 16px;
    box-shadow:
      0 20px 60px rgba(1, 28, 57, 0.22),
      0 4px 16px rgba(1, 28, 57, 0.10);
    animation: lo-slide-up 0.28s cubic-bezier(0.22, 1, 0.36, 1) both;
    font-family: 'Poppins', sans-serif;
    text-align: center;
  }

  @keyframes lo-slide-up {
    from { opacity: 0; transform: translateY(18px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)    scale(1);    }
  }

  /* ── Icon circle ── */
  .lo-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #eef2fb;
    border: 1.5px solid #d0d9f5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }

  .lo-icon svg {
    width: 28px;
    height: 28px;
    color: #2a5fd6; /* --accent */
  }

  /* ── Heading ── */
  .lo-card h2 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #011c39; /* --primary */
    margin: 0 0 8px;
    letter-spacing: -0.01em;
  }

  /* ── Description ── */
  .lo-card p {
    font-size: 0.84rem;
    font-weight: 400;
    color: #6b7280; /* --muted */
    line-height: 1.65;
    margin: 0 0 28px;
  }

  /* ── Divider ── */
  .lo-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0 -36px 24px;
  }

  /* ── Button row ── */
  .lo-actions {
    display: flex;
    gap: 10px;
  }

  .lo-btn {
    flex: 1;
    height: 44px;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.87rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s, transform 0.12s;
    border: none;
    outline: none;
  }

  /* Cancel */
  .lo-btn-cancel {
    background: #f8f9fa;
    color: #374151;
    border: 1.5px solid #d1d5db;
  }
  .lo-btn-cancel:hover {
    background: #f1f3f5;
    border-color: #9ca3af;
  }
  .lo-btn-cancel:active {
    transform: scale(0.98);
  }

  /* Confirm */
  .lo-btn-confirm {
    background: #011c39; /* --primary */
    color: #ffffff;
  }
  .lo-btn-confirm:hover {
    background: #1e3a6e; /* --primary-hover */
  }
  .lo-btn-confirm:active {
    transform: scale(0.98);
  }


</style>

<!-- Modal markup -->
<div id="logout-overlay" role="dialog" aria-modal="true" aria-labelledby="lo-title">
  <div class="lo-card">

    <!-- Icon -->
    <div class="lo-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </div>

    <h2 id="lo-title">Sign out of BalikGamit?</h2>
    <p>You're about to end your current session.<br>Any unsaved changes will be lost.</p>

    <div class="lo-divider"></div>

    <div class="lo-actions">
      <button class="lo-btn lo-btn-cancel" type="button" onclick="closeLogoutModal()">
        Cancel
      </button>
      <!-- POST form so logout.php can verify intent server-side -->
      <form method="POST" action="<?= LOGOUT_PATH ?>" style="flex:1;display:flex;">
        <input type="hidden" name="confirm_logout" value="1">
        <button class="lo-btn lo-btn-confirm" type="submit" style="flex:1;">
          Yes, Sign Out
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  function openLogoutModal() {
    const overlay = document.getElementById('logout-overlay');
    overlay.classList.add('active');
    // Trap focus inside modal
    document.body.style.overflow = 'hidden';
  }

  function closeLogoutModal() {
    const overlay = document.getElementById('logout-overlay');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  // Close on backdrop click
  document.getElementById('logout-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeLogoutModal();
  });

  // Close on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLogoutModal();
  });
</script>