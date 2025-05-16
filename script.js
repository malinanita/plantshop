// ==========================
// Produkter och kundvagn
// ==========================

let orderJustPlaced = false;

  
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
    if (orderJustPlaced) return;
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
  
  // ==========================
  // Filtrering
  // ==========================
  
  
  function toggleCart() {
    const cartDropdown = document.getElementById("cart-dropdown");
    if (cartDropdown) cartDropdown.classList.toggle("show");
  }
  
  function toggleFilterDropdown() {
    const filterDropdown = document.getElementById("filter-dropdown");
    if (filterDropdown) filterDropdown.classList.toggle("hidden");
  }
  

  // ==========================
  // DOM Ready
  // ==========================
  
  document.addEventListener("DOMContentLoaded", () => {
    // Allmänt
    const cartIcon = document.getElementById("cart-icon");
    const filterToggle = document.getElementById("filter-toggle");
  
    if (cartIcon) cartIcon.addEventListener("click", toggleCart);
    if (filterToggle) filterToggle.addEventListener("click", toggleFilterDropdown);
  
    updateCartUI();
  
    // ======================
    // Profil – login/logout
    // ======================
    const loginForm = document.getElementById('login-form');
    const logoutSection = document.getElementById('logout-section');
    const welcomeMsg = document.getElementById('welcome-msg');
    const orderHistorySection = document.getElementById('order-history-section');
    const closeModalBtn = document.getElementById("close-modal");
  
    if (closeModalBtn) {
      closeModalBtn.addEventListener("click", () => {
        document.getElementById("order-modal").classList.add("hidden");
      });
    }
  
    if (loginForm) {
      loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
  
        const response = await fetch('login.php', {
          method: 'POST',
          body: formData
        });
  
        const result = await response.json();
        if (result.success) {
          loginForm.classList.add("hidden");
          welcomeMsg.classList.remove("hidden");
          welcomeMsg.textContent = `Välkommen, ${result.name || result.email}!`;
          logoutSection.classList.remove("hidden");
          loadOrderHistory();
        } else {
          alert('Fel e-post eller lösenord.');
        }
      });
    }
  
    // Kontrollera session
    if (loginForm && welcomeMsg && logoutSection) {
      fetch('check_session.php')
        .then(res => res.json())
        .then(data => {
          if (data.loggedIn) {
            loginForm.classList.add("hidden");
            welcomeMsg.classList.remove("hidden");
            welcomeMsg.textContent = `Välkommen, ${data.name || data.email}!`;
            logoutSection.classList.remove("hidden");
            loadOrderHistory();
          }
        });
    }
  
    // ======================
    // Orderhistorik
    // ======================
  
    async function loadOrderHistory() {
      const res = await fetch('orders.php');
      const data = await res.json();
  
      if (data.success && orderHistorySection) {
        orderHistorySection.classList.remove("hidden");
        const list = document.getElementById("order-list");
        list.innerHTML = "";
  
        if (data.orders.length === 0) {
          list.innerHTML = "<li>Du har inga tidigare ordrar.</li>";
          return;
        }
  
        data.orders.forEach(order => {
          const li = document.createElement("li");
          const date = new Date(order.created_at).toLocaleDateString("sv-SE");
          li.textContent = `🧾 Order #${order.order_id} – ${date} – ${order.item_count} produkter – Totalt: ${order.total_price} kr`;
          li.style.cursor = "pointer";
          li.addEventListener("click", () => {
            fetch(`order_details.php?order_id=${order.order_id}`)
              .then(res => res.json())
              .then(detail => {
                if (detail.success) {
                  document.getElementById("order-details").textContent = formatOrderDetails(detail);
                  document.getElementById("order-modal").classList.remove("hidden");
                } else {
                  alert("Kunde inte hämta orderdetaljer: " + detail.message);
                }
              });
          });
          list.appendChild(li);
        });
      }
    }
  
    function formatOrderDetails(detail) {
      const items = detail.items.map(i =>
        `– ${i.name}, ${i.quantity} st á ${i.price_at_purchase} kr`
      ).join('\n');
  
      const date = new Date(detail.order.created_at).toLocaleDateString("sv-SE");
      const total = detail.order.total_price;
  
      return `📦 Order #${detail.order.id}\nDatum: ${date}\n\n${items}\n\nTotalt: ${total} kr`;
    }
  
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

    // ======================
    // Inloggningsformulär (login.html)
    // ======================
    const standaloneLoginForm = document.getElementById("login-form");

    if (standaloneLoginForm && !document.getElementById("welcome-msg")) {
    standaloneLoginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(standaloneLoginForm);

        try {
        const response = await fetch("login.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = "profile.html";
        } else {
            alert(result.message || "Fel e-post eller lösenord.");
        }
        } catch (error) {
        console.error("Något gick fel vid inloggning:", error);
        alert("Ett tekniskt fel uppstod.");
        }
    });
    }

    // ======================
    // Checkout
    // ======================
    const checkoutForm = document.getElementById("checkout-form");
    const cartContainer = document.getElementById("cart-container");

    if (checkoutForm && cartContainer) {
    
        async function displayCart() {
            if (orderJustPlaced) return;

            const cart = await fetchCart();
            cartContainer.innerHTML = "";
            let total = 0;

            if (cart.length === 0) {
            return; // Visa inget om kundvagnen är tom på checkout
            }

            cart.forEach(item => {
            total += item.price * item.quantity;

            const cartItem = document.createElement("div");
            cartItem.classList.add("cart-item");

            const img = document.createElement("img");
            img.src = item.image;
            img.alt = item.name;

            const title = document.createElement("h3");
            title.textContent = item.name;

            const price = document.createElement("p");
            price.textContent = `Pris: ${item.price} kr`;

            const qty = document.createElement("p");
            qty.textContent = `Antal: ${item.quantity}`;

            cartItem.appendChild(img);
            cartItem.appendChild(title);
            cartItem.appendChild(price);
            cartItem.appendChild(qty);
            cartContainer.appendChild(cartItem);
        });

        const totalDiv = document.createElement("h3");
        totalDiv.textContent = `Total: ${total} kr`;
        cartContainer.appendChild(totalDiv);
    }

    displayCart();

    // Hantera formulär
    checkoutForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        orderJustPlaced = true;

        const name = document.getElementById("name").value;
        const address = document.getElementById("address").value;
        const email = document.getElementById("email").value;

        try {
        const response = await fetch("place_order.php", {
            method: "POST",
            headers: {
            "Content-Type": "application/json"
            },
            body: JSON.stringify({ name, address, email })
        });

        const raw = await response.text();
        console.log("Rått svar från servern:", raw);

        let result;
        try {
            result = JSON.parse(raw);
        } catch (jsonErr) {
            console.error("Fel vid tolkning av JSON:", jsonErr);
            alert("Tekniskt fel: Kunde inte tolka svar från servern.");
            return;
        }

        if (result.success) {
            orderJustPlaced = true;
            alert("Tack för ditt köp! 💛🌿 En orderbekräftelse skickas nu till din mail.");
            window.location.href = "profile.html";
            return;
        } else {
            alert("Fel: " + result.message);
        }
        } catch (err) {
        console.error("Ett tekniskt fel uppstod:", err);
        alert("Ett tekniskt fel uppstod. Försök igen senare.");
        }
    });
    }

  });
  