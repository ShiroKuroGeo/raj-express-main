<template>
  <q-page class="q-pa-md">
    <div class="text-h6 q-mb-md">Earning Report</div>
    <q-card flat bordered class="q-pa-md">
      <q-card-section class="row items-center">
        <q-avatar size="lg" class="q-mr-md">
          <img src="https://cdn.quasar.dev/img/avatar.png" alt="Earning Icon">
        </q-avatar>
        <div>
          <div class="text-h6">Earning Report Overview</div>
          <div>Admin: <span class="text-primary">admin</span></div>
          <div>Date: ( {{ startDate }} - {{ endDate }} )</div>
        </div>
        <q-btn icon="home" flat round class="q-ml-auto" />
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div class="row">
          <q-input
            v-model="startDate"
            mask="####-##-##"
            label="Start Date"
            outlined
            dense
            type="date"
            class="col-12"
            style="margin-top: 10px"
          />
          <q-input
            v-model="endDate"
            mask="####-##-##"
            label="End Date"
            outlined
            dense
            type="date"
            class="col-12"
            style="margin-top: 10px"
          />
          <q-btn color="orange" label="Show" @click="fetchReports" class="col-12" style="margin-top: 10px" />
        </div>
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div class="row">
          <q-card flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1">Total Sold</div>
              <div class="text-h6">{{ total }} P </div>
            </q-card-section>
          </q-card>

        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      total: 0,
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
          'http://localhost/raj-express/backend/controller/adminController/earningController/totalEarningController.php',
          {
            params: {
              startDate: this.startDate,
              endDate: this.endDate,
            },
          }
        );
        this.total = response.data.total.total;
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
</style>
