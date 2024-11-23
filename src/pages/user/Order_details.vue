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
                  <q-item-label class="text-uppercase">{{ customerReference || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Date</q-item-label>
                  <q-item-label>{{ dateCreated ? formatDate(dateCreated) : 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Payment Method</q-item-label>
                  <q-item-label class="text-uppercase">{{ paymentMethods || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
          <div class="col-12 col-md-6">
            <q-list>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Total Amount</q-item-label>
                  <q-item-label>{{ grandTotal + total_amount_product + totalShippingItems(deliveryAddress) }}</q-item-label>
                  <q-item-label><small>AddsOns: {{ grandTotal }}</small></q-item-label>
                  <q-item-label><small>Product Amount: {{ total_amount_product }}</small></q-item-label>
                  <q-item-label><small>Shipping Fee: {{ totalShippingItems(deliveryAddress) }}</small></q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>
      </q-card-section>

      <q-card-section>
        <div class="text-h6">Products Items</div>
        <q-table :rows="products" :columns="productsON" row-key="name" flat bordered>
          <template v-slot:body-cell-product_image="props">
            <q-td :props="props" align="left">
              <img :src="'http://localhost/raj-express/backend/uploads/' + props.row.product_image" alt="Product Image"
                style="width: 50px; height: auto;">
            </q-td>
          </template>
          <template v-slot:body-cell-price="props">
            <q-td :props="props">
              {{ formatCurrency(this.props) }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <!-- Adds On Items Table -->
      <q-card-section>
        <div class="text-h6">Adds On Items</div>
        <q-table :rows="allExtraCombine" :columns="addsOns" row-key="name" flat bordered>
          <template v-slot:body-cell-price="props">
            <q-td :props="props">
              {{ formatCurrency(props.row.price) }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6">Delivery Address</div>
                <div class="text-h6">
                  <small> Address: {{ deliveryAddress }} <br></small>
                  <small> Street: {{ streetNumber }} <br></small>
                  <small> Landmark: {{ landmark }} <br> </small>
                </div> <br>
                <div id="map" style="position: relative; height: 400px; width: 100%;"></div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <!-- Actions -->
      <q-card-actions align="right">
        <q-btn flat color="primary" label="Back" @click="goBack" />
        <q-btn color="red" label="Rate Product" @click="showModal = true"/>
        <q-dialog v-model="showModal">
          <q-card>
            <q-card-section>
              <div class="text-h6">Select Product to Rate</div>
            </q-card-section>
            <q-card-section>
              <div v-for="pro in products" :key="pro.product_id">
                <span>{{ pro.product_name }}</span><br>
                <q-btn flat label="Rate Product" color="primary" @click="rateProduct(pro.product_id)" /> <br><br>
              </div>
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="Close" color="primary" @click="showModal = false" />
            </q-card-actions>
          </q-card>
        </q-dialog>
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script>
import axios from 'axios';
import L from 'leaflet';

export default {
  name: 'ViewDetails',
  data() {
    return {
      orders: [],
      products: [],
      extra: [],
      allExtraCombine: [],
      latitude: null,
      longitude: null,
      showModal: false,
      deliveryAddress: null,
      streetNumber: null,
      landmark: null,
      total_payment: null,
      total_amount_product: null,
      grandTotal: null,
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
  methods: {
    rateProduct(id) {
      this.checkDoneRating(id);
    },
    formatCurrency(value) {
      const number = parseFloat(value);
      return isNaN(number) ? '0.00' : number.toFixed(2);
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
    async fetchOrderDetails() {
      const token = this.$route.params.id;
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/orderController/orderDetailsController.php', {
          headers: { 'Authorization': token }
        });
        this.orders = response.data.orders;

        this.extra = response.data.orders.map(order => {
          let extras = JSON.parse(order.extra);

          const combinedExtras = extras.reduce((acc, item) => {
            if (acc[item.name]) {
              acc[item.name].quantity += item.quantity;
            } else {
              acc[item.name] = { ...item };
            }
            return acc;
          }, {});

          return {
            combinedExtras: Object.values(combinedExtras), 
          };
        });

        this.allExtraCombine = this.extra.reduce((acc, order) => {
          order.combinedExtras.forEach(item => {
            if (acc[item.name]) {
              acc[item.name].quantity += item.quantity;
              acc[item.name].totalPrice += item.price * item.quantity;
            } else {
              acc[item.name] = { 
                name: item.name, 
                price: item.price, 
                quantity: item.quantity, 
                totalPrice: item.price * item.quantity
              };
            }
          });
          return acc;
        }, {});
        this.allExtraCombine = Object.values(this.allExtraCombine);
        this.grandTotal = this.allExtraCombine.reduce((sum, item) => sum + item.totalPrice, 0);

      } catch (error) {
        console.error('Error fetching order details:', error);
      }
    },
    async fetchProduct() {
      try {
        const token = this.$route.params.id;
        const response = await axios.get('http://localhost/raj-express/backend/controller/orderController/orderDetailsProductController.php', {
          headers: { 'Authorization': token }
        });
        this.total_amount_product = response.data.orderDetails.reduce((total, product) => {
          return total + (product.product_price * product.qty);
        }, 0);
        this.products = response.data.orderDetails;
      } catch (error) {
        console.error('Error fetching product details:', error);
      }
    },
    async fetchAddress() {
      try {
        const token = this.$route.params.id;
        const response = await axios.get('http://localhost/raj-express/backend/controller/orderController/orderDetailsAddressController.php', {
          headers: { 'Authorization': token }
        });
        this.latitude = response.data.latitude;
        this.longitude = response.data.longitude;
        this.deliveryAddress = response.data.deliveryAddress;
        this.streetNumber = response.data.streetNumber;
        this.landmark = response.data.landmark;
        this.mapMark();
      } catch (error) {
        console.error('Error fetching product details:', error);
      }
    },
    goBack() {
      this.$router.back();
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
    },
    totalExtraItems(item) {
      return item.reduce((total, extra) => {
        const extraPrice = parseFloat(extra.price) * (parseInt(extra.quantity) || 1);
        return total + extraPrice;
      }, 0);
    },
    totalProductItems(item){
      return item.reduce((total, product) => {
        const productPrice = parseFloat(product.product_price) * (parseInt(product.qty) || 1);
        return total + productPrice;
      }, 0);
    },
    totalShippingItems(deliveryAddress){
      return deliveryAddress == 'Sudtongan' ? 20 : 60;
    },
    getTheSameData(data, specify){
      let result = data.map(order => order.specify);
      return result;
    },
    async checkDoneRating(id){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/ratingController/getRateControllerProductId.php', {
          headers: { 'Authorization': id }
        });
        
        alert('Already rated!');

      } catch (error) {
        
        this.$router.push({ name: 'productRating', params: { id } });
        console.error('Error fetching product details:', error);
      }
    }
  },
  created() {
    this.fetchOrderDetails();
    this.fetchProduct();
    this.fetchAddress();
    if (this.latitude || this.longitude) {
      this.mapMark();
    }
  },

  computed: {
    customerReference() {
      return this.orders.length > 0 ? this.orders[0].customer_reference : 'No orders available';
    },
    dateCreated() {
      return this.orders.length > 0 ? this.orders[0].created_at : 'No orders available';
    },
    paymentMethods() {
      return this.orders.length > 0 ? this.orders[0].payment_method : 'No orders available';
    },
    totalPayment() {
      return this.orders.length > 0 ? this.orders[0].totalPayment : 'No orders available';
    }
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

.border{
  border: 1px solid black
}

#map {
  width: 100%;
  height: 400px;
  overflow: hidden;
}
</style>
