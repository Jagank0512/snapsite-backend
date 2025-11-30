const products = [
  {
    id: 1,
    name: "Wireless Headphones",
    description: "Noise-cancelling over-ear wireless headphones.",
    price: 99.99,
    image: "https://images.unsplash.com/photo-1511367461989-f85a21fda167?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 2,
    name: "Smart Watch",
    description: "Stay connected with this waterproof smart watch.",
    price: 149.99,
    image: "https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 3,
    name: "DSLR Camera",
    description: "Capture stunning photos with this DSLR camera.",
    price: 549.99,
    image: "https://images.unsplash.com/photo-1519183071298-a2962cc0f09b?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 4,
    name: "Gaming Mouse",
    description: "Ergonomic gaming mouse with RGB lighting.",
    price: 39.99,
    image: "https://images.unsplash.com/photo-1584270354949-48e79138b1f0?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 5,
    name: "Bluetooth Speaker",
    description: "Portable Bluetooth speaker with deep bass.",
    price: 59.99,
    image: "https://images.unsplash.com/photo-1486308510493-cb308aaacc29?auto=format&fit=crop&w=400&q=80"
  }
];

let cart = {};

const productsContainer = document.getElementById("products");
const cartCount = document.getElementById("cart-count");
const cartButton = document.getElementById("cart-button");
const cartModal = document.getElementById("cart-modal");
const closeCartButton = document.getElementById("close-cart");
const cartItemsContainer = document.getElementById("cart-items");
const cartTotalDisplay = document.getElementById("cart-total");
const checkoutBtn = document.getElementById("checkout-btn");

function renderProducts() {
  productsContainer.innerHTML = "";
  products.forEach(product => {
    const card = document.createElement("article");
    card.className = "product-card";

    const img = document.createElement("img");
    img.className = "product-image";
    img.src = product.image;
    img.alt = product.name;

    const info = document.createElement("div");
    info.className = "product-info";

    const name = document.createElement("h3");
    name.className = "product-name";
    name.textContent = product.name;

    const desc = document.createElement("p");
    desc.className = "product-description";
    desc.textContent = product.description;

    const price = document.createElement("p");
    price.className = "product-price";
    price.textContent = `$${product.price.toFixed(2)}`;

    const addBtn = document.createElement("button");
    addBtn.className = "add-btn";
    addBtn.textContent = "Add to Cart";
    addBtn.addEventListener("click", () => addToCart(product.id));

    info.appendChild(name);
    info.appendChild(desc);
    info.appendChild(price);
    info.appendChild(addBtn);

    card.appendChild(img);
    card.appendChild(info);

    productsContainer.appendChild(card);
  });
}

function addToCart(productId) {
  if (cart[productId]) {
    cart[productId].quantity += 1;
  } else {
    const product = products.find(p => p.id === productId);
    cart[productId] = {
      ...product,
      quantity: 1
    };
  }
  updateCartCount();
  alert(`Added "${cart[productId].name}" to cart.`);
}

function updateCartCount() {
  const count = Object.values(cart).reduce((acc, item) => acc + item.quantity, 0);
  cartCount.textContent = count;
  checkoutBtn.disabled = count === 0;
}

function openCart() {
  renderCartItems();
  cartModal.classList.remove("hidden");
}

function closeCart() {
  cartModal.classList.add("hidden");
}

function renderCartItems() {
  cartItemsContainer.innerHTML = "";
  const items = Object.values(cart);
  if (items.length === 0) {
    cartItemsContainer.textContent = "Your cart is empty.";
    cartTotalDisplay.textContent = "";
    checkoutBtn.disabled = true;
    return;
  }

  items.forEach(item => {
    const itemDiv = document.createElement("div");
    itemDiv.className = "cart-item";

    const nameSpan = document.createElement("span");
    nameSpan.className = "cart-item-name";
    nameSpan.textContent = item.name;

    const quantityControls = document.createElement("div");
    quantityControls.className = "cart-item-quantity";

    const minusBtn = document.createElement("button");
    minusBtn.textContent = "-";
    minusBtn.title = "Decrease quantity";
    minusBtn.addEventListener("click", () => {
      decreaseQuantity(item.id);
    });

    const qtySpan = document.createElement("span");
    qtySpan.textContent = item.quantity;

    const plusBtn = document.createElement("button");
    plusBtn.textContent = "+";
    plusBtn.title = "Increase quantity";
    plusBtn.addEventListener("click", () => {
      addToCart(item.id);
      renderCartItems();
    });

    quantityControls.appendChild(minusBtn);
    quantityControls.appendChild(qtySpan);
    quantityControls.appendChild(plusBtn);

    const priceSpan = document.createElement("span");
    priceSpan.className = "cart-item-price";
    priceSpan.textContent = `$${(item.price * item.quantity).toFixed(2)}`;

    itemDiv.appendChild(nameSpan);
    itemDiv.appendChild(quantityControls);
    itemDiv.appendChild(priceSpan);

    cartItemsContainer.appendChild(itemDiv);
  });

  let total = items.reduce((acc, item) => acc + item.price * item.quantity, 0);
  cartTotalDisplay.textContent = `Total: $${total.toFixed(2)}`;
  checkoutBtn.disabled = false;
}

function decreaseQuantity(productId) {
  if (!cart[productId]) return;
  if (cart[productId].quantity > 1) {
    cart[productId].quantity -= 1;
  } else {
    delete cart[productId];
  }
  updateCartCount();
  renderCartItems();
}

cartButton.addEventListener("click", (e) => {
  e.preventDefault();
  openCart();
});

closeCartButton.addEventListener("click", () => {
  closeCart();
});

checkoutBtn.addEventListener("click", () => {
  alert("Thank you for your purchase!");
  cart = {};
  updateCartCount();
  closeCart();
});

window.addEventListener("click", (event) => {
  if (event.target === cartModal) {
    closeCart();
  }
});

renderProducts();
updateCartCount();