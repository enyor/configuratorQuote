document.addEventListener('DOMContentLoaded', async () => {
    const root = document.getElementById('pc-configurator-root');
    const cart = [];

    // Obtener estructura de producto desde AJAX
    const res = await fetch(pc_ajax_url + '?action=pc_get_config&nonce=' + pc_nonce);
    const config = await res.json();

    if (!config.success) {
        root.innerHTML = "<p>No hay configuraciones disponibles.</p>";
        return;
    }

    const data = config.data;
    const state = {
        selected: {},
        skuParts: [],
        baseFilter: ''
    };

    function renderForm() {
        root.innerHTML = '';
        const form = document.createElement('form');

        data.forEach((feature, index) => {
            const field = document.createElement('div');
            field.className = 'pc-feature-block';

            const label = document.createElement('label');
            label.textContent = feature.name;
            field.appendChild(label);

            const select = document.createElement('select');
            select.dataset.feature = feature.name;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Seleccionar --';
            select.appendChild(defaultOption);

            const items = feature.items.filter(item => {
                if (index === 0) return true;
                if (!state.baseFilter) return true;
                return item.name.startsWith('ALL -') || item.name.startsWith(state.baseFilter);
            });

            items.forEach(item => {
                const opt = document.createElement('option');
                opt.value = JSON.stringify(item);
                opt.textContent = item.name;
                select.appendChild(opt);
            });

            select.addEventListener('change', (e) => {
                const selectedItem = JSON.parse(e.target.value || 'null');
                state.selected[feature.name] = selectedItem;
                if (index === 0) {
                    state.baseFilter = selectedItem?.name || '';
                }
                updateSKU();
                renderForm(); // rerender for dynamic filtering
            });

            field.appendChild(select);
            form.appendChild(field);
        });

        const skuDisplay = document.createElement('p');
        skuDisplay.id = 'pc-sku-display';
        skuDisplay.textContent = 'SKU: ' + (state.skuParts.join('-') || 'N/A');
        form.appendChild(skuDisplay);

        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.textContent = 'Add to Quote';
        addButton.disabled = data.some(f => !state.selected[f.name]);

        addButton.addEventListener('click', () => {
            cart.push({ ...state.selected });
            alert('Producto agregado al carrito');
        });

        form.appendChild(addButton);
        root.appendChild(form);
    }

    function updateSKU() {
        state.skuParts = data.map(f => state.selected[f.name]?.sku || '');
        const skuText = 'SKU: ' + state.skuParts.join('-');
        document.getElementById('pc-sku-display')?.textContent = skuText;
    }

    renderForm();
});
