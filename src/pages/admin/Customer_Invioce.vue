<!-- //resibo nis customer sir.
<template>
  <q-page class="flex flex-center">
    <q-card class="invoice-card">
      <q-card-section>
        <h6 class="text-center">Print Invoice</h6>
        <div class="row q-mt-md">
          <q-btn color="orange" label="Proceed, if printer is ready" class="col" @click="printInvoice" />
          <q-btn color="pink" label="Back" class="col q-ml-sm" @click="goBack" />
        </div>
      </q-card-section>

      <q-card-section>
        <h2 class="text-center">R.A.J Food express</h2>
        <q-separator class="q-my-md" />
        <div class="row justify-between items-center">
          <p><strong>Order ID:</strong> {{ orderNumber }}</p>
          <p>{{ orderDateTime }}</p>
        </div>
        <p><strong>Customer Name:</strong> {{ customerName }}</p>
        <p><strong>Phone Number:</strong> {{ phoneNumber }}</p>
        <p><strong>Address:</strong> {{ address }}</p>
        <q-separator class="q-my-md" />
      </q-card-section>

      <q-card-section>
        <q-table
          :rows="items"
          :columns="columns"
          row-key="name"
          hide-bottom
          flat
          bordered
        />
      </q-card-section>

      <q-card-section>
        <q-separator class="q-my-md" />
        <p>Total Items: {{ totalItems }}</p>
        <p>Paid by: {{ paymentMethod }}</p>
        <p class="text-weight-bold">Total Amount: ₱{{ formatCurrency(totalAmount) }}</p>
        <q-separator class="q-my-md" />
        <p class="text-center text-weight-bold">"THANK YOU"</p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'InvoicePage',
  data () {
    return {
      orderNumber: '12345',
      orderDateTime: '12/Jun/2022 12:43 pm',
      customerName: 'John Doe',
      phoneNumber: '123-456-7890',
      address: '123 Main St, City',
      items: [
        { qty: 1, name: 'Bihon-Bam e: With Shrimp', price: 30 },
        { qty: 2, name: 'Mountain due', price: 30 }
      ],
      columns: [
        { name: 'qty', label: 'QTY', field: 'qty', align: 'left' },
        { name: 'name', label: 'Name & Desc', field: 'name', align: 'left' },
        { name: 'price', label: 'Price', field: 'price', align: 'left' }
      ],
      totalItems: 3,
      paymentMethod: 'Cash',
      totalAmount: 90
    }
  },
  methods: {
    printInvoice () {
      window.print()
    },
    goBack () {
      this.$router.go(-1)
    },
    formatCurrency (value) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
      }).format(value)
    }
  }
})
</script>

<style scoped>
.invoice-card {
  width: 100%;
  max-width: 400px;
}

