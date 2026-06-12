import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Saran pencarian (autocomplete) — dipakai kotak cari beranda, arsip, dan /cari
Alpine.data('searchSuggest', (url, kategori = null, initial = '') => ({
    q: initial,
    items: [],
    open: false,
    timer: null,
    controller: null,

    fetchItems() {
        clearTimeout(this.timer);

        const term = this.q.trim();

        if (term.length < 2) {
            this.items = [];
            this.open = false;

            return;
        }

        this.timer = setTimeout(async () => {
            try {
                this.controller?.abort();
                this.controller = new AbortController();

                const params = new URLSearchParams({ q: term });
                if (kategori) params.set('kategori', kategori);

                const response = await fetch(`${url}?${params}`, {
                    signal: this.controller.signal,
                    headers: { Accept: 'application/json' },
                });

                this.items = await response.json();
                this.open = this.items.length > 0;
            } catch {
                // permintaan dibatalkan / jaringan — abaikan
            }
        }, 250);
    },

    close() {
        this.open = false;
    },
}));

Alpine.start();
