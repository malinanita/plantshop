let cart = JSON.parse(localStorage.getItem("cart")) || []; // Hämta kundvagnen från localStorage

// Funktion för att hämta produkter baserat på kategori
async function fetchProducts(category = "") {
    try {
        const response = await fetch(`get_products.php?category=${category}`);
        const products = await response.json();

        const container = document.getElementById('products-container');
        container.innerHTML = ''; // Rensa gamla produkter

        // Skapa produktkort
        products.forEach(product => {
            const article = document.createElement('article');

            // Skapa figure och img element
            const figure = document.createElement('figure');
            const img = document.createElement('img');
            img.src = product.image_url;
            img.alt = product.name;
            figure.appendChild(img);

            const a = document.createElement('a');
            a.href = `product.html?id=${product.id}`;
            a.textContent = product.name;
            a.classList.add('product-link'); // valfritt för styling
            const h3 = document.createElement('h3');
            h3.appendChild(a);
            article.appendChild(h3);
            img.onclick = () => window.location.href = `product.html?id=${product.id}`;
            img.style.cursor = 'pointer';

            const pPrice = document.createElement('p');
            const strong = document.createElement('strong');
            strong.textContent = `${product.price} kr`;
            pPrice.appendChild(strong);

            // Skapa en knapp för att lägga till i kundvagn
            const button = document.createElement('button');
            button.textContent = 'Lägg i kundvagn';
            button.onclick = () => addToCart(product.id, product.name, product.image_url, product.price);

            // Lägg till alla element i article
            article.appendChild(figure);
            article.appendChild(h3);
            article.appendChild(pPrice);
            article.appendChild(button);

            // Lägg till article i container
            container.appendChild(article);
        });
    } catch (error) {
        console.error("Fel vid hämtning av produkter:", error);
    }
}

// Funktion för att applicera filter på produkter
function applyFilter() {
    const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(input => input.value);
    const category = selectedCategories.length > 0 ? selectedCategories.join(',') : ""; // Filtrera baserat på valda kategorier
    fetchProducts(category);
}

// Funktion för att lägga till produkter i kundvagnen
function addToCart(id, name, image, price) {
    let existingItem = cart.find(item => item.id === id); // Hitta om produkten redan finns

    if (existingItem) {
        existingItem.quantity++; // Om produkten finns, öka mängden
    } else {
        cart.push({ id, name, image, price, quantity: 1 }); // Lägg till ny produkt
    }

    localStorage.setItem("cart", JSON.stringify(cart)); // Spara till localStorage
    updateCartUI(); // Uppdatera UI för kundvagnen
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id); // Ta bort produkt från kundvagnen
    localStorage.setItem("cart", JSON.stringify(cart)); // Spara till localStorage
    updateCartUI(); // Uppdatera UI för kundvagnen
}

// Uppdatera visningen av kundvagnen
function updateCartUI() {
    const cartContainer = document.getElementById("cart-items");
    const cartCount = document.getElementById("cart-count");
    cartContainer.innerHTML = ""; // Rensa gammal kundvagnsinformation

    if (cart.length === 0) {
        cartContainer.innerHTML = "<p>Kundvagnen är tom.</p>"; // Om kundvagnen är tom
    } else {
        cart.forEach(item => {
            const cartItem = document.createElement("div");
            cartItem.classList.add("cart-item");

            const span = document.createElement('span');
            span.textContent = `${item.name} (${item.quantity}st) - ${item.price * item.quantity} kr`;

            const removeButton = document.createElement('button');
            removeButton.textContent = '❌';
            removeButton.onclick = () => removeFromCart(item.id);

            cartItem.appendChild(span);
            cartItem.appendChild(removeButton);

            cartContainer.appendChild(cartItem);
        });
    }
    cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
}

function toggleCart() {
    const cartDropdown = document.getElementById("cart-dropdown");
    cartDropdown.classList.toggle('show'); // Toggla 'show' för att visa/dölja
}

function toggleFilterDropdown() {
    const filterDropdown = document.getElementById("filter-dropdown");
    filterDropdown.classList.toggle('show'); // Toggla 'show' för att visa/dölja filter
}

document.getElementById("cart-icon").addEventListener("click", () => {
    toggleCart(); // Anropa toggleCart
});

document.getElementById("filter-toggle").addEventListener("click", () => {
    document.getElementById("filter-dropdown").classList.toggle("hidden");
});

document.querySelectorAll('.category-filter').forEach(filter => {
    filter.addEventListener('change', applyFilter); // Applicera filter när kategori ändras
});

fetchProducts();
updateCartUI();