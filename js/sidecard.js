// js/sidecard.js  — Universal SideCard controller (used by tasks.js, rewards.js, children.js)
(function () {
  const SideCard = {
    init() {
      this.card = document.getElementById('sideCard');
      this.backdrop = document.getElementById('sideCardBackdrop');
      this.form = document.getElementById('sideCardForm');
      this.fields = document.getElementById('sideCardFields');
      this.entityInput = this.form ? this.form.querySelector("input[name='entity_id']") : null;
      this.title = document.getElementById('sideCardTitle');
      this.btnClose = document.getElementById('sideCardClose');
      this.btnCancel = document.getElementById('sideCardCancel');

      if (!this.card || !this.form || !this.fields) {
        console.warn('SideCard: required elements missing.');
        return;
      }

      this.btnClose && this.btnClose.addEventListener('click', () => this.close());
      this.btnCancel && this.btnCancel.addEventListener('click', () => this.close());
      if (this.backdrop) this.backdrop.addEventListener('click', () => this.close());

      // ESC key close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !this.card.classList.contains('hidden')) this.close();
      });

      // expose
      window.SideCard = {
        open: (opts) => SideCard.open(opts),
        close: () => SideCard.close(),
        formElement: SideCard.form,
        fieldsContainer: SideCard.fields
      };
    },

    open({ title = 'Form', mode = '', entityId = 0, innerHTML = '', focusSelector = null } = {}) {
      this.title.textContent = title;
      if (this.form && this.form.mode) this.form.mode.value = mode;
      // support both task_id and entity_id
      if (this.form && this.form.task_id) this.form.task_id.value = String(entityId || 0);
      if (this.entityInput) this.entityInput.value = String(entityId || 0);

      // inject fields
      this.fields.innerHTML = innerHTML;

      // show backdrop + card
      if (this.backdrop) {
        this.backdrop.classList.remove('hidden');
        this.backdrop.setAttribute('aria-hidden', 'false');
      }
      this.card.classList.remove('hidden');
      this.card.setAttribute('aria-hidden', 'false');

      // small delay then focus
      setTimeout(() => {
        if (focusSelector) {
          const el = this.fields.querySelector(focusSelector) || document.querySelector(focusSelector);
          if (el) el.focus();
        } else {
          const firstInput = this.fields.querySelector('input,select,textarea');
          if (firstInput) firstInput.focus();
        }
      }, 20);
    },

    close() {
      this.card.classList.add('hidden');
      this.card.setAttribute('aria-hidden', 'true');
      if (this.backdrop) {
        this.backdrop.classList.add('hidden');
        this.backdrop.setAttribute('aria-hidden', 'true');
      }
      // optionally clear fields: this.fields.innerHTML = '';
    }
  };

  // init on DOM ready
  document.addEventListener('DOMContentLoaded', () => SideCard.init());
})();
