<template>
  <q-page padding>
    <div class="q-pa-md">
      <q-header class="bg-red text-white">
        <q-toolbar>
          <q-btn flat round dense icon="arrow_back" color="white" @click="$router.back()" />
          <q-toolbar-title>Add delivery info</q-toolbar-title>
        </q-toolbar>
      </q-header>

      <q-card flat bordered class="q-mt-md">
        <q-card-section>
          <div class="text-h6">Contact Person Info</div>
          <q-input outlined v-model="personName" label="Person Name">
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>
          <q-input outlined v-model="phoneNumber" label="Phone Number">
            <template v-slot:prepend>
              <q-icon name="flag" class="q-mr-sm" />
              +63
            </template>
          </q-input>
        </q-card-section>
      </q-card>

      <q-card flat bordered class="q-mt-md">
        <q-card-section>
          <div class="text-h6">Delivery Address</div>
          <div id="map" style="height: 500px;"></div>
          <q-input v-model="latitude" v-show="hide" label="Latitude" filled class="q-mt-md" />
          <q-input v-model="longitude" v-show="hide" label="Longitude" filled class="q-mt-md" />
          <q-input outlined v-model="landmark" label="Land Mark">
            <template v-slot:prepend>
              <q-icon name="home" />
            </template>
          </q-input>
          <q-input outlined v-model="streetNumber" label="Street Number">
            <template v-slot:prepend>
              <q-icon name="home" />
            </template>
          </q-input>
          <q-select
            outlined
            v-model="deliveryAddress"
            :options="addressOptions"
            label="Delivery Address"
            emit-value
            map-options
          >
            <template v-slot:prepend>
              <q-icon name="place" />
            </template>
          </q-select>
        </q-card-section>
      </q-card>

      <div class="q-mt-md">
        <q-btn color="red-5" class="full-width" label="Confirm" @click="submitDeliveryInfo" />
      </div>
    </div>
  </q-page>
</template>

<script>
import { ref } from 'vue'
import { api } from 'boot/axios'
import axios from 'axios'
import L from 'leaflet';

export default {
  name: "CebuMap",
  data() {
    return {
      latitude: '',
      longitude: '',
      deliveryAddress: '',
      streetNumber: '',
      landmark: '',
      personName: '',
      phoneNumber: '',
      addressDatas: [],
      addressOptions: [
        { label: 'Agus ', value: 'Agus' },
        { label: 'Bankal ', value: 'Bankal' },
        { label: 'Marigondon', value: 'Marigondon' },
        { label: 'Pajac ', value: 'Pajac' },
        { label: 'Pusok ', value: 'Pusok' },
        { label: 'Subabasbas', value: 'Subabasbas' },
        { label: 'Sudtongan', value: 'Sudtongan' },
      ],
      hide: false,
      map: null,
      marker: null,
    };
  },
  methods: {
    async submitDeliveryInfo (){
      try{
        const token = localStorage.getItem('token');

        const dataOfAddress = {
          user_id: token,
          latitude: this.latitude,
          longitude: this.longitude,
          deliveryAddress: this.deliveryAddress,
          streetNumber: this.streetNumber,
          landmark: this.landmark,
          personName: this.personName,
          phoneNumber: this.phoneNumber,
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/addressController/addAddress.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(dataOfAddress)
        });

        if (!response.ok) {
          const errorMessage = await response.text();
          throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
        }

        const result = await response.json();

        if (result) {
          this.$router.back();
          // $q.notify({
          //   color: 'positive',
          //   message: result.success
          // })
        }


      }catch(error){
        console.log('Error in ' . error);
      }
    },
    async userInformation (){
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('No auth token found');
        }

        const response = await axios.get('http://localhost/raj-express/backend/controller/profile.php', {
          headers: {
            'Authorization': `${token}`
          }
        });

        const data = response.data;

        console.log(data);
        if (data) {

          this.personName = data.first_name +" "+ data.last_name;
          this.phoneNumber = data.phoneNumber;
        } else {
          throw new Error(data.error || 'Failed to fetch user data');
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
      }
    },
    async checkIfThereIsAlreadyAnAddress (){
      try{
        const token = localStorage.getItem('token');

        const response = await axios.get("http://localhost/raj-express/backend/controller/addressController/getAllAddress.php", {
          headers: {
            Authorization: token
          },
        });

        const data = response.data;

        this.addressDatas = data.addressItems

      }catch(error){
        console.log('Error in ' . error);
      }
    },
    markMap (){
      const basakCoordinates = { lat: 10.3070, lng: 123.9470 };
      const basakBounds = L.latLngBounds([
        [10.2500, 123.9500],
        [10.3500, 124.0500]
      ]);

      this.map = L.map("map", {
        center: basakCoordinates,
        zoom: 15,
        maxBounds: basakBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 14,
        maxZoom: 17,
      });

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: '© OpenStreetMap contributors'
      }).addTo(this.map);

      this.map.on('click', (e) => {
        const { lat, lng } = e.latlng;

        this.latitude = lat;
        this.longitude = lng;

        if (basakBounds.contains([lat, lng])) {
          if (this.marker) {
            this.map.removeLayer(this.marker);
          }

          this.marker = L.marker([lat, lng]).addTo(this.map);
        } else {
          alert("You can only interact within Basak, Lapu-Lapu area.");
        }
      });
    }
  },
  mounted() {
    this.markMap();
    this.userInformation();
    this.checkIfThereIsAlreadyAnAddress();
  },
}
</script>

<style>
@import 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css';
</style>
