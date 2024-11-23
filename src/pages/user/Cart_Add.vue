<template>
  <q-page padding>
    <q-card class="my-card" :class="{ 'dark-card': $q.dark.isActive }">
      <q-img :src="`http://localhost/raj-express/backend/uploads/${product.image}`">
        <div class="absolute-top-left q-pa-sm">
          <q-btn round color="white" text-color="black" icon="arrow_back" @click="$router.back()" />
        </div>
        <div class="absolute-top-right q-pa-sm">
          <q-btn
          round color="white"
          text-color="red"
          icon="favorite"
          @click="addToWishlist" />
        </div>
      </q-img>

      <q-card-section>
        <div class="text-h6" :class="{ 'dark-text': $q.dark.isActive }">{{ product.name }}</div>
        <div class="text-subtitle1" :class="{ 'dark-text': $q.dark.isActive }">₱ {{ product.price }}</div>
        <div class="text-yellow">
          <q-rating v-model="product.rating" :max="5" size="1em" readonly />
          <span :class="{ 'dark-text': $q.dark.isActive }">{{ product.rating }}</span>
        </div>
        <div class="text-caption" :class="{ 'dark-text': $q.dark.isActive }">{{ product.description }}</div>
        <q-btn-group flat>
          <q-item-section side :class="{ 'dark-text': $q.dark.isActive }">Quantity : </q-item-section>
          <q-btn flat round icon="remove" @click="updateQuantity(-1)" :color="$q.dark.isActive ? 'white' : 'black'" />
          <q-btn flat :class="{ 'dark-text': $q.dark.isActive }">{{ quantity }}</q-btn>
          <q-btn flat round icon="add" @click="updateQuantity(1)" :color="$q.dark.isActive ? 'white' : 'black'" />
        </q-btn-group>
      </q-card-section>

      <q-card-section>
        <div class="text-subtitle2" :class="{ 'dark-text': $q.dark.isActive }">Add ons</div>
        <q-list>
            <q-item v-for="(addon, key) in addons" :key="addon.id" :class="{ 'dark-item': $q.dark.isActive }">
                <q-item-section avatar>
                    <q-checkbox v-model="addon.selected" />
                </q-item-section>
                <q-item-section>{{ addon.ao_name }}</q-item-section>
                <q-item-section side>₱ {{ addon.ao_price }}</q-item-section>
                <q-item-section side>
                    <q-btn-group flat>
                        <q-btn flat round icon="remove" @click="changeQuantity(key, -1)" :disable="!addon.selected || addon.quantity === 0" :color="$q.dark.isActive ? 'white' : 'black'" />
                        <q-btn flat disable :class="{ 'dark-text': $q.dark.isActive }">{{ addon.quantity }}</q-btn>
                        <q-btn flat round icon="add" @click="changeQuantity(key, 1)" :disable="!addon.selected" :color="$q.dark.isActive ? 'white' : 'black'" />
                    </q-btn-group>
                </q-item-section>
            </q-item>
        </q-list>
      </q-card-section>

      <q-card-section>
        <div class="text-subtitle1">Total: ₱ {{ totalPrice }}</div>
      </q-card-section>

      <q-card-actions align="center">
        <q-btn
          :disable="totalPrice < 50"
          color="red"
          :label="totalPrice < 50 ? 'Price must 50 above' : 'Add To Cart'"
          @click="addToCart"
           style="width: 200px; margin-top: 20px"
        />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script>
import { useQuasar } from 'quasar'
import axios from 'axios'

export default {
  setup() {
    const $q = useQuasar()
    return { $q }
  },
  data(){
    return{
      quantity: 1,
      product: [],
      addons: [],
    }
  },
  methods:{
    async fetchProduct (){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/productView.php', {
          headers: {
            'Authorization': this.$route.params.id
          }
        });
        const data = response.success;
        if(!data){
          this.product = {
            id: response.data.product_id,
            name: response.data.product_name,
            price: response.data.product_price,
            rating: 5,
            description: response.data.product_description,
            image: response.data.product_image,
          }
        }else{
          throw new Error(data.error || 'Failed to fetch products data');
        }
        } catch (error) {
          console.error('Error fetching specials:', error)
        }
    },
    updateQuantity (change) {
      this.quantity = Math.max(1, this.quantity + change);
    },
    async addToWishlist () {
      try {
        const token = localStorage.getItem('token');

        const addToCartData = {
          product_id: this.$route.params.id,
          user_id: token,
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/addToWishlist.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(addToCartData)
        });

        if (!response.ok) {
          const errorMessage = await response.text();
          throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
        }

        const result = await response.json();
        console.log(result);

        if (result) {
          this.$q.notify({
            type: 'positive',
            message: 'Successfully added to wishlist',
            position: 'top'
          })
        } else {
          throw new Error(result.error || "Failed to add product to cart");
        }

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: error.message || 'Error adding to wishlist',
          position: 'top'
        })
        console.error("Error adding to cart:", error.message || error);
      }
    },
    async addToCart () {
      try {
        const token = localStorage.getItem('token');

        let selectedAddons = this.addons
          .filter(addon => addon.selected)
          .map(addon => ({
            name: addon.ao_name,
            price: parseFloat(addon.ao_price),
            quantity: addon.quantity
          }));

        const addToCartData = {
          product_id: this.$route.params.id,
          user_id: token,
          quantity: this.quantity,
          extra: selectedAddons,
          pending: 'pending'
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/cartController/add.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(addToCartData)
        });

        if (!response.ok) {
          const errorMessage = await response.text();
          throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
        }

        const result = await response.json();
        console.log(result);

        if (result) {
          this.$q.notify({
            type: 'positive',
            message: 'Successfully added to cart',
            position: 'top'
          })
          this.$router.push('/cart');
        } else {
          throw new Error(result.error || "Failed to add product to cart");
        }

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: error.message || 'Error adding to cart',
          position: 'top'
        })
        console.error("Error adding to cart:", error.message || error);
      }
    },
    goBack(){
      this.$router.back();
    },
    changeQuantity(index, delta) {
        const addon = this.addons[index];

        if (addon.selected) {
            addon.quantity = Math.max(0, addon.quantity + delta);
        }
    },
    async fetchAddsOn(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/addOnController/get.php');
        const data = response.data;

        this.addons = data.addOnsItems.map(item => ({
          ...item,
          selected: false,
          quantity: 1
        }));

      } catch (error) {
        console.error('Error fetching specials:', error);
      }
    }
  },
  created(){
    this.fetchProduct();
    this.fetchAddsOn();
  },
  computed: {
    totalPrice() {
      const productTotal = parseFloat(this.product?.price) * this.quantity;
      const addOnsTotal = this.addons.reduce((sum, addon) => {
        if (addon.selected) {
          return sum + (parseInt(addon.ao_price) * addon.quantity);
        }
        return sum;
      }, 0);
      return productTotal + addOnsTotal;
    }
  }
}
</script>

<style scoped>
.my-card {
  max-width: 400px;
  margin: 20px auto;
}
</style>
