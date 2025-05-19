// ==========================
// Produkter och kundvagn
// ==========================
  
  async function addToCart(id, name, image, price) {
    await fetch("add_to_cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, name, image, price })
    });
    updateCartUI();
  }
  
  async function fetchCart() {
    const response = await fetch("get_cart.php");
    const data = await response.json();
    return data.cart;
  }
  
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

  function toggleCart() {
    const cartDropdown = document.getElementById("cart-dropdown");
    if (cartDropdown) cartDropdown.classList.toggle("show");
  }

  function closeCart() {
    const cartDropdown = document.getElementById("cart-dropdown");
    if (cartDropdown) cartDropdown.classList.remove("show");
  }
  
  // ==========================
  // Filtrering
  // ==========================
  
  
  function toggleFilterDropdown() {
    const filterDropdown = document.getElementById("filter-dropdown");
    if (filterDropdown) filterDropdown.classList.toggle("hidden");
  }
  

  // ==========================
  // DOM Ready
  // ==========================

  document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.getElementById("hamburger");
    const nav = document.querySelector("header nav");
  
    if (hamburger && nav) {
      hamburger.addEventListener("click", () => {
        nav.classList.toggle("show");
      });
    }

    const cartIcon = document.getElementById("cart-icon");
    const filterToggle = document.getElementById("filter-toggle");
  
    if (cartIcon) cartIcon.addEventListener("click", toggleCart);
    if (filterToggle) filterToggle.addEventListener("click", toggleFilterDropdown);
  
    updateCartUI();

  
  
  // ======================
  // Registreringsformulär
  // ======================
  
    const registerForm = document.getElementById("register-form");
    const feedback = document.getElementById("feedback");
  
    if (registerForm && feedback) {
      registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
  
        try {
          const response = await fetch("register.php", {
            method: "POST",
            body: formData
          });
  
          const result = await response.json();
          if (result.success) {
            feedback.style.color = "green";
            feedback.textContent = "Registrering lyckades! Du kan nu logga in.";
            registerForm.reset();
          } else {
            feedback.style.color = "red";
            feedback.textContent = result.message || "Registrering misslyckades.";
          }
        } catch (error) {
          feedback.style.color = "red";
          feedback.textContent = "Kunde inte kontakta servern.";
          console.error(error);
        }
      });
    }

  });
  