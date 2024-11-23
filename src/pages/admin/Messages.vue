<template>
  <q-page class="flex flex-center">
    <div class="chat-container">
      <q-header class="bg-pink-1 text-black" v-for="user in contacts">
        <q-toolbar>
          <q-btn flat round dense icon="arrow_back" @click="back" />
          <q-toolbar-title>
            {{ user.last_name }}, {{ user.first_name }}
            <div class="text-caption">{{ user.contact_number }}</div>
          </q-toolbar-title>
        </q-toolbar>
      </q-header>

      <div class="chat-area">
        <div v-for="message in messages" :key="message.msg_id" :class="message.receiver_id == currentId ? 'd-flex padding-top-bottom message-right' : 'd-flex padding-top-bottom message-left'">
          <div class="message">
            <q-card flat bordered class="message-content">
              <q-card-section>{{ message.content }}</q-card-section>
            </q-card>
          </div>
        </div>
      </div>

      <div class="message-input q-pa-sm">
        <q-input v-model="message" outlined dense placeholder="Type a message" bg-color="white" >
          <template v-slot:after>
            <q-btn round flat icon="send" @click="sendMessage" />
          </template>
        </q-input>
      </div>
    </div>
  </q-page>
</template>
<!-- <template>
  <q-page padding>
    <div class="row">
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">Messages</div>
            <q-input
              outlined
              v-model="searchQuery"
              placeholder="Search"
              dense
              prepend-inner-icon="search"
            />
          </q-card-section>
          <q-list>
            <q-item
              v-for="contact in filteredContacts"
              :key="contact.id"
              clickable
              @click="selectContact(contact)"
            >
              <q-item-section avatar>
                <q-avatar>
                  <q-icon name="account_circle" />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ contact.name }}</q-item-label>
                <q-item-label caption>{{ contact.phone }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <div class="col-12">
        
        <div class="chat-area">
          <div v-for="message in messages" :key="message.msg_id" :class="message.sender_id == currentId ? 'd-flex padding-top-bottom message-right' : 'd-flex padding-top-bottom message-left'">
            <div class="message">
              <q-card flat bordered class="message-content">
                <q-card-section>{{ message.content }}</q-card-section>
              </q-card>
            </div>
          </div>
        </div>

        <div class="message-input q-pa-sm">
          <q-input v-model="message" outlined dense placeholder="Type a message" bg-color="white" >
            <template v-slot:after>
              <q-btn round flat icon="send" @click="sendMessage" />
            </template>
          </q-input>
        </div>
      </div>
    </div>
  </q-page>
</template> -->

<script>
import axios from "axios";

export default {
  data() {
    return {
      contacts: [],
      currentId: 0,
      message: '',
      messages: []
    };
  },
  methods: {
    async fetchUserInfo() {
      try{
        const token = this.$route.params.id;
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/messageController/getUserInfoController.php',{
          headers:{
            "Authorization": token,
          }
        });
        this.contacts = response.data.users;
        this.currentId = token;
      }catch(error){
        console.log('Error in ' + error);
      }
    },
    async fetchMessages(){
      try{
        const token = this.$route.params.id;
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/messageController/getUserMessageController.php',{
          headers:{
            "Authorization": token,
          }
        });
        this.messages = response.data.message;
      }catch(error){
        console.log('Error in ' + error);
      }
    },
    async setNotitication(){
      const content = 'Good Day, Raj Express send you a message!';
      const params = this.$route.params.id;
      const notificationData = {
        user_id: params,
        customer_ref: null,
        content: content
      };

      try{
        const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/notificationController/setNotificationController.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(notificationData)
        });

        if(response.status == 200){
          alert('Notification Sent!');
        }else{
          alert('The status is : '+response.status);
        }

      }catch(error){
        console.log('Error in '+ error);
      }

    },
    async sendMessage (){
      const token = localStorage.getItem('token');
      const params = this.$route.params.id;
      
      const messageData = {
        sender_id: token,
        receiver_id: params,
        content: this.message
      };

      const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/messageController/sendController.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(messageData)
      });

      if (!response.ok) {
        const errorMessage = await response.text();
        throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
      }

      const result = await response.json();

      if (result && result.success) {
        this.fetchMessages();
        this.setNotitication();
        this.message = '';
      } else {
        throw new Error(result.error || "Message not sent!");
      }
    },
    loadMessage(){
      setInterval(() => {
        this.fetchMessages();
      }, 1000);
    },
    back(){
      this.$router.back();
    },
  },
  created() {
    this.fetchUserInfo();
    this.fetchMessages();
    this.loadMessage();
  },
};
</script>

<style scoped>
.chat-container {
  width: 100%;
  height: 100vh;
  display: flex;
  flex-direction: column;
  border: 1px solid #ddd;
}

.d-flex{
  display: flex;
}

.message-left {
  justify-content: flex-start;
}

.message-right {
  justify-content: flex-end;
}

.message-content {
  width: 100%;
}

.chat-area {
  flex-grow: 1;
  overflow-y: auto;
  background-color: white;
}

.message-input {
  background-color: #f0f0f0;
}

.padding-top-bottom{
  padding-top: 5px;
  padding-bottom: 5px;
}

.q-toolbar {
  min-height: 70px;
}

.q-toolbar-title {
  font-size: 1.2rem;
  font-weight: bold;
}
</style>
