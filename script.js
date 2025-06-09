/**
* script.js (9 - Gesällprov)
* Hanterar interaktiv funktionalitet för butikssidan:
* – Kommunikation med PHP-backend för kundvagn (lägg till, ta bort, hämta)
* – Dynamisk uppdatering av kundvagnsgränssnitt
* – Menyfunktioner och DOM-händelser (mobilmeny, filtermeny, modal)
*/

// ==========================
// Produkter och kundvagn
// ==========================

/**
* Funktion som lägger till en produkt i kundvagnen via POST till servern.
*
* @param {string} id - Produktens ID
* @param {string} name - Produktens namn
* @param {string} image - URL till produktens bild
* @param {number} price - Produktens pris
*/
async function addToCart(id, name, image, price) {
  await fetch("add_to_cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, name, image, price })
  });
  updateCartUI();
}

/**
* Funktion som hämtar den aktuella kundvagnen från servern som JSON.
*
* @returns {Promise<Array>} En array med produkter i kundvagnen
*/
async function fetchCart() {
  const response = await fetch("get_cart.php");
  const data = await response.json();
  return data.cart;
}

/**
* Funktion som tar bort en produkt från kundvagnen.
*
* @param {string} id - Produktens ID som ska tas bort
*/
async function removeFromCart(id) {
  const res = await fetch("remove_from_cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  });

  const result = await res.json();
  if (!result.success) {
    alert("Det gick inte att ta bort produkten: " + (result.message || "okänt fel"));
  } else {
    updateCartUI();
  }
}

/**
* Funktion som uppdaterar visningen av kundvagnen i användargränssnittet.
* Visar innehåll eller "kundvagnen är tom", samt uppdaterar räkningen.
*/
async function updateCartUI() {
  const cartContainer = document.getElementById("cart-items");
  const cartCount = document.getElementById("cart-count");
  if (!cartContainer || !cartCount) return;

  const cart = await fetchCart();
  cartContainer.innerHTML = "";

  if (cart.length === 0) {
    const emptyMsg = document.createElement("p");
    emptyMsg.textContent = "Kundvagnen är tom.";
    cartContainer.appendChild(emptyMsg);    
    cartCount.textContent = "(0)";
  } else {
    cart.forEach((item) => {
      const cartItem = document.createElement("section");
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

// ==========================
// Menyfunktioner
// ==========================

/**
* funktion som växlar synlighet för kundvagnsmenyn.
*/
function toggleCart() {
  const cartDropdown = document.getElementById("cart-dropdown");
  if (cartDropdown) cartDropdown.classList.toggle("show");
}

/**
* Funktion som stänger kundvagnsmenyn.
*/
function closeCart() {
  const cartDropdown = document.getElementById("cart-dropdown");
  if (cartDropdown) cartDropdown.classList.remove("show");
}

/**
* Funktion som växlar synlighet för filtermenyn.
*/
function toggleFilterDropdown() {
  const filterDropdown = document.getElementById("filter-dropdown");
  if (filterDropdown) filterDropdown.classList.toggle("hidden");
}

// ==========================
// DOM Ready – körs när sidan laddats
// ==========================
document.addEventListener("DOMContentLoaded", () => {
  // Hamburger-meny för mobil
  const hamburger = document.getElementById("hamburger");
  const nav = document.querySelector("header nav");
  if (hamburger && nav) {
    hamburger.addEventListener("click", () => {
      nav.classList.toggle("show");
    });
  }

  // Ikoner och knappar
  const cartIcon = document.getElementById("cart-icon");
  const filterToggle = document.getElementById("filter-toggle");

  if (cartIcon) cartIcon.addEventListener("click", toggleCart);
  if (filterToggle) filterToggle.addEventListener("click", toggleFilterDropdown);

  // Uppdatera kundvagnen vid laddning
  updateCartUI();

  // "Lägg till i kundvagn"-knappar
  document.querySelectorAll("[data-product-id]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-product-id");
      const name = btn.getAttribute("data-product-name");
      const image = btn.getAttribute("data-product-image");
      const price = btn.getAttribute("data-product-price");
      addToCart(id, name, image, parseFloat(price));
    });
  });

  // Scrolla till modal (om den finns)
  const modal = document.querySelector(".modal");
  if (modal) {
    modal.scrollIntoView({ behavior: "smooth" });
  }

  // Kundvagnsmeny-knappar
  document.querySelectorAll("[data-action='close-cart']").forEach(btn =>
    btn.addEventListener("click", closeCart)
  );

  document.querySelectorAll("[data-action='go-checkout']").forEach(btn =>
    btn.addEventListener("click", () => {
      window.location.href = 'checkout.php';
    })
  );
});
