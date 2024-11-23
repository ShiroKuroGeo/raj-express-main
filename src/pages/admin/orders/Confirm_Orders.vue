<template>
  <q-page padding>
    <q-table title="All Delivered Orders" :rows="orders" :columns="columns" row-key="id" :loading="loading" >
      <template v-slot:top-right>
        <q-input outlined v-model="search" dense debounce="300" placeholder="Search">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn color="primary" icon="visibility" size="sm" flat dense @click="viewOrder(props.row.cusref)" >
            <q-tooltip>View Order</q-tooltip>
          </q-btn>
          <q-btn color="secondary" icon="print" size="sm" flat dense @click="printOrder(props.row.cusref)" >
            <q-tooltip>Print Order</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const orders = ref([]);
    const loading = ref(false);
    const search = ref('');
    const router = useRouter();
    
    const columns = ref([
      { name: 'id', required: true, label: 'Order ID', align: 'left', field: (row) => row.cusref, sortable: true },
      { name: 'customer_info', label: 'Customer Info', field: (row) => row.addressContactPerson },
      { name: 'order_status', label: 'Order Status', field: (row) => row.status },
      { name: 'payment_status', label: 'Payment Status', field: (row) => row.payment_status },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
    ]);

    const fetchOrders = async () => {
      loading.value = true;
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/orderController/allOrderControllerConfirm.php');
        const filteredOrders = response.data.orders.filter(order => order.payment_status !== 'over-the-counter');
        orders.value = filteredOrders;
      } catch (error) {
        console.error('Failed to fetch orders:' + error);
      } finally {
        loading.value = false;
      }
    };

    const viewOrder = (orderId) => {
      router.push({ name: 'customerOrderDetails', params: { id: orderId } });
    };

    const printOrder = (orderId) => {
      router.push({ name: 'CustomerViewOrders', params: { id: orderId }, query: { print: true } });
    };

    fetchOrders();

    return { orders, columns, loading, search, viewOrder, printOrder };
  }
};
</script>

