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
              <q-item>
                <q-item-section>
                  <q-item-label caption>Order Payment Status {{ currentPaymentStatus }}</q-item-label>
                  <q-item-label class="text-capitalize">
                    <q-select
                      v-model="currentPaymentStatus"
                      class="q-mt-md text-capitalize"
                      label="Status"
                      :options="['pending', 'paid', paymentStatus]"
                      filled
                    />
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Order Status</q-item-label>
                  <q-item-label>
                    <q-select
                      v-model="status"
                      label="Status"
                      :options="['pending', 'confirm', 'processing', 'delivered', 'returned', 'canceled']"
                      filled
                      class="q-mt-md text-capitalize"
                    />
                  </q-item-label>
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
        <div class="text-h6">Products Items {{user_id}}</div>
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
        <q-btn color="primary" label="Save Status" @click="changeStatus" />
        <q-btn color="primary" label="To Delivered" @click="setNotitication(message = 0)" />
        <q-btn color="primary" label="Print" @click="printOrder" />
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
      printData: [],
      products: [],
      extra: [],
      allExtraCombine: [],
      latitude: null,
      longitude: null,
      user_id: null,
      delivered: 0,
      deliveryAddress: null,
      streetNumber: null,
      landmark: null,
      total_payment: null,
      total_amount_product: null,
      grandTotal: null,
      currentPaymentStatus: null,
      map: null,
      payment_status: null,
      status: null,
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
    async setNotitication(message){
      if(message == this.delivered){
        const content = 'Your order is to be delivered!';
        const token = this.$route.params.id;

        const notificationData = {
          user_id: this.user_id,
          customer_ref: token,
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
      }else{
        const content = 'Good Day, your has been '+ this.status;
        const token = this.$route.params.id;

        const notificationData = {
          user_id: this.user_id,
          customer_ref: token,
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
      }


    },
    rateProduct(id) {
      this.$router.push({ name: 'productRating', params: { id } });
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
    async changeStatus(){
      const token = this.$route.params.id;
      const data = [];
      this.orders.forEach(item => {
        data.push({
          product_id: token,
          status: this.status,
          payment_id: item.payment_id,
          payment_status: this.currentPaymentStatus
        });
      });

      if(this.currentPaymentStatus === null || this.status === null){
        alert('Status is null. Please check!');
      }else{
        const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/orderController/changeStatusOrderController.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ data: data})
        });

        if(response.status == 200){
          alert('Status Changed!');
          this.setNotitication();
        }else{
          alert('The status is : '+response.status);
        }
      }
    },
    async fetchOrderDetails() {
      const token = this.$route.params.id;
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/orderController/orderDetailsController.php', {
          headers: { 'Authorization': token }
        });
        this.orders = response.data.orders;
        this.currentPaymentStatus = this.orders[0].payment_status;
        this.user_id = this.orders[0].user_id;

        this.printData = response.data.orders.map(order => {
          return {
            ...order,
            created_at: order.created_at,
            total: this.grandTotal + this.total_amount_product + this.totalShippingItems(this.deliveryAddress)
          };
        });
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
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/orderController/orderDetailsProductController.php', {
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
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/orderController/orderDetailsAddressController.php', {
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
          orderData: JSON.stringify(this.printData)
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
    },
    paymentStatus() {
      return this.orders.length > 0 ? this.orders[0].payment_status : 'No orders available';
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

#map {
  width: 100%;
  height: 400px;
  overflow: hidden;
}
</style>
