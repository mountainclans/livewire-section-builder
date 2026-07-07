@once
    <script>
        // Персистентное UI-сворачивание секций/репитеров: состояние в localStorage
        // по ULID. Пустой ключ (несохранённый репитер) — только до перезагрузки.
        window.sbCollapsible = function (storageKey) {
            return {
                collapsed: false,
                init() {
                    if (storageKey) {
                        this.collapsed = localStorage.getItem(storageKey) === '1';
                    }
                },
                toggle() {
                    this.collapsed = !this.collapsed;

                    if (storageKey) {
                        localStorage.setItem(storageKey, this.collapsed ? '1' : '0');
                    }
                },
            };
        };
    </script>
@endonce
