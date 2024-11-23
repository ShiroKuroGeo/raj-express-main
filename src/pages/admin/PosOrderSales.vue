<template>
  <q-page class="q-pa-md">
    <div class="text-h6 q-mb-md">Pos Sale Report</div>
    <q-card flat bordered class="q-pa-md">
      <q-card-section class="row items-center">
        <q-avatar size="lg" class="q-mr-md">
          <img src="https://cdn.quasar.dev/img/avatar.png" alt="Sale Icon">
        </q-avatar>
        <div>
          <div class="text-h6">POS Sale Report Overview</div>
          <div>Admin: <span class="text-primary">Admin</span></div>
        </div>
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div class="row items-center">
          <q-input v-model="startDate" mask="####-##-##" label="Start Date" outlined dense type="date" class="col-12 my-2" />
          <q-input v-model="endDate" mask="####-##-##" label="End Date" outlined dense type="date" class="col-12 my-2" />
        </div>
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div>Total Orders: {{ totalOrders }}</div>
        <div>Total Item Qty: {{ totalQty }}</div>
        <div>Total Amount: {{ totalAmount }}</div>
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div class="row q-col-gutter-md">
          <q-btn label="Print" color="primary" />
        </div>
      </q-card-section>

      <!-- <q-table :rows="rows" :columns="columns" row-key="id" class="q-mt-md" flat bordered >
        <template v-slot:top-right>
          <q-input round dense debounce="300" v-model="search" placeholder="Search" />
        </template>
      </q-table> -->
    </q-card>
  </q-page>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      totalOrders: 0,
      totalQty: 0,
      totalAmount: 0,
      startDate: '',
      endDate: '',
    };
  },
  methods: {
    async fetchReports() {
      if (!this.startDate || !this.endDate) {
        console.warn('Please select both start and end dates');
        return;
      }
      try {
        const response = await axios.get(
          'http://localhost/raj-express/backend/controller/adminController/posController/posSalesReportController.php',
          {
            params: {
              startDate: this.startDate,
              endDate: this.endDate,
            },
          }
        );
        this.totalOrders = response.data.totalOrder;
        this.totalQty = response.data.totalQTY;
        this.totalAmount = response.data.totalAmount;
      } catch (error) {
        console.error('Failed to fetch reports:', error);
      }
    },
  },
  watch: {
    startDate() {
      if (this.endDate) this.fetchReports();
    },
    endDate() {
      if (this.startDate) this.fetchReports();
    },
  },
};
</script>

<style scoped>
.q-page {
  max-width: 1000px;
  margin: auto;
}
.q-card-section {
  display: flex;
  align-items: center;
}
.q-card {
  text-align: center;
}

.my-2{
  margin-top: 5px;
  margin-bottom: 5px;
}
</style>
