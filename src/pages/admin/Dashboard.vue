<template>
  <q-page padding>
    <h4 class="q-mb-md">Welcome, {{ adminName }}.</h4>
    <p class="text-grey-8">Here’s what’s happening with your R.A.J. business today.</p>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 row q-col-gutter-md">
              <span class="col-8">Order Statistics</span> 
              <q-select class="col-4" v-model="orderStatistics" :options="orderStatisticsOptions" label="Select Statistics" dense emit-value/>
            </div>
            <apexchart type="pie" :options="orderChartOptions" :series="orderSeries" />
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6 row q-col-gutter-md">
              <div class="text-h6">POS Order Statistics</div>
            </div>
            <apexchart type="line" :options="posChartOptions" :series="posOrderSeries" />
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6 row q-col-gutter-md">
              <span class="col-8">Earning Statistics</span> 
            </div>
            <apexchart type="bar" :options="orderEarnedChartOptions" :series="orderEarnedSeries" />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- This below here is already done! -->
    <div class="row q-col-gutter-md q-mt-md">
      <div class="col-sm-12 col-md-6">
        <q-card>
          <q-card-section class="row items-center justify-between">
            <div class="text-h6">Recent Order</div>
            <q-btn flat color="primary" label="View All" />
          </q-card-section>
          <q-table :rows="recentOrder" :columns="recentColumns" row-key="user_id" title="Recent Order (5)" class="text-capitalize" > </q-table>
        </q-card>
      </div>
      <div class="col-sm-12 col-md-6">
        <q-card>
          <q-card-section class="row items-center justify-between">
            <div class="text-h6">Top Selling Product</div>
            <q-btn flat color="primary" label="View All" />
          </q-card-section>
          <q-table :rows="recentOrder" :columns="recentColumns" row-key="user_id" title="Top Selling Products (5)" class="text-capitalize" > </q-table>
        </q-card>
      </div>
      <div class="col-12">
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

      // Order Series
      orderStatistics: 'today',
      orderStatisticsOptions: [
        { label: "This day", value: "today" },
        { label: "This month", value: "month" },
        { label: "This week", value: "week" },
        { label: "This year", value: "year" },
      ],
      orderSeries: [1, 1, 1],
      orderChartOptions: {
        chart: {
          type: "pie",
        },
        labels: ["Pending", "Delivered", "Cancelled"], 
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: 300,
                height: 500,
              },
              legend: {
                position: "bottom",
              },
            },
          },
        ],
      },
      orderEarnedSeries: [
        {
          name: ["Total Earned"],
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
      orderEarnedChartOptions: {
        chart: {
          type: "line",
          toolbar: {
            show: true,
          },
        },
        xaxis: {
          categories: ["Jan", "Feb", "Mar", "Apr", "May", 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          title: {
            text: "Days",
          },
        },
        yaxis: {
          title: {
            text: "Number of Orders",
          },
        },
        stroke: {
          curve: "smooth", 
        },
        title: {
          text: "Order Trends",
          align: "center",
        },
        markers: {
          size: 5,
          hover: {
            size: 7,
          },
        },
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: 300,
                height: 500,
              },
              legend: {
                position: "bottom",
              },
            },
          },
        ],
      },
      
      // POS Order Statistics
      posOrderSeries: [
        {
          name: ["POS Orders"],
          data: [0, 0, 0, 0, 0],
        }
      ],
      posChartOptions: {
        chart: {
          type: "line",
          toolbar: {
            show: true,
          },
        },
        xaxis: {
          categories: ["Today", "Yesterday", "This Week", "This Month", "This Year"],
          title: {
            text: "Days",
          },
        },
        yaxis: {
          title: {
            text: "Number of Orders",
          },
        },
        stroke: {
          curve: "smooth", 
        },
        title: {
          text: "Order Trends",
          align: "center",
        },
        markers: {
          size: 5,
          hover: {
            size: 7,
          },
        },
        responsive: [
          {
            breakpoint: 480,
            options: {
              chart: {
                width: 300,
                height: 500,
              },
              legend: {
                position: "bottom",
              },
            },
          },
        ],
      },
      newOrders: 0,
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
    orderStatistics(newValue) {
      this.selectedOption(newValue);
    },
  },
  methods: {
    selectedOption(value) {
      if(value == 'today'){
        this.fetchTodayOrderStatistics();
      } else if(value == 'week'){
        this.fetchWeekOrderStatistics();
      } else if(value == 'month'){
        this.fetchMonthOrderStatistics();
      } else if(value == 'year'){
        this.fetchYearOrderStatistics();
      } else {
        console.log(value);
      }
    },
    async fetchEarnedStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/earned.php');
        const data = response.data;
        if (data.success) {
          this.orderEarnedSeries[0].data = data.monthlySales;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchMonthEarnedStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersEarning/month.php');
        const data = response.data;
        if (data.success) {
          this.orderEarnedSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchWeekEarnedStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersEarning/week.php');
        const data = response.data;
        if (data.success) {
          this.orderEarnedSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchTodayEarnedStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersEarning/today.php');
        const data = response.data;
        if (data.success) {
          this.orderEarnedSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },

    async fetchYearOrderStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersCount/year.php');
        const data = response.data;
        if (data.success) {
          this.orderSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchMonthOrderStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersCount/month.php');
        const data = response.data;
        if (data.success) {
          this.orderSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchWeekOrderStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersCount/week.php');
        const data = response.data;
        if (data.success) {
          this.orderSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchTodayOrderStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/ordersCount/today.php');
        const data = response.data;
        if (data.success) {
          this.orderSeries = data.orderSeries;
        }
      } catch (error) {
        console.log('Error in '+ error);
      }
    },
    async fetchPosOrderStatistics(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/dashboardController/posOrderStatisticsController.php');
        const data = response.data;
        if (data.success) {
          this.posOrderSeries[0].data = [
            data.today,
            data.yesterday,
            data.thisWeek,
            data.thisMonth,
            data.thisYear,
          ];
        }
      } catch (error) {
        console.log('Error in '+ error);
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
    this.selectedOption(this.orderStatistics);
    this.fetchEarnedStatistics();
    this.fetchRecentOrder();
    this.fetchPosOrderStatistics();
    this.fetchTopSelling();
    this.fetchTopCustomer();
    this.newOrderNotification();
    setInterval(() => {
      this.orderNotification();
    }, 10000);
  }
}
</script>
