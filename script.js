// Hämta och visa produkter – endast om product-list finns
async function fetchProducts(category = "") {
    const container = document.getElementById("product-list");
    if (!container) return; // Undvik fel på sidor utan produktlista

    try {
        const response = await fetch(`get_products.php?category=${category}`);
        const products = await response.json();
        container.innerHTML = "";

        products.forEach((product) => {
            const article = document.createElement("article");
            const figure = document.createElement("figure");
            const img = document.createElement("img");
            img.src = product.image_url;
            img.alt = product.name;
            img.style.cursor = "pointer";
            img.onclick = () => (window.location.href = `product.html?id=${product.id}`);
            figure.appendChild(img);

            const h3 = document.createElement("h3");
            const a = document.createElement("a");
            a.href = `product.html?id=${product.id}`;
            a.textContent = product.name;
            a.classList.add("product-link");
            h3.appendChild(a);

            const pPrice = document.createElement("p");
            const strong = document.createElement("strong");
            strong.textContent = `${product.price} kr`;
            pPrice.appendChild(strong);

            const button = document.createElement("button");
            button.textContent = "Lägg i kundvagn";
            button.classList.add("add-to-cart-btn");
            button.onclick = () => addToCart(product.id, product.name, product.image_url, product.price);

            article.appendChild(figure);
            article.appendChild(h3);
            article.appendChild(pPrice);
            article.appendChild(button);
            container.appendChild(article);
        });
    } catch (error) {
        console.error("Fel vid hämtning av produkter:", error);
    }
}

// Lägg till i kundvagn
async function addToCart(id, name, image, price) {
    await fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, name, image, price })
    });
    updateCartUI();
}

// Hämta kundvagn
async function fetchCart() {
    const response = await fetch("get_cart.php");
    const data = await response.json();
    return data.cart;
}

// Ta bort från kundvagn
async function removeFromCart(id) {
    console.log("Försöker ta bort produkt med ID:", id);

    const res = await fetch("remove_from_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id })
    });

    const result = await res.json();
    console.log("Svar från remove_from_cart.php:", result);

    if (!result.success) {
        alert("Det gick inte att ta bort produkten: " + (result.message || "okänt fel"));
        return;
    }

    updateCartUI();
}

// Uppdatera kundvagns-UI
async function updateCartUI() {
    const cartContainer = document.getElementById("cart-items");
    const cartCount = document.getElementById("cart-count");
    if (!cartContainer || !cartCount) return;

    const cart = await fetchCart();
    cartContainer.innerHTML = "";

    if (cart.length === 0) {
        cartContainer.innerHTML = "<p>Kundvagnen är tom.</p>";
        cartCount.textContent = "(0)";
    } else {
        cart.forEach((item) => {
            const cartItem = document.createElement("div");
            cartItem.classList.add("cart-item");

            const span = document.createElement("span");
            span.textContent = `${item.name} (${item.quantity}st) - ${item.price * item.quantity} kr`;

            const removeBtn = document.createElement("button");
            removeBtn.textContent = "❌";
            removeBtn.onclick = () => removeFromCart(item.id);

            cartItem.appendChild(span);
            cartItem.appendChild(removeBtn);
            cartContainer.appendChild(cartItem);
        });

        const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = `(${totalCount})`;
    }
}

// Filter
function applyFilter() {
    const selectedCategories = Array.from(document.querySelectorAll(".category-filter:checked")).map(
        (input) => input.value
    );
    const category = selectedCategories.length > 0 ? selectedCategories.join(",") : "";
    fetchProducts(category);
}

// Toggle för kundvagn
function toggleCart() {
    const cartDropdown = document.getElementById("cart-dropdown");
    if (cartDropdown) cartDropdown.classList.toggle("show");
}

// Toggle för filter dropdown
function toggleFilterDropdown() {
    const filterDropdown = document.getElementById("filter-dropdown");
    if (filterDropdown) filterDropdown.classList.toggle("hidden");
}

// Init
document.addEventListener("DOMContentLoaded", () => {
    const cartIcon = document.getElementById("cart-icon");
    const filterToggle = document.getElementById("filter-toggle");
    const categoryFilters = document.querySelectorAll(".category-filter");

    if (cartIcon) cartIcon.addEventListener("click", toggleCart);
    if (filterToggle) filterToggle.addEventListener("click", toggleFilterDropdown);
    categoryFilters.forEach((filter) => filter.addEventListener("change", applyFilter));

    if (document.getElementById("product-list")) fetchProducts();  // Ladda produkter endast på butikssidan
    updateCartUI();  // Gäller alla sidor med kundvagn
});
