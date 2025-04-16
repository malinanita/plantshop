document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const productId = params.get("id");
  
    if (!productId) {
      showError("Ingen produkt angiven.");
      return;
    }
  
    fetch(`get_product.php?id=${productId}`)
      .then((res) => res.json())
      .then((product) => {
        if (product.error) {
          showError(product.error);
        } else {
          renderProduct(product);
        }
      })
      .catch(() => {
        showError("Något gick fel vid hämtning av produkten.");
      });
  });
  
  function showError(message) {
    const container = document.getElementById("product-detail");
    const errorMsg = document.createElement("p");
    errorMsg.textContent = message;
    container.appendChild(errorMsg);
  }
  
  function renderProduct(product) {
    const container = document.getElementById("product-detail");

    const backBtn = document.createElement("a");
    backBtn.textContent = "← Tillbaka till butiken";
    backBtn.href = "index.html";
    backBtn.classList.add("back-button");
  
    const img = document.createElement("img");
    img.src = product.image_url;
    img.alt = product.name;
    img.classList.add("product-image-large");
  
    const name = document.createElement("h1");
    name.textContent = product.name;
  
    const price = document.createElement("p");
    price.textContent = `${product.price} kr`;
    price.classList.add("price");
  
    const description = document.createElement("p");
    description.textContent = product.description;
    description.classList.add("description");
  
    container.appendChild(backBtn);
    container.appendChild(img);
    container.appendChild(name);
    container.appendChild(price);
    container.appendChild(description);
  }
  