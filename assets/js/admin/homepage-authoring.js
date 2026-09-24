(() => {
  'use strict';
  document.addEventListener('click', (event) => {
    const select = event.target.closest('.aznet-theme-homepage-media-select');
    const clear = event.target.closest('.aznet-theme-homepage-media-clear');
    if (!select && !clear) return;
    const form = event.target.closest('form');
    if (!form) return;
    const input = form.querySelector('input[name="homepage_featured_image_id"]');
    const preview = form.querySelector('.aznet-theme-homepage-media-preview');
    if (!input) return;
    if (clear) {
      event.preventDefault();
      input.value = '0';
      if (preview) preview.innerHTML = '';
      return;
    }
    event.preventDefault();
    if (!window.wp || !wp.media) return;
    const frame = wp.media({ title: select.dataset.title || 'Chọn ảnh', button: { text: select.dataset.button || 'Dùng ảnh này' }, multiple: false, library: { type: 'image' } });
    frame.on('select', () => {
      const attachment = frame.state().get('selection').first().toJSON();
      input.value = String(attachment.id || 0);
      if (preview) {
        const src = attachment.sizes?.thumbnail?.url || attachment.url || '';
        preview.innerHTML = src ? `<img src="${src}" alt="">` : '';
      }
    });
    frame.open();
  });
})();
