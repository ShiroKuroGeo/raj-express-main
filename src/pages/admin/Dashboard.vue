<template>
  <q-page padding>
    <h4 class="q-mb-md">Welcome, {{ adminName }}.</h4>
    <p class="text-grey-8">Here’s what’s happening with your R.A.J. business today.</p>

    <div class="row q-col-gutter-md">
      <div class="col-md-8 col-sm-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Order Statistics</div>
            <q-btn-toggle v-model="orderStatsTimeframe" flat stretch toggle-color="primary" :options="[ { label: 'This Month', value: 'month' }, { label: 'This Week', value: 'week' }, { label: 'Today', value: 'day' } ]" />
            <apexchart type="bar" :options="chartOptions" :series="chartSeries" />
          </q-card-section>
        </q-card>
      </div>
    </div>
    
    <div class="row q-col-gutter-md">
      <div class="col-md-8 col-sm-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">POS Order Statistics</div>
            <q-btn-toggle v-model="orderStatsTimeframe" flat stretch toggle-color="primary" :options="[ { label: 'This Month', value: 'month' }, { label: 'This Week', value: 'week' }, { label: 'Today', value: 'day' } ]" />
            <apexchart type="bar" :options="chartOptionsOrder" :series="chartSeries" />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-md q-mt-md">
      <div class="col-md-8 col-sm-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Earning Statistics</div>
            <q-btn-toggle v-model="earningStatsTimeframe" flat stretch toggle-color="primary" :options="[ { label: 'This Month', value: 'month' }, { label: 'This Week', value: 'week' }, { label: 'Today', value: 'day' } ]" />
            <apexchart type="bar" :options="chartOptionsEarning" :series="chartSeriesEarning" />
          </q-card-section>
        </q-card>
      </div>
      <div class="col-md-4 col-sm-12">
        <q-card>
          <q-card-section class="row items-center justify-between">
            <div class="text-h6">Recent Order</div>
            <q-btn flat color="primary" label="View All" />
          </q-card-section>
          <q-table :rows="recentOrder" :columns="recentColumns" row-key="user_id" title="Recent Order (5)" class="text-capitalize" > </q-table>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-md q-mt-md">
      <div class="col-md-4 col-sm-12">
        <q-card>
          <q-card-section class="row items-center justify-between">
            <div class="text-h6">Top Selling Product</div>
            <q-btn flat color="primary" label="View All" />
          </q-card-section>
          <q-table :rows="recentOrder" :columns="recentColumns" row-key="user_id" title="Top Selling Products (5)" class="text-capitalize" > </q-table>
        </q-card>
      </div>
      <!-- Most Selling and top selling di ba pariha ramana in terms of logic? dili lang nako apilon -->
      <!-- <div class="col-md-4 col-sm-12">
        <q-card>
          <q-card-section class="row items-center justify-between">
            <div class="text-h6">Most Selling Product</div>
            <q-btn flat color="primary" label="View All" />
          </q-card-section>
        </q-card>
      </div> -->
      <div class="col-md-4 col-sm-12">
        <q-card>
          <q-card-section class="row items-center justify-between">
            <div class="text-h6">Top Customer</div>
            <q-btn flat color="primary" label="View All" />
          </q-card-section>
          <q-table :rows="topCustomer" :columns="topCustomerColumns" row-key="user_id" title="Top Selling Products (5)" class="text-capitalize" > </q-table>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import axios from 'axios'
import VueApexCharts from "vue3-apexcharts";

