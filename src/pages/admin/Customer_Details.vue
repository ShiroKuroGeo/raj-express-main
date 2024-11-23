<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-avatar size="32px" class="q-mr-sm">
        <q-icon name="people" color="primary" />
      </q-avatar>
      <h5 class="q-my-none">Customer Details</h5>
    </div>

    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12">
        <h6 class="q-my-none text-capitalize">Customer ID #{{ user_id }}</h6>
        <q-badge color="grey" class="q-mt-xs text-capitalize">
          Joined at: {{ formattedDate(created_at) }}
        </q-badge>
      </div>
    </div>

    <div class="row q-mb-md">
      <div class="col-12 col-md-8">
        <div class="row items-center q-col-gutter-md">
          <div class="col-grow">
            <h6 class="q-my-none text-capitalize">Status List <q-badge color="primary" rounded>{{ status }}</q-badge></h6>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 text-capitalize">{{ last_name }}, {{ first_name }}</div>
            <q-item>
              <q-item-section avatar>
                <q-avatar>
                  <img :src="`http://localhost/raj-express/backend/uploads/` + profile_img">
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-capitalize" >{{ email }}</q-item-label>
                <q-item-label class="text-capitalize" caption>{{ contact_number }}</q-item-label>
                <q-item-label class="text-capitalize" caption>{{ status }} Status</q-item-label>
              </q-item-section>
            </q-item>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <div class="text-h6">Addresses</div>
            <q-list>
              <q-item v-for="address in addresses" :key="address.address_id">
                <q-item-section avatar>
                  <q-icon name="map" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ address.deliveryAddress }}</q-item-label>
                  <q-item-label caption>{{ address.phoneNumber }}</q-item-label>
                  <q-item-label caption>{{ address.streetNumber }}, {{ address.landmark }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <div class="col-auto">
        <q-btn color="primary" label="Dashboard" icon="dashboard" @click="goToDashboard" />
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

export default defineComponent({
  name: 'CustomerDetails',
  data(){
    return{
      addresses: [],
      user_id: '',
      first_name: '',
      last_name: '',
      address: '',
      contact_number: '',
      email: '',
      profile_img: '',
      status: '',
      created_at: '',
      date_deletion: '',
    }
  },
  methods:{
    async fetchUser(){
      try{
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/userController/userDetails.php',{
          headers:{
            'Authorization': this.$route.params.id
          }
        });
        const data = response.data;
        this.user_id = response.data.user_id;
        this.first_name = response.data.first_name;
        this.last_name = response.data.last_name;
        this.address = response.data.address;
        this.contact_number = response.data.contact_number;
        this.email = response.data.email;
        this.profile_img = response.data.profile_img == null ? "profile.jpg" : response.data.profile_img;
        this.status = response.data.status;
        this.created_at = response.data.created_at;
        this.date_deletion = response.data.date_deletion;
      }catch(error){
        console.log('Error in ' + error);
      }
    },
    async fetchAddressess(){
      try{
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/userController/userAddresses.php',{
          headers:{
            'Authorization': this.$route.params.id
          }
        });
        this.addresses = response.data.addressess;
      }catch(error){
        console.log('Error in ' + error);
      }
    },
    formattedDate(dateString) {
        // Ensure the date string is in a recognizable format
        const date = new Date(dateString.replace(" ", "T"));
        
        // Check if the date is valid
        if (isNaN(date)) {
          return "Invalid date";
        }

        return new Intl.DateTimeFormat("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric"
        }).format(date);

    },
    goToDashboard () {
      this.$router.back();
    }
  },
  created(){
    this.fetchUser();
    this.fetchAddressess();
  }
})
</script>
