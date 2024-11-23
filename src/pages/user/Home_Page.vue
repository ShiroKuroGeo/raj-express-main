<template>
  <q-layout>
    <div v-if="isLoading" :class="{ 'dark-text': $q.dark.isActive }">Loading...</div>
    <div v-else>
      <div v-for="category in categories" :key="category.category_id">
        <h5 style="margin-left: 20px; margin-bottom: 5px">
          <span :class="{ 'dark-text': $q.dark.isActive }" style="font-weight: bold">{{ category.category_name }}</span>
        </h5>
        <div class="category-items" :class="{ 'dark-card': $q.dark.isActive }">
          <div v-if="getProductsByCategory(category.category_id).length === 0"
               :class="{ 'dark-text': $q.dark.isActive }">
            No products available in this category.
          </div>
          <div v-else class="row q-col-gutter-md">
            <div v-for="product in getProductsByCategory(category.category_id)"
                 :key="product.product_id"
                 class="col-xs-6 col-sm-4 col-md-3">
              <q-card class="product-card cursor-pointer"
                      :class="{ 'dark-card': $q.dark.isActive }"
                      @click="goToProductDetails(product.product_id)">
                <q-img :src="`http://localhost/raj-express/backend/uploads/${product.product_image}`"
                       :ratio="1"
                       style="height: 150px;" />
                <q-card-section>
                  <div class="text-h6" :class="{ 'dark-text': $q.dark.isActive }">
                    {{ product.product_name }}
                  </div>
                  <div class="text-h6">
                    <div v-if="ratings.length">
                      <small :class="{ 'dark-text': $q.dark.isActive }">
                        <q-rating :value="calculateAverageRating(product.product_id)"
                                :readonly="true"
                                color="yellow"
                                icon="star"
                                size="10px"
                                :max="5" />
                        ({{ calculateAverageRating(product.product_id) }})
                      </small>
                    </div>
                    <div v-else :class="{ 'dark-text': $q.dark.isActive }">
                      No ratings available for this product.
                    </div>
                  </div>
                  <div class="text-h6" :class="{ 'dark-text': $q.dark.isActive }">
                    {{ product.product_price }}
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-layout>
</template>

<style scoped>
.search {
  width: 50%;
  border: 2px solid var(--q-primary);
  border-radius: 50px;
  background: var(--q-card-bg);
  color: var(--q-primary-text);
  transition: all 0.3s ease;
}

.search-fixed {
  position: fixed;
  top: 0;
  width: 100%;
  padding: 10px;
  z-index: 1000;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
  background: var(--q-card-bg);
  transition: all 0.3s ease;
}

.dark-card {
  background: #1d1d1d;
  color: white;
}

.dark-text {
  color: white !important;
}

.category-items {
  width: 95%;
  min-height: 100px;
  margin-left: 20px;
  border-radius: 30px;
  padding: 20px;
  transition: all 0.3s ease;
}

.product-card {
  width: 100%;
  transition: all 0.3s ease;
  cursor: pointer;
}

.product-card:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Dark mode CSS variables */
:root[data-theme="dark"] {
  --q-primary: #fff;
  --q-primary-text: #fff;
  --q-secondary-text: #aaa;
  --q-card-bg: #1d1d1d;
}

/* Light mode CSS variables */
:root[data-theme="light"] {
  --q-primary: #000;
  --q-primary-text: #000;
  --q-secondary-text: #666;
  --q-card-bg: #fff;
}

/* Add transitions for smooth theme switching */
.q-card,
.q-item,
.text-h6,
.text-subtitle1,
.text-subtitle2,
.text-caption {
  transition: all 0.3s ease;
}
</style>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      categories: [],
      products: [],
      ratings: [],
      isLoading: true,
      search: '',
    };
  },
  mounted() {
    this.fetchAllData();
    this.fetchRateProduct();
    // Initialize theme
    document.documentElement.setAttribute('data-theme', this.$q.dark.isActive ? 'dark' : 'light');
  },
  methods: {
    async fetchAllData() {
      this.isLoading = true;
      try {
        await this.fetchCategories();
        await this.fetchProductsForCategories();
      } catch (error) {
        console.error("Error fetching data:", error);
      } finally {
        this.isLoading = false;
      }
    },
    async fetchCategories() {
      try {
        const response = await fetch('http://localhost/raj-express/backend/controller/pos_categories.php');
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data && Array.isArray(data.categories)) {
          this.categories = data.categories;
        } else {
          console.error('Unexpected response format:', data);
        }
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    },
    async fetchProductsForCategories() {
      try {
        const productPromises = this.categories.map(async (category) => {
          const response = await fetch(`http://localhost/raj-express/backend/controller/category_product.php?category_id=${category.category_id}`);
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          const data = await response.json();
          if (Array.isArray(data.products)) {
            this.products = [...this.products, ...data.products];
          }
        });
        await Promise.all(productPromises);
      } catch (error) {
        console.error("Error fetching products by category:", error);
      }
    },
    goToProductDetails(productId) {
      this.$router.push({ name: 'productDetails', params: { id: productId } });
    },
    async fetchRateProduct() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/ratingController/getRateController.php');
        this.ratings = response.data.ratings;
      } catch (error) {
        console.log('Error in fetchRateProduct:', error);
      }
    },
    calculateAverageRating(productId) {
      const productRatings = this.ratings.filter(rating => rating.product_id === productId);

      if (productRatings.length === 0) return 0;

      const total = productRatings.reduce((sum, rating) => sum + parseInt(rating.feedback), 0);
      return parseInt(total / productRatings.length);
    }
  },
  computed: {
    getProductsByCategory() {
      return (categoryId) => {
        return this.products.filter(product => parseInt(product.category_id) === parseInt(categoryId));
      };
    }
  },
  watch: {
    '$q.dark.isActive'(newValue) {
      document.documentElement.setAttribute('data-theme', newValue ? 'dark' : 'light');
    }
  }
};
</script>

