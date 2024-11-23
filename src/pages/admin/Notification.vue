<template>
  <q-page>
    <q-header class="red">
      <q-toolbar>
        <q-btn flat dense icon="arrow_back" color="white" @click="goBack" />
        <q-toolbar-title class="text-center text-white">Notification <small>({{ notifications.length }})</small></q-toolbar-title>
      </q-toolbar>
    </q-header>

    <div class="q-pa-md">
      <div class="" v-if="notifications.length > 1">
        <q-card v-for="notification in notifications" :key="notification.id" class="q-mb-md bg-pink-2">
        <q-card-section class="row no-wrap items-center">

          <q-card-section class="q-ml-md col">
            <div class="text-h6">{{ notification.content }}</div>
            <div class="text-caption">{{ notification.created_at }}</div>
          </q-card-section>
        </q-card-section>
      </q-card>
      </div>
      <div class="" v-else>
        No notification
      </div>
    </div>
  </q-page>
</template>

<script>
import axios from 'axios'

export default {
  name: 'NotificationPage',
  data(){
    return{
      notifications: []
    }
  },
  methods:{
    async fetchNotification (){
      try {
        const token = 1;
        const response = await axios.get('http://localhost/raj-express/backend/controller/notificationController/getNotifController.php',{
          headers:{
            'Authorization': token
          }
        });
        this.notifications = response.data.notifs;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    goBack() {
      this.$router.back();
    }
  },
  created(){
    this.fetchNotification();
  }

}
</script>

<style lang="scss" scoped>
.q-toolbar {
  min-height: 56px;
}

.q-toolbar-title {
  font-size: 1.2rem;
  font-weight: 500;
}

.q-btn {
  margin-left: -8px; // Adjust to align the back arrow with the edge
}
.q-layout__section--marginal {
    background-color: red;
    color: white;
}
</style>
