<!-- Custom Confirm Modal -->
<div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; border-radius: 16px; padding: 32px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalSlideIn 0.25s ease;">
    <div style="text-align: center; margin-bottom: 20px;">
      <div style="width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h3 id="confirmTitle" style="margin: 0 0 8px; font-size: 18px; color: #1f2937;">Επιβεβαίωση</h3>
      <p id="confirmMessage" style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.5;"></p>
    </div>
    <div style="display: flex; gap: 12px;">
      <button id="confirmCancel" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid #e5e7eb; background: white; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s;">Ακύρωση</button>
      <button id="confirmOk" style="flex: 1; padding: 12px; border-radius: 10px; border: none; background: #ef4444; color: white; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s;">Διαγραφή</button>
    </div>
  </div>
</div>

<style>
@keyframes modalSlideIn {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
#confirmCancel:hover { background: #f3f4f6; }
#confirmOk:hover { background: #dc2626; }
</style>

<script>
window.siteConfirm = function(message, title, btnText) {
  return new Promise(function(resolve) {
    var modal = document.getElementById('confirmModal');
    var msgEl = document.getElementById('confirmMessage');
    var titleEl = document.getElementById('confirmTitle');
    var okBtn = document.getElementById('confirmOk');
    var cancelBtn = document.getElementById('confirmCancel');

    titleEl.textContent = title || 'Επιβεβαίωση';
    msgEl.textContent = message;
    okBtn.textContent = btnText || 'Διαγραφή';
    modal.style.display = 'flex';

    function cleanup() {
      modal.style.display = 'none';
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      modal.removeEventListener('click', onOverlay);
    }
    function onOk() { cleanup(); resolve(true); }
    function onCancel() { cleanup(); resolve(false); }
    function onOverlay(e) { if (e.target === modal) { cleanup(); resolve(false); } }

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
    modal.addEventListener('click', onOverlay);
  });
};

function initConfirmForms() {
  document.querySelectorAll('form[data-confirm]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var msg = this.getAttribute('data-confirm');
      var title = this.getAttribute('data-confirm-title') || 'Επιβεβαίωση';
      var btn = this.getAttribute('data-confirm-btn') || 'Διαγραφή';
      var f = this;
      siteConfirm(msg, title, btn).then(function(ok) {
        if (ok) {
          var clone = f.cloneNode(true);
          f.parentNode.replaceChild(clone, f);
          clone.submit();
        }
      });
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initConfirmForms);
} else {
  initConfirmForms();
}
</script>
