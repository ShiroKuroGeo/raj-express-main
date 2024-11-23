<template>
    <div>
      <div id="map" style="height: 500px;"></div>
      <q-input v-model="latitude" label="Latitude" filled class="q-mt-md" />
      <q-input v-model="longitude" label="Longitude" filled class="q-mt-md" />
    </div>
  </template>
  
  <script>
  import L from 'leaflet';
  
  export default {
    name: "CebuMap",
    data() {
      return {
        latitude: '',
        longitude: '',
        map: null, 
        marker: null,
      };
    },
    mounted() {
      const cebuCoordinates = { lat: 10.2833322, lng: 123.983329 };
      this.map = L.map("map").setView(cebuCoordinates, 13);
  
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        attribution: '© OpenStreetMap contributors'
      }).addTo(this.map);
  
      this.map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        this.latitude = lat;
        this.longitude = lng; 
  
        // Remove existing marker if present
        if (this.marker) {
          this.map.removeLayer(this.marker);
        }
  
        // Add new marker to the map
        this.marker = L.marker([lat, lng]).addTo(this.map);
      });
    },
  };
  </script>
  
  <style>
  @import 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css';
  </style>
  