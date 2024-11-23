<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="q-pa-md">
        <!-- Title -->
        <div class="text-center text-h6 q-mt-md">My Order</div>

        <q-tabs v-model="tab" class="q-mt-md q-mb-md" active-color="red" indicator-color="red" align="center" dense>
          <q-tab name="ongoing" label="Ongoing" />
          <q-tab name="history" label="History" />
        </q-tabs>

        <q-page v-if="tab === 'ongoing'">
          <div v-if="ongoingOrders.length">
            <q-list bordered>
              <q-item v-for="order in ongoingOrders" :key="order.id" clickable @click="viewOrderDetails(order.reference)">
                <q-item-section avatar>
                  <q-icon name="receipt_long" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Order #{{ order.id }}</q-item-label>
                  <q-item-label caption>Amount: {{ order.totalPayment }}</q-item-label>
                  <q-item-label caption>Status: {{ order.payment_status }}</q-item-label>
                </q-item-section>
                <q-item-label caption>
                  <q-btn round flat icon="close" color="red" @click.stop="cancelledOrder(order.reference)" />
                </q-item-label>
              </q-item>
            </q-list>
          </div>
          <div v-else class="text-center q-pa-md">
            <q-img src="/statics/empty-box.png" width="120px" height="120px" />
            <div class="text-h6 q-mt-md">No Ongoing Orders</div>
            <div class="text-subtitle2">You haven't placed any ongoing orders</div>
            <q-btn color="red" class="q-mt-md" @click="goToHome">Explore Menu</q-btn>
          </div>
        </q-page>

        <q-page v-if="tab === 'history'">
          <div v-if="historyOrders.length">
            <q-list bordered>
              <q-item v-for="order in historyOrders" :key="order.id" clickable @click="viewOrderDetails(order.reference)">
                <q-item-section avatar>
                  <q-icon name="receipt_long" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Order #{{ order.id }}</q-item-label>
                  <q-item-label caption>Amount: {{ order.payment_total }}</q-item-label>
                </q-item-section>
                <q-btn color="red" class="q-mt-md" @click="set">Rate</q-btn>
              </q-item>
            </q-list>
          </div>
          <div v-else class="text-center q-pa-md">
            <q-img src="/statics/empty-box.png" width="120px" height="120px" />
            <div class="text-h6 q-mt-md">No Order History</div>
            <div class="text-subtitle2">You haven't made any purchases yet</div>
            <q-btn color="red" class="q-mt-md" @click="goToHome">Explore Menu</q-btn>
          </div>
        </q-page>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import axios from 'axios'

export default {
  name: 'OrderPage',
  data() {
    return {
      tab: 'ongoing',
      ongoingOrders: [],
      historyOrders: [],
    }
  },
  methods: {
    async cancelledOrder(id){
      try {
        const orderData = {
          cusref: id
        };

        const response = await axios.post('http://localhost/raj-express/backend/controller/orderController/cancelledOrder.php', orderData, {
          headers: {
            'Content-Type': 'application/json',
          },
        });

        const data = response.data;

        console.log(data);

      } catch (error) {
        console.log('Error in ' + error.message);
      }
    },
    async fetchOnGoingOrders(){
      try {
        const token = localStorage.getItem('token');

        const response = await axios.get('http://localhost/raj-express/backend/controller/orderController/get.php', {
          headers:{
            'Authorization': token
          }
        });

        const data = response.data;

        this.ongoingOrders = data.ordersItems

      } catch (error) {
        console.log('Error in ' + error.message);
      }
    },
    async fetchHistoryOrders(){
      try {
        const token = localStorage.getItem('token');

        const response = await axios.get('http://localhost/raj-express/backend/controller/orderController/history.php', {
          headers:{
            'Authorization': token
          }
        });

        const data = response.data;

        this.historyOrders = data.ordersItems

      } catch (error) {
        console.log('Error in ' + error.message);
      }
    },
    goToHome (){
      this.$router.push('/home');
    },
    viewOrderDetails(id) {
      this.$router.push({ name: 'order-details', params: { id } });
    }
  },
  created() {
    this.fetchOnGoingOrders();
    this.fetchHistoryOrders();
  },
};
</script>

<style scoped>
.q-footer {
  box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
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
