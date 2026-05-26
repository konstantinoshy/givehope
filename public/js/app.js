// GiveHope — πλοήγηση (mobile), φόρμες, κοινοποίηση

(function () {
  'use strict';

  // --- Mobile nav
  function initMobileNav() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const mobileNavFullscreen = document.getElementById('mobileNav');
    const links = document.querySelectorAll('.mobile-nav-link');

    if (!mobileToggle || !mobileNavFullscreen) return;

    let isOpen = false;

    function openMenu() {
      isOpen = true;
      mobileToggle.classList.add('active');
      mobileNavFullscreen.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      isOpen = false;
      mobileToggle.classList.remove('active');
      mobileNavFullscreen.classList.remove('open');
      document.body.style.overflow = '';
    }

    // Hamburger = toggle (open + close)
    mobileToggle.addEventListener('click', function (e) {
      e.preventDefault();
      if (isOpen) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    // Close on link click
    links.forEach(function (link) {
      link.addEventListener('click', function () {
        setTimeout(closeMenu, 150);
      });
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen) {
        closeMenu();
      }
    });

    // Resize display handler
    window.addEventListener('resize', function () {
      if (window.innerWidth > 768 && isOpen) {
        closeMenu();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileNav);
  } else {
    initMobileNav();
  }



  // --- Share / copy link
  window.copyShareLink = function (url) {
    const copyBtn = document.querySelector('.share-btn.copy');
    const copyText = document.getElementById('copy-text');

    // Fallback πρώτα (δουλεύει αξιόπιστα σε HTTP localhost)
    var success = false;
    var textArea = document.createElement('textarea');
    textArea.value = url;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    textArea.style.top = '-9999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      success = document.execCommand('copy');
    } catch (e) {
      success = false;
    }
    document.body.removeChild(textArea);

    if (success) {
      showCopySuccess(copyBtn, copyText);
    } else if (navigator.clipboard && navigator.clipboard.writeText) {
      // Δεύτερη προσπάθεια με Clipboard API
      navigator.clipboard.writeText(url).then(function () {
        showCopySuccess(copyBtn, copyText);
      }).catch(function () {
        alert('Δεν ήταν δυνατή η αντιγραφή. Αντιγράψτε χειροκίνητα: ' + url);
      });
    } else {
      alert('Δεν ήταν δυνατή η αντιγραφή. Αντιγράψτε χειροκίνητα: ' + url);
    }
  };

  function showCopySuccess(copyBtn, copyText) {
    if (copyBtn) {
      copyBtn.classList.add('copied');
    }
    if (copyText) {
      copyText.textContent = '✓ Αντιγράφηκε!';
    }

    // Επαναφορά μετά από 2 δευτερόλεπτα
    setTimeout(function () {
      if (copyBtn) {
        copyBtn.classList.remove('copied');
      }
      if (copyText) {
        copyText.textContent = 'Αντιγραφή Link';
      }
    }, 2000);
  }

  // --- UX (notices, scroll)

  // Αυτόματη απόκρυψη ειδοποιήσεων με κλικ
  document.addEventListener('click', (e) => {
    const notice = e.target.closest('.notice');
    if (notice && !notice.querySelector('form')) {
      notice.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
      notice.style.opacity = '0';
      notice.style.transform = 'translateY(-10px)';
      setTimeout(() => notice.remove(), 200);
    }
  });

  // Ομαλή κύλιση σε anchor αν υπάρχει
  if (window.location.hash) {
    const target = document.querySelector(window.location.hash);
    if (target) {
      setTimeout(() => {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        target.style.transition = 'box-shadow 0.3s ease';
        target.style.boxShadow = '0 0 0 3px rgba(45,106,79,0.3)';
        setTimeout(() => {
          target.style.boxShadow = '';
        }, 2500);
      }, 100);
    }
  }

  // Εμφάνιση μηνύματος επιτυχίας μετά τη δωρεά
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('donated') === '1') {
    const notice = document.createElement('div');
    notice.className = 'notice ok';
    notice.style.cssText = `
      position: fixed;
      top: 90px;
      right: 24px;
      z-index: 1000;
      max-width: 320px;
      animation: slideIn 0.3s ease;
      cursor: pointer;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    `;
    notice.innerHTML = '<strong>Ευχαριστούμε! 💚</strong><br>Η συνεισφορά σας καταχωρήθηκε επιτυχώς.';
    document.body.appendChild(notice);

    // Προσθήκη animation keyframes
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
      }
    `;
    document.head.appendChild(style);

    setTimeout(() => {
      notice.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      notice.style.opacity = '0';
      notice.style.transform = 'translateX(20px)';
      setTimeout(() => notice.remove(), 300);
    }, 4000);

    // Καθαρισμός URL
    history.replaceState(null, '', window.location.pathname + window.location.hash);
  }

  // Προσθήκη κατάστασης φόρτωσης σε φόρμες
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (event) {
      if (!this.checkValidity() || event.defaultPrevented) {
        return;
      }
      const btn = this.querySelector('button[type="submit"]');
      if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'wait';
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Παρακαλώ περιμένετε...';

        // Επανενεργοποίηση μετά από 5 δευτ. σε περίπτωση προβλήματος
        setTimeout(() => {
          btn.disabled = false;
          btn.style.opacity = '1';
          btn.style.cursor = 'pointer';
          btn.innerHTML = originalText;
        }, 5000);
      }
    });
  });

  // Βελτίωση αριθμητικών πεδίων για καλύτερη εμπειρία χρήστη
  document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('wheel', (e) => {
      if (document.activeElement === input) {
        e.preventDefault();
      }
    });
  });

  // --- Επικύρωση φορμών

  function getErrorMessage(field) {
    if (field.dataset.error) {
      return field.dataset.error;
    }
    if (field.validity.valueMissing) {
      return 'Αυτό το πεδίο είναι υποχρεωτικό.';
    }
    if (field.validity.typeMismatch) {
      return 'Η μορφή που δώσατε δεν είναι έγκυρη.';
    }
    if (field.validity.tooShort) {
      return 'Η τιμή είναι πολύ μικρή.';
    }
    if (field.validity.rangeUnderflow) {
      return 'Η τιμή είναι μικρότερη από το επιτρεπτό όριο.';
    }
    return 'Παρακαλώ ελέγξτε το πεδίο.';
  }

  function escapeSelector(value) {
    if (window.CSS && CSS.escape) {
      return CSS.escape(value);
    }
    return value.replace(/"/g, '\\"');
  }

  function getErrorKey(field, groupName) {
    return groupName || field.name || field.id || 'field';
  }

  function findErrorElement(form, key) {
    return form.querySelector(`.field-error[data-error-for="${key}"]`);
  }

  function placeErrorElement(container, errorEl) {
    const last = container.nextElementSibling;
    if (last && last.classList.contains('field-error')) {
      last.replaceWith(errorEl);
    } else {
      container.insertAdjacentElement('afterend', errorEl);
    }
  }

  function setFieldError(field, message, container, groupName) {
    const form = field.form;
    if (!form) return;
    const key = getErrorKey(field, groupName);
    let errorEl = findErrorElement(form, key);
    if (!errorEl) {
      errorEl = document.createElement('div');
      errorEl.className = 'field-error';
      errorEl.dataset.errorFor = key;
    }
    errorEl.textContent = message;
    const target = container || field;
    if (container) {
      placeErrorElement(container, errorEl);
    } else {
      target.insertAdjacentElement('afterend', errorEl);
    }
    field.classList.add('input-error');
  }

  function clearFieldError(field, groupName) {
    const form = field.form;
    if (!form) return;
    const key = getErrorKey(field, groupName);
    const errorEl = findErrorElement(form, key);
    if (errorEl) {
      errorEl.remove();
    }
    field.classList.remove('input-error');
  }

  function validateRadioGroup(form, name, container) {
    const radios = Array.from(form.querySelectorAll(`input[type="radio"][name="${escapeSelector(name)}"]`));
    if (!radios.length) return true;
    const hasSelection = radios.some(radio => radio.checked);
    if (!hasSelection) {
      const message = container?.dataset.error || radios[0].dataset.error || 'Επιλέξτε μία επιλογή.';
      setFieldError(radios[0], message, container, name);
      radios.forEach(radio => radio.classList.add('input-error'));
      return false;
    }
    radios.forEach(radio => clearFieldError(radio, name));
    return true;
  }

  function validateField(field) {
    if (!field.willValidate) return true;
    if (field.type === 'radio') return true;
    if (field.type === 'checkbox') {
      if (field.required && !field.checked) {
        setFieldError(field, getErrorMessage(field));
        return false;
      }
      clearFieldError(field);
      return true;
    }
    if (!field.checkValidity()) {
      setFieldError(field, getErrorMessage(field));
      return false;
    }
    clearFieldError(field);
    return true;
  }

  function initInlineValidation() {
    const forms = document.querySelectorAll('form.js-validate');
    forms.forEach(form => {
      form.setAttribute('novalidate', 'novalidate');
      const fields = Array.from(form.querySelectorAll('input, textarea, select'))
        .filter(field => !['hidden', 'submit', 'button'].includes(field.type));

      form.addEventListener('submit', (event) => {
        const radioGroups = new Set();
        fields.forEach(field => {
          if (field.type === 'radio' && field.required) {
            radioGroups.add(field.name);
          }
        });

        let isValid = true;
        radioGroups.forEach(name => {
          const container = form.querySelector(`[data-radio-group="${name}"]`);
          if (!validateRadioGroup(form, name, container)) {
            isValid = false;
          }
        });

        fields.forEach(field => {
          if (!validateField(field)) {
            isValid = false;
          }
        });

        if (!isValid) {
          event.preventDefault();
          const firstInvalid = form.querySelector('.input-error');
          if (firstInvalid) firstInvalid.focus();
        }
      });

      fields.forEach(field => {
        const eventName = (field.type === 'checkbox' || field.type === 'radio' || field.tagName === 'SELECT')
          ? 'change'
          : 'input';
        field.addEventListener(eventName, () => {
          if (field.type === 'radio') {
            const container = form.querySelector(`[data-radio-group="${field.name}"]`);
            validateRadioGroup(form, field.name, container);
          } else {
            validateField(field);
          }
        });
      });
    });
  }

  // --- Φόρμα νέου εράνου

  function initCharCounters() {
    document.querySelectorAll('[data-char-count]').forEach(field => {
      const counter = document.querySelector(field.dataset.charCount);
      if (!counter) return;
      const recommended = parseInt(field.dataset.recommended || '', 10);
      const max = field.maxLength > 0 ? field.maxLength : null;
      const update = () => {
        const length = field.value.length;
        if (max) {
          counter.textContent = `${length}/${max}`;
          counter.classList.toggle('over', length > max);
        } else if (recommended) {
          counter.textContent = `${length}/${recommended}`;
          counter.classList.toggle('over', length > recommended);
        } else {
          counter.textContent = `${length} χαρακτ.`;
          counter.classList.remove('over');
        }
      };
      field.addEventListener('input', update);
      update();
    });
  }

  function initImagePreview() {
    document.querySelectorAll('[data-image-preview]').forEach(input => {
      const img = document.querySelector(input.dataset.imagePreview);
      const wrapper = document.querySelector(input.dataset.imagePreviewWrapper);
      if (!img || !wrapper) return;
      const update = () => {
        const url = input.value.trim();
        if (!url) {
          img.src = '';
          wrapper.style.display = 'none';
          return;
        }
        img.src = url;
        wrapper.style.display = 'block';
      };
      input.addEventListener('input', update);
      input.addEventListener('change', update);
      update();
    });
  }

  function initDonationAmountHint() {
    const amountInput = document.getElementById('donationAmount');
    const hint = document.getElementById('donationAmountHint');
    if (!amountInput || !hint) return;
    const update = () => {
      const value = Math.max(1, Number(amountInput.value || 0));
      hint.textContent = `Θα δωρίσετε ${value.toLocaleString('el-GR')}€`;
    };
    amountInput.addEventListener('input', update);
    update();
  }

  initInlineValidation();
  initCharCounters();
  initImagePreview();
  initDonationAmountHint();

  // --- Scroll reveal

  /**
   * Αποκάλυψη στοιχείων καθώς εισέρχονται στην οθόνη
   * Χρησιμοποιεί IntersectionObserver για βέλτιστη απόδοση
   */
  function initScrollReveal() {
    var items = document.querySelectorAll('.reveal-item');
    if (!items.length) return;

    // Άμεση εμφάνιση στοιχείων που είναι ήδη ορατά
    function revealIfVisible(el) {
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight - 40 && rect.bottom > 0) {
        el.classList.add('revealed');
        return true;
      }
      return false;
    }

    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    items.forEach(function (el) {
      // Πρώτα έλεγχος αν είναι ήδη ορατό, αλλιώς παρακολούθηση
      if (!revealIfVisible(el)) {
        revealObserver.observe(el);
      }
    });
  }

  initScrollReveal();

  // --- Floating header

  function initFloatingHeader() {
    var header = document.querySelector('.site-header');
    if (!header) return;
    window.addEventListener('scroll', function () {
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  initFloatingHeader();

})();
