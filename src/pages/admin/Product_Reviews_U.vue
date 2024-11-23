<template>
  <q-page class="q-pa-md">
    <h1 class="text-h4 q-mb-md">🎁 Product Review</h1>

    <div class="row items-center q-mb-md">
      <div class="col-12">
        <h2 class="text-h5">
          Review list
          <q-badge color="secondary" class="q-ml-sm">{{ reviews.length }}</q-badge>
        </h2>
      </div>
      <div class="col-12">
        <q-input
          v-model="searchQuery"
          dense
          outlined
          placeholder="Search by product name"
          class="q-mr-sm"
        >
          <template v-slot:append  @click="searchReviews">
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
    </div>

    <q-table
      :rows="reviews"
      :columns="columns"
      row-key="index"
      :pagination="{ rowsPerPage: 0 }"
    >
      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td key="index" :props="props">
            {{ props.rowIndex + 1 }}
          </q-td>
          <q-td key="productName" :props="props">
            <div class="row items-center">
              <q-avatar size="40px" class="q-mr-sm">
                <img :src="'http://localhost/raj-express/backend/uploads/'+props.row.productImage" :alt="props.row.product_name">
              </q-avatar>
              <span>{{ props.row.product_name }}</span>
            </div>
          </q-td>
          <q-td key="customerInfo" :props="props">
            <div>{{ props.row.last_name }}, {{ props.row.first_name }}</div>
            <div class="text-caption">{{ (props.row.contact_number) }}</div>
          </q-td>
          <q-td key="reviewText" :props="props">
            {{ props.row.fb_description }}
          </q-td>
          <q-td key="rating" :props="props">
            <q-rating
              v-model="props.row.feedback"
              size="1.5em"
              color="yellow"
              icon="star_border"
              icon-selected="star"
              readonly
            />
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<script>

import axios from 'axios';

export default {
  data(){
    return{
      searchQuery: '',
      reviews: [],
      columns: [
        { name: 'index', label: 'id', field: (row) => row.rating_id, align: 'left' },
        { name: 'productName', label: 'Product Name', field: (row) => row.product_name, align: 'left' },
        { name: 'customerInfo', label: 'Customer Info', field: (row) => row.last_name +' '+ row.first_name, align: 'left' },
        { name: 'reviewText', label: 'Review', field: (row) => row.feedback, align: 'left' },
        { name: 'rating', label: 'Rating', field: (row) => row.fb_description, align: 'left' },
      ],
    }
  },
  methods:{
 
    async fetchReviews(){
      try{
         const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/ratingController/getAllController.php');
         this.reviews = response.data.reviews;
         console.log(response.data.reviews);
      }catch(error){
        console.log(error);
      }
    },
    async searchReviews(){
      alert('search');
    }
  },
  created(){
    this.fetchReviews();
  }
};
</script>

<style scoped>
.product-review {
  font-family: Arial, sans-serif;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.review-count {
  background-color: #e0e0e0;
  padding: 2px 6px;
  border-radius: 12px;
}

.search-bar input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.search-button {
  background-color: #ff6b6b;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.review-table {
  width: 100%;
  border-collapse: collapse;
}

.review-table th, .review-table td {
  border: 1px solid #e0e0e0;
  padding: 12px;
  text-align: left;
}

.product-info, .customer-info {
  display: flex;
  align-items: center;
}

.product-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
}

.rating {
  color: #4caf50;
}
</style>
