<template>
  <q-page class="q-pa-md">
    <div class="text-h6 q-mb-md">Order Report</div>
    <q-card flat bordered class="q-pa-md">
      <q-card-section class="row items-center">
        <q-avatar size="lg" class="q-mr-md">
          <img src="https://cdn.quasar.dev/img/avatar.png" alt="Order Icon">
        </q-avatar>
        <div>
          <div class="text-h6">Order Report Overview</div>
          <div>Admin: <span class="text-primary">admin</span></div>
          <div>Date: ( {{ startDate }} - {{ endDate }} )</div>
        </div>
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div class="row q-col-gutter-md items-center">
          <q-input
            v-model="startDate"
            mask="####-##-##"
            label="Start Date"
            outlined
            dense
            type="date"
            class="col-12"
          />
          <q-input
            v-model="endDate"
            mask="####-##-##"
            label="End Date"
            outlined
            dense
            type="date"
            class="col-12"
          />
        </div>
      </q-card-section>

      <q-card-section class="q-mt-md">
        <div class="row q-col-gutter-md">
          <q-card flat bordered class="col-12">
            <q-card-section>
              <div class="text-subtitle1">Delivered</div>
              <div class="text-h6">{{ delivered }} <q-icon name="shopping_cart" /></div>
            </q-card-section>
          </q-card>
          <q-card flat bordered class="col-12">
            <q-card-section>
              <div class="text-subtitle1">Returned</div>
              <div class="text-h6">{{ returned }} <q-icon name="undo" /></div>
            </q-card-section>
          </q-card>
          <q-card flat bordered class="col-12">
            <q-card-section>
              <div class="text-subtitle1">Canceled</div>
              <div class="text-h6">{{ canceled }} <q-icon name="cancel" /></div>
            </q-card-section>
          </q-card>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
  data() {
    return {
      delivered: 0,
      returned: 0,
      canceled: 0,
      startDate: '',
      endDate: '',
    }
  },
  methods: {
    async responseDelivered() {
      try {
        if (!this.startDate || !this.endDate) {
          console.warn('Please select both start and end dates');
          return;
        }

        const response = await axios.get("http://localhost/raj-express/backend/controller/admincontroller/orderController/orderDeliveredController.php",
        {
            params: {
              startDate: this.startDate,
              endDate: this.endDate,
            },
          }
        );

        this.delivered = response.data.orderWithStatus.length;
      } catch (error) {
        console.error('Failed to fetch reports:', error);
      }
    },
    async responseReturned() {
      try {
        const startDate = this.startDate ? this.startDate : '';
        const endDate = this.endDate ? this.endDate : '';

        const response = await axios.get("http://localhost/raj-express/backend/controller/adminController/earningController/orderReturnedController.php",           {
            params: {
              startDate: this.startDate,
              endDate: this.endDate,
            },
          });
        
        this.returned = response.data.total.total;
      } catch (error) {
        console.error('Failed to fetch reports:', error);
      }
    },
    async responseCanceled() {
      try {
        const startDate = this.startDate ? this.startDate : null;
        const endDate = this.endDate ? this.endDate : null;

        const response = await axios.get("http://localhost/raj-express/backend/controller/adminController/earningController/orderCancelledController.php",           {
            params: {
              startDate: this.startDate,
              endDate: this.endDate,
            },
          });

        this.canceled = response.data.total.total;
      } catch (error) {
        console.error('Failed to fetch reports:', error);
      }
    },
  },
  watch: {
    startDate() {
      if (this.endDate) {
        this.responseDelivered();
        this.responseReturned();
        this.responseCanceled();
      }
    },
    endDate() {
      if (this.startDate) {
        this.responseDelivered();
        this.responseReturned();
        this.responseCanceled();
      }
    },
  },
  created() {
    // Initial load of reports when the component is created
    this.responseDelivered();
    this.responseReturned();
    this.responseCanceled();
  }
}
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
