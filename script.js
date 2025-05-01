// Hämta och visa produkter (med valbar kategori)
async function fetchProducts(category = "") {
    try {
      const response = await fetch(`get_products.php?category=${category}`);
      const products = await response.json();
      const container = document.getElementById('product-list');
      container.innerHTML = '';
  
      products.forEach(product => {
        const article = document.createElement('article');
  
        const figure = document.createElement('figure');
        const img = document.createElement('img');
        img.src = product.image_url;
        img.alt = product.name;
        img.style.cursor = 'pointer';
        img.onclick = () => window.location.href = `product.html?id=${product.id}`;
        figure.appendChild(img);
  
        const h3 = document.createElement('h3');
        const a = document.createElement('a');
        a.href = `product.html?id=${product.id}`;
        a.textContent = product.name;
        a.classList.add('product-link');
        h3.appendChild(a);
  
        const pPrice = document.createElement('p');
        const strong = document.createElement('strong');
        strong.textContent = `${product.price} kr`;
        pPrice.appendChild(strong);
  
        const button = document.createElement('button');
        button.textContent = 'Lägg i kundvagn';
        button.classList.add('add-to-cart-btn');
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
  
  // Lägg till produkt i kundvagnen (server-session)
  async function addToCart(id, name, image, price) {
    await fetch('add_to_cart.php', {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, name, image, price })
    });
    updateCartUI();
  }
  
  // Hämta varukorgen från servern
  async function fetchCart() {
    const response = await fetch('get_cart.php');
    const data = await response.json();
    return data.cart;
  }
  
  // Ta bort produkt från kundvagnen
  async function removeFromCart(id) {
    await fetch('remove_from_cart.php', {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });
    updateCartUI();
  }
  
  // Visa uppdaterad varukorg i sidhuvudet
  async function updateCartUI() {
    const cartContainer = document.getElementById("cart-items");
    const cartCount = document.getElementById("cart-count");
    const cart = await fetchCart();
  
    cartContainer.innerHTML = "";
  
    if (cart.length === 0) {
      cartContainer.innerHTML = "<p>Kundvagnen är tom.</p>";
    } else {
      cart.forEach(item => {
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
    }
  
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalCount;
  }
  
  // Filtrera produkter
  function applyFilter() {
    const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked'))
      .map(input => input.value);
    const category = selectedCategories.length > 0 ? selectedCategories.join(',') : "";
    fetchProducts(category);
  }
  
  // Toggla kundvagn/dropdown
  function toggleCart() {
    const cartDropdown = document.getElementById("cart-dropdown");
    cartDropdown.classList.toggle('show');
  }
  
  // Toggla filtermenyn
  function toggleFilterDropdown() {
    const filterDropdown = document.getElementById("filter-dropdown");
    filterDropdown.classList.toggle("hidden");
  }
  
  // Initiera efter att DOM laddats
  document.addEventListener("DOMContentLoaded", () => {
    const cartIcon = document.getElementById("cart-icon");
    const filterToggle = document.getElementById("filter-toggle");
    const categoryFilters = document.querySelectorAll(".category-filter");
  
    if (cartIcon) {
      cartIcon.addEventListener("click", toggleCart);
    }
  
    if (filterToggle) {
      filterToggle.addEventListener("click", toggleFilterDropdown);
    }
  
    categoryFilters.forEach(filter => {
      filter.addEventListener("change", applyFilter);
    });
  
    fetchProducts();
    updateCartUI();
  });

