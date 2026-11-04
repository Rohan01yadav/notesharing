document.addEventListener('DOMContentLoaded', () => {
  const search = document.querySelector('[data-note-search]');
  const cards = Array.from(document.querySelectorAll('[data-note-card]'));

  if (search && cards.length) {
    const initialQuery = new URLSearchParams(window.location.search).get('q') || '';
    search.value = initialQuery;

    const filterCards = () => {
      const query = search.value.trim().toLowerCase();
      cards.forEach((card) => {
        card.hidden = !card.textContent.toLowerCase().includes(query);
      });
      const noResults = document.getElementById('noResults');
      if (noResults) {
        noResults.hidden = cards.some((card) => !card.hidden);
      }
    };

    filterCards();
    search.addEventListener('input', () => {
      filterCards();
    });
  }

  document.querySelectorAll('[data-demo-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const message = form.querySelector('[data-demo-result]');
      if (message) {
        message.textContent = form.dataset.demoMessage
          || 'This static GitHub Pages demo has no PHP/MySQL backend. This form did not send or save your information.';
        message.hidden = false;
        message.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  });

  document.querySelectorAll('[data-demo-action]').forEach((button) => {
    button.addEventListener('click', () => {
      const message = button.closest('[data-note-card]')?.querySelector('[data-action-result]');
      if (message) {
        message.textContent = button.dataset.demoAction === 'download'
          ? 'Downloads are unavailable in this static demo.'
          : 'Bookmarks require the PHP/MySQL application.';
        message.hidden = false;
      }
    });
  });
});
