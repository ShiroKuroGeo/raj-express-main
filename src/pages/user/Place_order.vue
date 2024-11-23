<template>
  <q-page padding>
    <div class="checkout-container">
      <div class="header">
        <q-btn flat round color="black" icon="arrow_back" @click="goBack" />
        <h2 class="text-h5 q-ml-md">Checkout</h2>
      </div>

      <q-card flat bordered class="delivery-address q-mt-md">
        <q-card-section>
          <div class="row justify-between items-center">
            <div class="text-subtitle1">Deliver to</div>
            <q-btn flat color="orange" label="Add Address" @click="addAddress" />
          </div>
          <div v-if="!addresses" class="text-red q-mt-sm">No info added</div>
          <div v-else>
            <div class="text-capitalize">
              <q-radio
                v-for="(a, index) in addresses"
                :key="index"
                :label="a.landmark"
                :val="a.address_id"
                v-model="selectedAddress"
              /> <br>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <q-card v-for="item in cartItems" :key="item.cart_id" flat bordered class="order-summary q-mt-md">
        <q-card-section horizontal>
          <q-img :src="`http://localhost/raj-express/backend/uploads/${item.product_image}`"
            style="width: 100px; height: 100px; object-fit: cover;" />
          <q-card-section>
            <div class="text-subtitle1">{{ item.product_name }} <small>x</small>{{ item.quantity }}</div>

            <div class="text-h6">₱ {{ parseInt(item.product_price) }}</div>
            <div class="" v-for="ex in item.extra">Adds On - {{ ex.name }} - (P {{ ex.price }}.00)</div>
            <div class="">Total Amount: {{ parseInt(item.product_price * item.quantity) + totalExtraItems(item)}}</div>
          </q-card-section>
        </q-card-section>
      </q-card>

      <div class="payment-method q-mt-lg">
        <div class="text-subtitle1 q-mb-sm">Payment Method:</div>
        <q-option-group v-model="paymentMethod" :options="paymentOptions" color="red" />
      </div>

      <div class="payment-details q-mt-lg">
        <div class="text-subtitle1 q-mb-sm">Payment Details:</div>
        <div class="between">
          <small>Adds On Total</small>
          <span class="float-end">₱ {{ extrasTotal }}</span>
        </div>
        <div class="between">
          <small>Product Total</small>
          <span class="float-end">₱ {{ totalProduct.toFixed(2) }}</span>
        </div>
        <div class="between">
          <small>Shippning Total</small>
          <span class="float-end">₱ {{ getDeliveryAddress == 'Sudtongan' ? 20 : 60  }}</span>
        </div>
      </div>

      <div class="total-payment q-mt-lg">
        <div class="row justify-between text-subtitle1">
          <span>Total Payment: </span>
          <span class="text-primary">₱ {{ extrasTotal + totalProduct + getDeliveryShippingFee }}</span>
        </div>
      </div>

      <q-btn v-if="isNightTime" disabled class="full-width q-mt-lg" color="red" label="We're Closed" @click="placeOrder" />
      <q-btn v-else class="full-width q-mt-lg" color="red" label="Place Order" @click="placeOrder" />
    </div>
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
  data() {
    return {
      address: '',
      isNightTime: false,
      paymentMethod: 'cash',
      paymentOptions: [{ label: 'Cash on Delivery', value: 'cash' }, { label: 'Online Payment', value: 'online' }],
      cartItems: [],
      addresses: [],
      addons: [],
      extras: [],
      selectedAddress: 0,
    }
  },
  methods: {
    goBack() {
      this.$router.back();
    },
    selectedIdForAddress(id) {
      this.selectedAddress = id;
    },
    checkTimeRange() {
      const now = new Date();
      const phTime = new Date(
        now.toLocaleString("en-US", { timeZone: "Asia/Manila" })
      );

      const hour = phTime.getHours();
      this.isNightTime = hour >= 20 || hour < 6;
    },
    async fetchCartItems() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost/raj-express/backend/controller/cartController/get.php', {
          headers: {
            'Authorization': token
          }
        });
        const data = response.data;

        this.cartItems = data.cartItems.map(item => ({
          ...item,
          extra: JSON.parse(item.extra)
        }));

        this.extras =  data.cartItems.map(item => ({
          extra: JSON.parse(item.extra)
        }));;
      } catch (error) {
        console.error('Error fetching cart items:', error);
      }
    },
    async fetchAddresses() {
      try {
        const token = localStorage.getItem('token');

        const response = await axios.get('http://localhost/raj-express/backend/controller/addressController/getAllAddress.php', {
          headers: {
            'Authorization': token
          }
        });

        const data = response.data;
        this.addresses = data.addressItems;

      } catch (error) {
        console.error('Error fetching addresses:', error)
      }
    },
    async setNotitication(result) {
      const content = 'Order has been sent to Order Management as Pending! Please check this out.';
      const token = 'TO ADMIN';
      const id = 1;

      const notificationData = {
        user_id: id,
        customer_ref: token,
        content: content
      };
      
      try {
        const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/notificationController/setNotificationController.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(notificationData)
        });

        if (response.status == 200) {
          if (result == 1) {
            this.$q.notify({
              type: 'positive',
              message: 'Order placed successfully!',
              position: 'top',
              timeout: 2000
            })
            this.$router.push('/home');
          } else {
            window.location.href = result;
          }
        } else {
          this.$q.notify({
            type: 'negative',
            message: `Error: ${response.status}`,
            position: 'top'
          })
        }

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: `Error: ${error.message}`,
          position: 'top'
        })
      }
    },
    async placeOrder() {
      try {

        if (this.selectedAddress != 0) {
          if(this.paymentMethod === 'cash'){

            const token = localStorage.getItem('token');
            let orderData = [];

            const totalOrders = this.cartItems.length;

            const shippingFeeTotal = this.getDeliveryAddress === 'Sudtongan' ? 20 : 60;

            const shippingFeePerOrder = (shippingFeeTotal / totalOrders).toFixed(2);

            this.cartItems.forEach(item => {
              const productTotal = parseFloat(item.product_price) * parseInt(item.quantity);

              const addonsTotal = item.extra.reduce((total, addon) => {
                return total + (parseFloat(addon.price) * (addon.quantity || 1));
              }, 0);

              const paymentTotal = (productTotal + addonsTotal + parseFloat(shippingFeePerOrder)).toFixed(2);

              orderData.push({
                user_id: token,
                cart_id: item.cart_id,
                product_id: item.product_id,
                address_id: this.selectedAddress,
                order_qty: item.quantity,
                totalExtra: this.extras.length > 0 ? this.extras : null,
                extra: item.extra.length > 0 ? item.extra : null,
                payment_method: this.paymentMethod,
                payment_total: paymentTotal,
                payment_status: this.paymentMethod === 'cash' ? 'pending' : 'pending on gcash',
              });
            });

            const response = await fetch("http://localhost/raj-express/backend/controller/orderController/add.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ orders: orderData })
            });

            if (!response.ok) {
              const errorMessage = await response.text();
              throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
            }

            const result = await response.json();

            if (result && result.success) {
              const home = 1;
              this.setNotitication(home);
            } else {
              throw new Error(result.error || "Failed to add product to cart");
            }
          }
          else if(this.paymentMethod === 'online'){
            const token = localStorage.getItem('token');
            let orderData = [];

            const totalOrders = this.cartItems.length;

            const shippingFeeTotal = this.getDeliveryAddress === 'Sudtongan' ? 20 : 60;

            const shippingFeePerOrder = (shippingFeeTotal / totalOrders).toFixed(2);

            this.cartItems.forEach(item => {
              const productTotal = parseFloat(item.product_price) * parseInt(item.quantity);

              const addonsTotal = item.extra.reduce((total, addon) => {
                return total + (parseFloat(addon.price) * (addon.quantity || 1));
              }, 0);

              const paymentTotal = (productTotal + addonsTotal + parseFloat(shippingFeePerOrder)).toFixed(2);
              const onlineTotal = (productTotal + addonsTotal) * 100;
              // const onlineTotal = productTotal;

              item.extra.forEach(addon => {
                orderData.push({
                  forExtraloop: 1,
                  user_id: token,
                  cart_id: item.cart_id,
                  product_id: item.product_id,
                  address_id: this.selectedAddress,
                  order_qty: addon.quantity,
                  totalExtra: null,
                  extra: null,
                  payment_method: this.paymentMethod,
                  payment_total: parseInt(paymentTotal),
                  payment_status: this.paymentMethod === 'cash' ? 'pending' : 'pending on gcash',
                  onlineTotal: onlineTotal,
                  productTotal: addon.price * 100,
                  addonTotal: 0,
                  description: addon.name,
                  name: addon.name,
                  quantity: addon.quantity,
                });
              });

              orderData.push({
                forExtraloop: 0, // Main product loop
                user_id: token,
                cart_id: item.cart_id,
                product_id: item.product_id,
                address_id: this.selectedAddress,
                order_qty: item.quantity,
                totalExtra: item.extra.length > 0 ? item.extra : null, // Pass add-ons if available
                extra: item.extra.length > 0 ? item.extra : null,
                payment_method: this.paymentMethod,
                payment_total: parseInt(paymentTotal), // Total for GCash/database
                payment_status: this.paymentMethod === 'cash' ? 'pending' : 'pending on gcash',
                onlineTotal: onlineTotal,
                productTotal: parseInt(item.product_price) * 100,
                addonTotal: item.extra.reduce((total, addon) => total + parseInt(addon.price || 0), 0), // Sum of add-ons
                description: item.extra.length > 0 ? 'Has extras' : 'No extras',
                name: item.product_name,
                quantity: item.quantity,
              });

            });
            orderData.push({
              forExtraloop: 1,
              user_id: token,
              cart_id: 1,
              product_id: null,
              address_id: null,
              order_qty: 1,
              totalExtra: null,
              extra: null,
              payment_method: null,
              payment_total: null,
              payment_status: null,
              onlineTotal: null,
              productTotal: shippingFeeTotal * 100,
              addonTotal: 0,
              description: 'Shipping Fee',
              name: 'Shipping Fee',
              quantity: 1,
            });

            try {
                const response = await fetch("http://localhost/raj-express/backend/controller/onlinePaymentController/gcashPaymentController.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ orders: orderData })
                });

                if (!response.ok) {
                    const errorMessage = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
                }

                const result = await response.json();

                if (result && result.success) {
                    // alert('You are redirected to PayMongo for online payment!');
                    this.setNotitication(result.success);
                } else {
                    throw new Error(result.error || "Failed to add product to cart or missing checkout URL");
                }

            } catch (error) {
                console.error("Payment process error:", error);
                alert("An error occurred during the payment process. Please try again.");
            }
          }
          else{
            this.$q.notify({
              type: 'warning',
              message: 'No payment method selected!',
              position: 'top'
            })
          }

        } else {
          console.log('No Selected Address.');
        }

      } catch (error) {
        console.log('Error in ' + error.message);
      }

    },
    addAddress() {
      this.$router.push('/address');
    },
    totalExtraItems(item) {
      return item.extra.reduce((total, extra) => {
        const extraPrice = parseFloat(extra.price) * (parseInt(extra.quantity) || 1);
        return total + extraPrice;
      }, 0);
    },
  },
  computed: {
    totalProduct(){
      return this.cartItems.reduce((total, item) => {
        const productTotal = parseInt(item.product_price) * parseInt(item.quantity);

        return total + productTotal;
      },0)
    },

    extrasTotal() {
      return this.extras.reduce((grandTotal, item) => {
          const itemTotal = item.extra.reduce((total, extra) => {
              return total + (parseFloat(extra.price) * parseInt(extra.quantity));
          }, 0);
          return grandTotal + itemTotal;
      }, 0);
    },

    getDeliveryShippingFee() {
      const selected = this.addresses.find(address => address.address_id === this.selectedAddress);
      const shippingFee = selected ? selected.deliveryAddress : 'No address selected';
      return shippingFee == 'Sudtongan' ? 20 : 60;
    },

    getDeliveryAddress() {
      const selected = this.addresses.find(address => address.address_id === this.selectedAddress);
      return selected ? selected.deliveryAddress : 'No address selected';
    }

  },
  created() {
    this.fetchAddresses();
    this.fetchCartItems();
    // this.checkTimeRange();
    // setInterval(this.checkTimeRange, 60000);
  }

}
</script>

<style scoped>
.checkout-container {
  max-width: 600px;
  margin: 0 auto;
}

.header {
  display: flex;
  align-items: center;
}

.delivery-address,
.order-summary {
  background-color: #fff;
}

.between {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
}

.between small {
    font-size: 1rem;
    color: #333;
}

.between span {
    font-size: 1rem;
    font-weight: bold;
    color: #2c3e50;
}

</style>
