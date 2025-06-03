document.addEventListener('DOMContentLoaded', async () => {
    const root = document.getElementById('pc-configurator-root');
    const cart = [];

    // Obtener productos configurables
    const productRes = await fetch(pc_ajax_url + '?action=pc_get_products&nonce=' + pc_nonce);
    const productData = await productRes.json();
    if (!productData.success || !productData.data.length) {
        root.innerHTML = '<p>No hay productos configurables disponibles.</p>';
        return;
    }

    const state = {
        selectedProduct: null,
        selected: {},
        skuParts: [],
        baseFilter: '',
        productDataMap: {}
    };

    // Mapeamos productos por ID
    productData.data.forEach(p => {
        state.productDataMap[p.ID] = p.characteristics;
    });

    renderProductSelector();

    function renderProductSelector() {
        root.innerHTML = '<h3>Selecciona un producto</h3>';
        const select = document.createElement('select');
        select.innerHTML = '<option value="">-- Selecciona producto --</option>';
        productData.data.forEach(prod => {
            const opt = document.createElement('option');
            opt.value = prod.ID;
            opt.textContent = prod.title;
            select.appendChild(opt);
        });

        select.addEventListener('change', () => {
            const selectedID = select.value;
            if (!selectedID) {
                state.selectedProduct = null;
                root.innerHTML = '<p>Por favor selecciona un producto</p>';
                return;
            }

            state.selectedProduct = selectedID;
            state.selected = {};
            state.skuParts = [];
            state.baseFilter = '';
            root.innerHTML = '';
            root.appendChild(select);
            renderForm(state.productDataMap[selectedID]);
        });

        root.appendChild(select);
    }

    function renderForm(characteristics) {
        const oldForms = root.querySelectorAll('form, hr, .pc-feature-block, #pc-sku-display');
        oldForms.forEach(el => el.remove());
        const form = document.createElement('form');
        root.appendChild(document.createElement('hr'));
        root.appendChild(form);

        characteristics.forEach((feature, index) => {
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

                const previouslySelected = state.selected[feature.name]?.name;
                if (previouslySelected && item.name === previouslySelected) {
                    opt.selected = true;
                }

                select.appendChild(opt);
            });

            select.addEventListener('change', (e) => {
                const selectedItem = JSON.parse(e.target.value || 'null');
                if (!selectedItem) return;

                const isFirst = feature.name === characteristics[0].name;

                if (isFirst) {
                    state.selected = { [feature.name]: selectedItem };
                    state.baseFilter = selectedItem.name || '';
                    renderForm(characteristics);
                } else {
                    state.selected[feature.name] = selectedItem;
                    updateSKU(characteristics);
                }
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
        addButton.disabled = !characteristics.every(f => state.selected[f.name]);

        addButton.addEventListener('click', () => {
            const copy = JSON.parse(JSON.stringify(state.selected));
            cart.push(copy);
            alert('Producto agregado al carrito');
        });

        form.appendChild(addButton);
        updateSKU(characteristics);
    }

    function updateSKU(characteristics) {
        state.skuParts = characteristics.map(f => state.selected[f.name]?.sku || '');
        const base = productData.data.find(p => p.ID == state.selectedProduct)?.base_sku || '';
        const skuText = 'SKU: ' + [base, ...state.skuParts].filter(Boolean).join('-');
        const skuEl = document.getElementById('pc-sku-display');
        if (skuEl) skuEl.textContent = skuText;

        // Habilitar botón si todas las características están seleccionadas
        const addBtn = document.querySelector('button[type="button"]');
        if (addBtn) {
            addBtn.disabled = !characteristics.every(f => state.selected[f.name]);
        }
    }
});
