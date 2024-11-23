<template>
  <q-layout>
    <q-page-container>
      <q-page>
        <div class="text-center text-h6 q-mt-md" :class="{ 'dark-text': $q.dark.isActive }">My Cart</div>
        <div v-if="cartItems.length === 0" class="text-center">
          <h3 style="padding: 50px;" :class="{ 'dark-text': $q.dark.isActive }">Your cart is empty!</h3>
          <p :class="{ 'dark-text': $q.dark.isActive }">Please add some items from the menu</p>
          <q-btn
            label="Explore Menu"
            color="red"
            @click="goToHome"
            style="width: 200px; margin-top: 20px"
          />
        </div>
        <div v-else>
          <q-list class="q-card my-card">
            <q-item
              v-for="(item, index) in cartItems"
              :key="index"
              clickable
              @click="goToCartAdd(item.cart_id)"
              class="product-card q-mb-md"
              :class="{ 'dark-card': $q.dark.isActive }"
            >
              <div class="row full-width items-center">
                <div class="col-4">
                  <q-img
                    :src="'http://localhost/raj-express/backend/uploads/' + item.product_image"
                    :ratio="1"
                    class="rounded-borders"
                  />
                </div>
                <div class="col-8 q-pl-md">
                  <div class="row items-center justify-between">
                    <div class="text-h6" :class="{ 'dark-text': $q.dark.isActive }">{{ item.product_name }}</div>
                    <q-btn round flat icon="close" color="red" @click.stop="removeCarts(item.cart_id)" />
                  </div>
                  <div class="text-h5" :class="{ 'dark-text': $q.dark.isActive }">₱{{ calculateTotalPrice(item) }}</div>
                  <div class="text-caption" :class="{ 'dark-text': $q.dark.isActive }">
                    <q-rating
                      v-model="item.rating"
                      size="1.5em"
                      color="orange"
                      readonly
                      :max="5"
                    />
                  </div>
                  <div class="row items-center justify-end q-mt-sm">
                    <q-btn round flat icon="remove" :color="$q.dark.isActive ? 'white' : 'black'" @click.stop="decrementQuantity(item)" />
                    <div class="q-px-md" :class="{ 'dark-text': $q.dark.isActive }">{{ item.quantity || 1 }}</div>
                    <q-btn round flat icon="add" color="red" @click.stop="incrementQuantity(item)" />

                  </div>
                </div>
              </div>
            </q-item>
          </q-list>
          <div class="text-center">
            <q-btn
              label="Purchase Cart"
              color="red"
              @click="purchase"
              style="width: 200px; margin-top: 20px"
            />
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<style scoped>
.text-center {
  text-align: center;
}

.dark-text {
  color: white !important;
}

.dark-card {
  background: #1d1d1d !important;
}

.product-card {
  background: white;
  border-radius: 12px;
  padding: 12px;
  margin-bottom: 16px;
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.dark-card:hover {
  box-shadow: 0 2px 12px rgba(255, 255, 255, 0.1);
}

.text-h6 {
  font-weight: bold;
  margin-bottom: 8px;
  transition: color 0.3s ease;
}

.text-h5 {
  font-weight: bold;
  transition: color 0.3s ease;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Add transitions for smooth theme switching */
.product-card,
.text-h6,
.text-h5,
.text-caption,
.q-btn {
  transition: all 0.3s ease;
}

.footer-fixed {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60px;
  box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
  z-index: 999;
}

.q-tab-active {
  border-radius: 5px;
  padding: 10px;
  transform: translateY(-10px);
}
</style>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      darkMode: false,
      cartItems: [],
      selectedOptions: [],
    };
  },
  methods: {
    async fetchCartItems() {
      this.loading = true; // Set loading state to true while fetching

      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost/raj-express/backend/controller/cartController/get.php', {
          headers: {
            'Authorization': token
          }
        });

        // Check if response data is valid and contains cartItems
        if (response.data && response.data.cartItems && response.data.cartItems.length > 0) {
          const data = response.data;
          this.cartItems = data.cartItems.map(item => ({
            ...item,
            quantity: item.quantity || 1,
            rating: item.average_rating || 5.0,
            price: parseFloat(item.price || 0)
          }));
          this.error = false; // Reset any previous error flag
        } else {
          // If no items are found in the cart
          this.error = true;
          this.errorMessage = 'Your cart is empty.';
        }
      } catch (error) {
        // Handle different types of errors
        this.error = true;
        if (error.response) {
          // Server-side error (e.g., 500, 404)
          this.errorMessage = `Error: ${error.response.status} - ${error.response.data.message || 'Something went wrong'}`;
        } else if (error.request) {
          // No response from server (e.g., network issue)
          this.errorMessage = 'Network error: Could not reach the server.';
        } else {
          // Any other error
          this.errorMessage = `An error occurred: ${error.message}`;
        }
      } finally {
        this.loading = false; // Stop loading once the request completes
      }
    },
    calculateTotalPrice(item) {
        let totalPrice = parseFloat(item.product_price) * item.quantity;

        if (item.extra && item.extra !== 'null' && item.extra !== '') {
            try {
                const extraItems = JSON.parse(item.extra);
                extraItems.forEach(extraItem => {
                    if (extraItem.price && extraItem.quantity) {
                        totalPrice += parseFloat(extraItem.price) * extraItem.quantity;
                    }
                });
            } catch (e) {
                console.error('Error parsing extra items:', e);
            }
        }

        return totalPrice.toFixed(2);
    },
    purchase(){
      this.$router.push('/place-order');
    },
    goToCartAdd(cart_id){
      localStorage.setItem('selectedCartId', cart_id);
      this.$router.push('/update-cart/' + cart_id);
    },
    goToHome() {
      this.$router.push('/home');
    },
    async incrementQuantity(item) {
      if (!item.quantity) item.quantity = 1;
      item.quantity++;

      this.onChangeQuantity(item.quantity, item.cart_id);
    },
    async decrementQuantity(item) {
      if (!item.quantity) item.quantity = 1;
      if (item.quantity > 1) {
        item.quantity--;
      }

      this.onChangeQuantity(item.quantity, item.cart_id);
    },
    async onChangeQuantity(quantity, id){
      try {
        const datas = {
          quantity: quantity,
          id: id
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/cartController/updateQuantity.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(datas)
        });

        const result = await response.json();
        console.log(result);

        if (result) {
          console.log('Quantity Updated!');
          // $q.notify({
          //   color: 'positive',
          //   message: result.success
          // })
        } else {
          throw new Error(result.error || "Failed to add product to cart");
        }
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async removeCarts(id) {
      try {
        const datas = {
          cart_id: id
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/cartController/removeCart.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(datas)
        });

        const result = await response.json();

        if (result) {
          // Use Quasar notify instead of alert
          this.$q.notify({
            type: 'positive',
            message: 'Item removed from cart!',
            position: 'top',
            timeout: 2000
          });

          // Update the cart items
          this.cartItems = this.cartItems.filter(item => item.cart_id !== id);
          await this.fetchCartItems();
        } else {
          throw new Error(result.error || "Failed to remove item from cart");
        }
      } catch (error) {
        console.error('Error removing cart item:', error);
        // Show error notification
        this.$q.notify({
          type: 'negative',
          message: 'Failed to remove item from cart',
          position: 'top',
          timeout: 2000
        });
      }
    }
  },
  mounted() {
    this.fetchCartItems();
  }
};
</script>
