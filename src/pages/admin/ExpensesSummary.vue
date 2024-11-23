<template>
  <q-page padding>
    <q-card flat bordered class="q-pa-md">
      <q-card-section class="text-h6">
        Expenses Summary
      </q-card-section>

      <q-card-section>
        <q-btn label="Fetch Expenses Summary" @click="fetchExpensesSummary" color="primary" class="q-mb-md" />
      </q-card-section>

      <q-card-section>
        <div class="row q-col-gutter-md">
          <q-card flat bordered class="col-xs-12 col-sm-4">
            <q-card-section class="text-center">
              <q-icon name="calendar-today" color="primary" size="2rem" />
              <div class="text-h5 q-mt-sm">Today's Expenses</div>
              <div class="text-subtitle1">${{ daily }}</div>
            </q-card-section>
          </q-card>

          <q-card flat bordered class="col-xs-12 col-sm-4">
            <q-card-section class="text-center">
              <q-icon name="calendar-month" color="primary" size="2rem" />
              <div class="text-h5 q-mt-sm">This Month's Expenses</div>
              <div class="text-subtitle1">${{ monthly }}</div>
            </q-card-section>
          </q-card>

          <q-card flat bordered class="col-xs-12 col-sm-4">
            <q-card-section class="text-center">
              <q-icon name="calendar-year" color="primary" size="2rem" />
              <div class="text-h5 q-mt-sm">This Year's Expenses</div>
              <div class="text-subtitle1">${{ yearly }}</div>
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
      daily: 0,
      monthly: 0,
      yearly: 0,
    };
  },
  methods: {
    async fetchExpensesSummary() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/expensesController/getExpensesSummaryController.php');
        this.daily = response.data.today;
        this.monthly = response.data.month;
        this.yearly = response.data.year;
      } catch (error) {
        console.error('Error fetching expenses summary:', error);
      }
    },
  },
};
</script>

<style scoped>
.q-card-section {
  padding: 16px;
}
</style>