export default {
  name: 'AdminDashboard',
  components: {
    apexchart: VueApexCharts
  },
  data() {
    return {
      adminName: 'Raj Express',
      orderStatsTimeframe: 'month',
      monthTotal: 0,
      newOrders: 0,
      dayTotal: 0,
      weekTotal: 0,
      chartSeries: [],
      chartOptions: {
        chart: {
          id: 'simple-bar',
          toolbar: { show: true }
        },
        xaxis: {
          categories: []
        },
        colors: ['#3B82F6'],
        plotOptions: {
          bar: {
            borderRadius: 4,
            horizontal: true
          }
        }
      },
      earningStatsTimeframe: 'month',
      monthTotalEarning: 0,
      dayTotalEarning: 0,
      weekTotalEarning: 0,
      chartSeriesEarning: [],
      chartOptionsEarning: {
        chart: {
          id: 'simple-bar',
          toolbar: { show: true }
        },
        xaxis: {
          categories: []
        },
        colors: ['#3B82F6'],
        plotOptions: {
          bar: {
            borderRadius: 4,
            horizontal: true
          }
        }
      },
      recentOrder: [],
      recentColumns: [
        { name: "customerRef", label: "Customer Reference", align: "left", field: (row) => row.customer_reference},
        { name: "fullname", label: "Customer Fullname", align: "left", field: (row) => row.last_name +', '+row.first_name},
        { name: "productname", label: "Product Name", align: "left", field: (row) => row.product_name},
        { name: "order_quantity", label: "Order Quantity", align: "left", field: (row) => row.order_qty},
      ],
      topSelling: [],
      topSellingColumns: [
        { name: "productName", label: "Product Name", align: "left", field: (row) => row.product_name},
        { name: "totalSales", label: "Total Sales", align: "left", field: (row) => row.total_sales},
      ],
      topCustomer: [],
      topCustomerColumns: [
        { name: "productName", label: "Customer's Name", align: "left", field: (row) => row.last_name +', '+ row.first_name},
        { name: "totalOrders", label: "Total Orders", align: "left", field: (row) => row.total_orders},
      ],

    }
  },
  watch: {
    orderStatsTimeframe: {
      immediate: true,
      handler() {
        this.updateChartData();
      }
    },
    earningStatsTimeframe: {
      immediate: true,
      handler() {
        this.updateChartDataEarning();
      }
    }
  },
  methods: {
    async getMonthEarningOrderStatistics() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/earnings/orderMonthController.php');
        this.monthTotalEarning = response.data.total.total;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async getDayEarningOrderStatistics() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/earnings/orderDayController.php');
        this.dayTotalEarning = response.data.total.total;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async getWeekEarningOrderStatistics() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/earnings/orderWeekController.php');
        this.weekTotalEarning = response.data.total.total;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async getMonthOrderStatistics() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/orderMonthController.php');
        this.monthTotal = response.data.total.total;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async getDayOrderStatistics() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/orderDayController.php');
        this.dayTotal = response.data.total.total;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async getWeekOrderStatistics() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/orderWeekController.php');
        this.weekTotal = response.data.total.total;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    updateChartData() {
      const todayData = this.dayTotal;
      const weekData = this.weekTotal;
      const monthData = this.monthTotal;

      if (this.orderStatsTimeframe === 'day') {
        this.chartSeries = [{ name: 'Today', data: [todayData] }];
        this.chartOptions.xaxis.categories = ['Today'];
      } else if (this.orderStatsTimeframe === 'week') {
        this.chartSeries = [{ name: 'This Week', data: [weekData] }];
        this.chartOptions.xaxis.categories = ['This Week'];
      } else if (this.orderStatsTimeframe === 'month') {
        this.chartSeries = [{ name: 'This Month', data: [monthData] }];
        this.chartOptions.xaxis.categories = ['This Month'];
      }
    },
    updateChartDataEarning() {
      const todayData = this.dayTotalEarning;
      const weekData = this.weekTotalEarning;
      const monthData = this.monthTotalEarning;
      
      if (this.earningStatsTimeframe === 'day') {
        this.chartSeriesEarning = [{ name: 'Today', data: [todayData] }];
        this.chartOptionsEarning.xaxis.categories = ['Today'];
      } else if (this.earningStatsTimeframe === 'week') {
        this.chartSeriesEarning = [{ name: 'This Week', data: [weekData] }];
        this.chartOptionsEarning.xaxis.categories = ['This Week'];
      } else if (this.earningStatsTimeframe === 'month') {
        this.chartSeriesEarning = [{ name: 'This Month', data: [monthData] }];
        this.chartOptionsEarning.xaxis.categories = ['This Month'];
      }else{
        this.chartSeriesEarning = [{ name: 'This Month', data: [monthData] }];
        this.chartOptionsEarning.xaxis.categories = ['This Month'];
      }
    },
    async fetchRecentOrder(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/recentOrderController.php');
        this.recentOrder = response.data.recentsOrder;
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchTopSelling(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/topSellingController.php');
        this.topSelling = response.data.topSelling;
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchTopCustomer(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/topCustomerController.php');
        this.topCustomer = response.data.topCustomers;
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async newOrderNotification(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/notificationController/newNotification.php');
        this.newOrders = response.data.newOrder;
      } catch (error) {
        console.log(error);
      }
    },
    async orderNotification(){
      if(this.newOrders == 1){
        if(confirm('New order has been arrived!')){
          const response = await axios.post('http://localhost/raj-express/backend/controller/adminController/notificationController/readNotification.php');
          if(response.data.success){
            alert('Read Done!');
          }
        }else{
          alert('Cancel');
        }
      }
    }
  },
  mounted() {
    this.getMonthOrderStatistics();
    this.getDayOrderStatistics();
    this.getWeekOrderStatistics();
    this.fetchTopCustomer();
    this.updateChartData();
    this.updateChartDataEarning();
    this.getMonthEarningOrderStatistics();
    this.getDayEarningOrderStatistics();
    this.getWeekEarningOrderStatistics();
    this.fetchRecentOrder();
    this.fetchTopSelling();
    this.newOrderNotification();
    setInterval(() => {
      this.orderNotification();
    }, 10000);
  }
}
</script>
