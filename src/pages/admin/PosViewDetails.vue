<template>
  <q-page class="q-pa-md">
    <q-card flat bordered>
      <!-- Order Details -->
      <q-card-section>
        <div class="text-h6">Order Details</div>
      </q-card-section>
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-list>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Order Number</q-item-label>
                  <q-item-label class="text-uppercase">{{ cusref || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Date</q-item-label>
                  <q-item-label>{{ first_order_date ? formatDate(first_order_date) : 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Payment Method</q-item-label>
                  <q-item-label class="text-uppercase">{{ payment_method || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
          <div class="col-12 col-md-6">
            <q-list>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Total Items</q-item-label>
                  <q-item-label>{{ calculateTotalItems }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Order Total</q-item-label>
                  <q-item-label>{{ formatCurrency(calculateOrderTotal) }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>
      </q-card-section>

      <!-- Products Items Table -->
      <q-card-section>
        <div class="text-h6">Products Items</div>
        <q-table :rows="products" :columns="productsON" row-key="name" flat bordered>
          <template v-slot:body-cell-product_image="props">
            <q-td :props="props" align="left">
              <img :src="'http://localhost/raj-express/backend/uploads/' + props.row.product_image" alt="Product Image" style="width: 50px; height: auto;">
            </q-td>
          </template>
          <template v-slot:body-cell-price="props">
            <q-td :props="props">
              {{ formatCurrency(this.props) }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat color="primary" label="Back" @click="goBack" />
        <q-btn color="primary" label="Print" @click="printOrder" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>


<script>
import axios from 'axios';
import L from 'leaflet';

export default {
  name: 'PosViewDetails',
  data() {
    return {
      orders: [],
      products: [],
      extra: [],
      cusref: '',
      payment_id: '',
      user_id: '',
      total_orders: '',
      product_names: '',
      status: '',
      first_order_date: '',
      addressContactPerson: '',
      addressContactNumber: '',
      deliveryAddress: '',
      latitude: 0,
      longitude: 0,
      streetNumber: '',
      landmark: '',
      payment_method: '',
      total_payment: '',
      payment_status: '',
      map: null,
      addsOns: [
        { name: 'name', label: 'Adds On', field: (row) => row.name, align: 'left' },
        { name: 'price', label: 'Price', field: (row) => row.price, align: 'right' },
        { name: 'quantity', label: 'Quantity', field: (row) => row.quantity, align: 'center' }
      ],
      productsON: [
        { name: 'product_image', label: 'Product Image', field: (row) => 'http://localhost/raj-express/backend/uploads/' + row.product_image, align: 'left' },
        { name: 'product_name', label: 'Product Name', field: (row) => row.product_name, align: 'center' },
        { name: 'product_price', label: 'Price', field: (row) => row.product_price, align: 'center' },
        { name: 'product_quantity', label: 'Quantity', field: (row) => row.qty, align: 'center' },
      ],
    };
  },
  computed: {
    calculateTotalItems() {
      if (!this.products || this.products.length === 0) return 0;
      return this.products.reduce((sum, product) => {
        return sum + (parseInt(product.qty) || 0);
      }, 0);
    },
    calculateOrderTotal() {
      if (!this.products || this.products.length === 0) return 0;
      return this.products.reduce((total, product) => {
        const price = parseFloat(product.product_price) || 0;
        const quantity = parseInt(product.qty) || 0;
        return total + (price * quantity);
      }, 0);
    }
  },
  methods: {
    formatCurrency(value) {
      if (!value && value !== 0) return 'N/A';
      const number = parseFloat(value);
      return isNaN(number) ? 'N/A' : `₱${number.toFixed(2)}`;
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    },
    async setNotitication(){
      const content = 'Good Day, your has been '+this.status;
      const notificationData = {
        user_id: this.user_id,
        customer_ref: this.cusref,
        content: content
      };

      try{
        const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/notificationController/setNotificationController.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(notificationData)
        });

        if(response.status == 200){
          alert('Notification Sent!');
        }else{
          alert('The status is : '+response.status);
        }

      }catch(error){
        console.log('Error in '+ error);
      }

    },
    async changeStatus(){
      const token = this.$route.params.id;
      const data = {
        product_id: token,
        status: this.status,
        payment_id: this.payment_id,
        payment_status: this.payment_status
      };

      const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/orderController/changeStatusOrderController.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data)
      });

      if(response.status == 200){
        alert('Status Changed!');
        this.setNotitication();
      }else{
        alert('The status is : '+response.status);
      }
    },
    async fetchOrderDetails() {
      const token = this.$route.params.id;
      try {
        console.log('Fetching order details with token:', token);

        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/orderController/orderDetailsController.php', {
          headers: { 'Authorization': token }
        });

        console.log('Raw response:', response);

        if (!response.data) {
          console.error('No data received from server');
          return;
        }

        console.log('Response data:', response.data);

        if (response.data.orders && response.data.orders.length > 0) {
          const order = response.data.orders[0];
          this.orders = response.data.orders;
          this.cusref = order.customer_reference || 'N/A';
          this.first_order_date = order.created_at || new Date().toISOString();
          this.payment_method = order.payment_method || 'N/A';
          console.log('Processed order:', {
            cusref: this.cusref,
            first_order_date: this.first_order_date,
            payment_method: this.payment_method
          });
        } else {
          console.warn('No orders found in response');
        }
      } catch (error) {
        console.error('Error fetching order details:', error);
        if (error.response) {
          console.error('Error response:', error.response.data);
          console.error('Error status:', error.response.status);
        }
      }
    },
    async fetchProduct() {
      const token = this.$route.params.id;
      try {
        console.log('Fetching products with token:', token);

        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/orderController/orderDetailsProductController.php', {
          headers: { 'Authorization': token }
        });

        console.log('Product response:', response.data);

        if (response.data && response.data.orderDetails) {
          this.products = response.data.orderDetails;
          console.log('Processed products:', this.products);
        } else {
          console.warn('No products found in response');
          this.products = [];
        }
      } catch (error) {
        console.error('Error fetching product details:', error);
        this.products = [];
      }
    },
    goBack() {
      this.$router.push({ name: 'orders' });
    },
    printOrder() {
      this.$router.push({
        name: 'pos-print-order',
        params: {
          orderData: JSON.stringify(this.orders)
        }
      });
    },
    mapMark() {
  if (!this.latitude || !this.longitude) {
    console.error('Invalid latitude or longitude values.');
    return;
  }

  if (this.map) {
    this.map.remove();
  }

  const basakCoordinates = { lat: this.latitude, lng: this.longitude };

  this.map = L.map("map", {
    center: basakCoordinates,
    zoom: 14,
    minZoom: 14,
  });

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '© OpenStreetMap contributors'
  }).addTo(this.map);

  L.marker(basakCoordinates).addTo(this.map)
    .bindPopup('Customer Location')
    .openPopup();
}

  },
  mounted() {
    this.fetchOrderDetails();
    this.fetchProduct();
    this.mapMark();
  }
};
</script>


<style scoped>
@import 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css';
@media print {
  .q-page {
    padding: 0 !important;
  }

  .q-card {
    box-shadow: none !important;
  }

  .q-btn {
    display: none !important;
  }
}
#map {
  width: 100%;
  height: 400px;
  overflow: hidden;
}

</style>