@media print {
  .q-page {
    padding: 0;
  }
  .invoice-card {
    box-shadow: none;
    max-width: none;
  }
}
</style> -->
<template>
  <div class="invoice-container" v-if="orders">
    <div class="invoice-header">
      <p>Print Invoice</p>
    </div>
    <div class="button-container">
      <button @click="printInvoice" class="print-button">Proceed, if printer is ready.</button>
      <button @click="goBack" class="back-button">Back</button>
    </div>
    <div class="divider"></div>

    <div class="invoice-content" id="printArea">
      <h5>R.A.J Food express</h5>
      <div class="divider"></div>
      <div class="order-info">
        <span>Order ID: <span class="text-uppercase"> {{ cusref }}</span></span>
        <span>{{ formatDate( createdAt ) }}</span>
      </div>
      <div class="divider"></div>
      <q-card-section>
        <div class="text-h6">Products Items</div>
        <q-table :rows="products" :columns="productsON" row-key="name" flat bordered>
          <template v-slot:body-cell-price="props">
            <q-td :props="props">
              {{ formatCurrency(this.props) }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>
      <q-card-section>
        <div class="text-h6">AddsOn Items</div>
        <q-table :rows="allExtraCombine" :columns="addsOns" row-key="name" flat bordered>
          <template v-slot:body-cell-price="props">
            <q-td :props="props">
              {{ formatCurrency(props.row.price) }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>
      <div class="divider"></div>
      {{ grandTotal + total_amount_product + totalShippingItems(deliveryAddress) }}
      <div class="order-summary">
        <p>Product Total: {{ total_amount_product }}</p>
        <p>Adds On: {{ grandTotal }}</p>
        <p v-if="paymentStatus === 'over-the-counter'">Shipping Fee:  0</p>
        <p v-else>Shipping Fee:  {{ totalShippingItems(deliveryAddress) }}</p>
        <p class="total-amount" v-if="paymentStatus === 'over-the-counter'">Total Amount: {{ grandTotal + total_amount_product }}</p>
        <p class="total-amount" v-else>Total Amount: {{ grandTotal + total_amount_product + totalShippingItems(deliveryAddress) }}</p>
      </div>
      <div class="divider"></div>
      <p class="thank-you">"THANK YOU"</p>
    </div>
  </div>
  <div v-else>Loading order details...</div>
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
      deliveryAddress: null,
      streetNumber: null,
      landmark: null,
      total_payment: null,
      total_amount_product: null,
      grandTotal: null,
      map: null,
      payment_status: null,
      status: null,
      addsOns: [
        { name: 'name', label: 'Adds On', field: (row) => row.name, align: 'left' },
        { name: 'price', label: 'Price', field: (row) => row.price, align: 'right' },
        { name: 'quantity', label: 'Quantity', field: (row) => row.quantity, align: 'center' }
      ],
      productsON: [
        { name: 'product_name', label: 'Product Name', field: (row) => row.product_name, align: 'center' },
        { name: 'product_price', label: 'Price', field: (row) => row.product_price, align: 'center' },
        { name: 'product_quantity', label: 'Quantity', field: (row) => row.qty, align: 'center' },
      ],
    };
  },
  methods: {
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
          payment_status: this.paymentStatus
        });
      });

      if(this.paymentStatus === null || this.status === null){
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
    this.mapMark();
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
    },
    createdAt() {
      return this.orders.length > 0 ? this.orders[0].created_at : 'No orders available';
    }
  }
};
</script>




<style scoped>
.invoice-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  font-family: Arial, sans-serif;
}

.invoice-header {
  text-align: left;
  margin-bottom: 15px;
}

.invoice-header p {
  margin: 0;
  font-weight: bold;
}

.button-container {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.print-button {
  background-color: #f27c22;
  color: white;
  border: none;
  padding: 10px 15px;
  cursor: pointer;
  font-size: 1em;
  border-radius: 5px;
  margin-left: 20px;
}

.back-button {
  background-color: #ef3a5d;
  color: white;
  border: none;
  padding: 10px 20px;
  cursor: pointer;
  font-size: 1em;
  border-radius: 5px;
  margin-right: 50px;


}

.invoice-content {
  text-align: center;
}

.invoice-content h5 {
  margin-bottom: 10px;
  font-weight: bold;
  font-size: 1.5em;
  margin-top: 10px;

}

.divider {
  border-top: 1px dashed #000;
  margin: 10px 0;
}

.order-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
}

th,
td {
  border: 1px solid #000;
  padding: 5px;
  text-align: left;
}

.order-summary {
  text-align: left;
}

.total-amount {
  font-weight: bold;
}

.thank-you {
  font-weight: bold;
  margin-top: 10px;
}

@media print {

  /* Hide the header and any other elements you don't want to print */
  .invoice-header,
  .button-container,
  /* Assuming the header in the image has a class or id, add it here */
  .header-container {
    display: none !important;
  }

  .invoice-container {
    border: none;
    padding: 0;
    max-width: none;
  }

  .invoice-content {
    padding: 0;
  }

  /* Ensure the print starts from the top of the page */
  @page {
    margin-top: 0;
    margin-bottom: 0;
  }

  body {
    padding-top: 0;
  }
}

/* Add or modify these styles for better table layout */
th {
  background-color: #f2f2f2;
  font-weight: bold;
}

td {
  vertical-align: top;
}
</style>
