<template>
  <div class="row q-pa-sm bg-orange-1">
    <div class="text-h6">
      📩 Messages <q-badge color="orange" text-color="white"></q-badge>
    </div>
  </div>
  
  <div class="row full-height"></div>
  
  <q-page padding>
    <div class="chat-container row q-pa-md full-height">
      <!-- Left sidebar -->
      <div class="left-sidebar col-4 q-pa-sm">
        <q-card flat class="bg-white">
          <q-card-section class="q-pa-sm">
            <q-item>
              <q-item-section avatar>
                <q-avatar>
                  <img src="https://cdn.quasar.dev/img/avatar.png">
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold text-capitalize">admin</q-item-label>
                <q-item-label caption>+8**********</q-item-label>
              </q-item-section>
            </q-item>
          </q-card-section>
        </q-card>

        <q-input outlined dense v-model="search" placeholder="Search customers..." class="q-mt-md">
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>

        <q-scroll-area class="user-list">
          <q-list separator>
            <q-item v-for="user in users" :key="user.user_id" clickable v-ripple @click="selectUser(user.user_id)" class="q-py-md">
              <q-item-section avatar>
                <q-avatar>
                  <img :src="'http://localhost/raj-express/backend/uploads/'+user.profile_img">
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-medium">{{ user.last_name }}, {{ user.first_name }}</q-item-label>
                <q-item-label caption>{{ (user.contact_number) }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </div>

    </div>
  </q-page>
</template>

<script>
import axios from 'axios'

export default {
  data(){
    return{
      users: [],
      search: '',
    }
  },
  methods:{
    selectUser(id){
      this.$router.push({ name: 'messageSomeone', params: { id } });
    },
    async allUserMessage(){
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/messageController/getAllController.php');
        const data = response.data.users;
        const filteredData = data.filter(item => item.first_name !== "Raj");
        this.users = filteredData;
      } catch (error) {
        console.log('Error in ' + error);
      }
    }
  },
  created(){
    this.allUserMessage();
  }

}
</script>

<style lang="scss" scoped>
.chat-container {
  display: flex;
  flex-direction: column;

  .left-sidebar {
    @media (min-width: 600px) {
      width: 100%;
      margin-bottom: 10px;
    }
  }

  .user-list {
    height: calc(100vh - 450px);
    @media (max-width: 600px) {
      height: 450px;
    }
  }

  .right-chat {
    width: 100%;
    border-left: 1px solid black;

    @media (max-width: 600px) {
      border-left: none;
    }

    .user-info {
      display: flex;
      justify-content: center;
      text-align: center;
    }

    .chat-area {
      flex-grow: 1;
      overflow-y: auto;
    }

    .message-input {
      position: fixed;
      bottom: 0;
      width: 100%;
    }
  }
}

@media (max-width: 600px) {
  .chat-container {
    flex-direction: column;

    .left-sidebar {
      width: 100%;
      margin-bottom: 10px;
    }

    .right-chat {
      width: 100%;
    }

    .chat-area {
      height: 60vh;
    }

    .message-input {
      bottom: 0;
      width: 100%;
    }
  }
}

.q-page {
  background-color: #f5f5f5;
}

.q-card {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
}

.q-item {
  min-height: 60px;
}

.q-scroll-area {
  border-radius: 4px;
  background-color: white;
}

.q-chat-message {
  margin: 8px 0;
}
</style>
