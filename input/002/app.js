const products = [
    { name: 'Klaviatuur', price: 29.9, category: 'tarvikud' },
    { name: 'Monitor', price: 179.0, category: 'ekraanid' },
];

function formatPrice(price) {
    return `${price.toFixed(2)} eurot`;
}

function renderProducts(items) {
    const list = document.querySelector('#products');
    list.innerHTML = '';

    if (items.length === 0) {
        list.textContent = 'Tooteid ei leitud.';
        return;
    }

    items.forEach((product) => {
        const item = document.createElement('li');
        item.textContent = `${product.name} - ${formatPrice(product.price)} (${product.category})`;
        list.appendChild(item);
    });
}

renderProducts(products);
