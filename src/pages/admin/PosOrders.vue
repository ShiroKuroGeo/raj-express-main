<template>
  <q-page padding>
    <q-table title="Orders History" :rows="orders" :columns="columns" class="text-capitalize" row-key="id" :loading="loading" >
      <template v-slot:top-right>
        <q-input outlined v-model="search" dense debounce="300" placeholder="Search">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </template>
      <template v-slot:body-cell-ProductImage="props">
        <q-td :props="props">
          <img :src="'http://localhost/raj-express/backend/uploads/' + props.row.product_image" alt="Product Image" style="width: 50px; height: 50px;" />
        </q-td>
      </template>
      <template v-slot:body-cell-action="props">
        <q-td :props="props" class="">
          <q-btn color="primary" icon="visibility" size="sm" flat dense @click="viewOrder(props.row.cusref)"></q-btn>
          <q-btn color="secondary" icon="print" size="sm" flat dense @click="printOrder(props.row.pos_id)"></q-btn>
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
      { name: 'id', required: true, label: 'Order ID', align: 'center', field: (row) => row.pos_id, sortable: true },
      { name: 'customer', label: 'Customer', align: 'center', field: () => 'Walk-in Customer'},
      // { name: 'price', label: 'Order Price', align: 'center', field: (row) => row.price },
      { name: 'paymentMethod', label: 'Payment Method', align: 'center', field: (row) => row.status },
      { name: 'date', label: 'Date', align: 'center', field: (row) => row.created_at },
      { name: 'action', label: 'action', align: 'center', field: 'actions' },
    ]);

    const fetchOrders = async () => {
      loading.value = true;
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/posController/getOrderController.php');
        orders.value = response.data.orders;
        console.log(response.data.orders);
      } catch (error) {
        console.error('Failed to fetch orders:' + error);
      } finally {
        loading.value = false;
      }
    };

    const viewOrder = (orderId) => {
      router.push({ name: 'pos-order-details', params: { id: orderId } });
    };

    const printOrder = (orderId) => {
      router.push({ name: 'CustomerViewOrders', params: { id: orderId }, query: { print: true } });
    };

    fetchOrders();

    return { orders, columns, loading, search, viewOrder, printOrder };
  }
};
</script>

