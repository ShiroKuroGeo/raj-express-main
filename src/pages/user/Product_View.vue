<template>
  <q-page>
    <q-header :class="$q.dark.isActive ? 'bg-dark' : 'bg-red'">
      <q-toolbar>
        <q-btn flat round dense icon="arrow_back" class="text-white" @click="$router.back()" />
        <q-toolbar-title class="text-bold text-white">Today's Special</q-toolbar-title>
      </q-toolbar>
    </q-header>

    <div class="q-pa-md">
      <div :key="specialItems.product_id" class="special-item q-mb-md">
        <q-card flat bordered :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
          <q-item>
            <q-item-section>
              <q-img
                :src="`http://localhost/raj-express/backend/uploads/${specialItems.product_image}`"
                :ratio="16/9"
                style="border-radius: 8px"
              />
              <div class="row items-center q-mt-sm">
                <div class="text-subtitle1 text-weight-bold" :class="{ 'dark-text': $q.dark.isActive }">
                  {{ specialItems.product_name }}
                </div>
                <q-space />
                <q-btn
                  @click="goToAddProduct(specialItems.product_id)"
                  round
                  :color="$q.dark.isActive ? 'grey-9' : 'white'"
                  :text-color="$q.dark.isActive ? 'white' : 'red'"
                  icon="add"
                  size="1em"
                />
              </div>
              <div class="row items-center">
                <q-rating
                  size="1.5em"
                  color="yellow"
                  icon="star"
                  readonly
                />
                <div class="q-ml-sm text-subtitle2" :class="{ 'dark-text': $q.dark.isActive }">
                  ({{ averageRating }})
                </div>
              </div>
              <div class="text-subtitle1 text-weight-bold" :class="{ 'dark-text': $q.dark.isActive }">
                ₱ {{ specialItems.product_price }}
              </div>
              <div class="text-subtitle1 text-weight-normal" :class="{ 'dark-text': $q.dark.isActive }">
                {{ specialItems.product_description }}
              </div>
            </q-item-section>
          </q-item>
        </q-card>
        <div class="product-rating">
          <h5 class="text-bold" :class="{ 'dark-text': $q.dark.isActive }">Product Ratings</h5>
          <div v-if="ratings.length === 0" class="no-ratings" :class="{ 'dark-text': $q.dark.isActive }">
            No ratings available.
          </div>
          <div v-for="(item, index) in ratings" :key="index" class="comment" style="border: 1px solid black; margin: 5px; padding: 5px">
            <strong>User:</strong> {{ item.first_name }} <br>
            <strong>Rate:</strong> {{ item.feedback }} <br>
            <strong>Comment:</strong> {{ item.fb_description }}
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

export default {
  setup () {
    const router = useRouter()
    const route = useRoute()
    const specialItems = ref({
      product_id : '',
      category_id : '',
      product_name : '',
      product_description : '',
      product_price : '',
      product_status : '',
      product_image : '',
      created_at : '',
      updated_at : '',
    })
    const ratings = ref([])
    const averageRating = ref(0)

    const fetchRateProduct = async () => {
      try {
        const id = route.params.id;

        const response = await axios.get("http://localhost/raj-express/backend/controller/ratingController/getRateControllerProductId.php", {
          headers: {
            "Authorization": id,
          },
        });

        ratings.value = response.data.ratings;
        calculateAverageRating();
      } catch (error) {
        console.log('Error in fetchRateProduct:', error);
      }
    };

    const calculateAverageRating = () => {
      const id = parseInt(route.params.id);
      const productRatings = ratings.value.filter(rating => rating.product_id === id);

      console.log('Filtered Ratings:', productRatings);

      if (productRatings.length === 0) return 0;

      const total = productRatings.reduce((sum, rating) => sum + parseInt(rating.feedback), 0);
      averageRating.value = parseInt(total / productRatings.length);
      console.log('Calculated Average Rating:', averageRating.value);
    };

    const addToCart = async () => {
      try {
        const token = localStorage.getItem('token');

        const addToCartData = {
          product_id: route.params.id,
          user_id: token,
          address_payment: '',
          quantity: 1,
          mop: 'Cash on Delivery',
          pending: 'pending'
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/addToCart.php", {
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

        if (result.status === 200) {
          console.log("Added Successfully!");
        } else {
          throw new Error(result.error || "Failed to add product to cart");
        }

      } catch (error) {
        console.error("Error adding to cart:", error.message || error);
      }
    };

    const fetchSpecials = async () => {
      try {

        const response = await axios.get('http://localhost/raj-express/backend/controller/productView.php', {
          headers: {
            'Authorization': route.params.id
          }
        });

        const data = response.success;

        if(!data){
          specialItems.value = {
            product_id: response.data.product_id,
            category_id: response.data.category_id,
            product_name: response.data.product_name,
            product_description: response.data.product_description,
            product_price: response.data.product_price,
            product_status: response.data.product_status,
            product_image: response.data.product_image,
            created_at: response.data.created_at,
            updated_at: response.data.updated_at,
          }
        }else{
          throw new Error(data.error || 'Failed to fetch products data');
        }

      } catch (error) {
        console.error('Error fetching specials:', error)
      }
    }

    const goToAddProduct = (productId) => {
      router.push({ name: 'addProduct', params: { id: productId } });
    };

    onMounted(() => {
      fetchSpecials();
      fetchRateProduct();
    })

    return {
      addToCart,
      goToAddProduct,
      specialItems,
      fetchRateProduct,
      calculateAverageRating,
      ratings,
      averageRating
    }
  }
}
</script>

<style lang="scss" scoped>
.special-item {
  max-width: 400px;
  margin: 0 auto;
}
</style>
