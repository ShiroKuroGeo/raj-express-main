<template>
  <q-page class="flex flex-center">
    <div class="chat-container">
      <!-- Header -->
      <q-header class="bg-red text-white">
        <q-toolbar>
          <q-btn flat round dense icon="arrow_back" @click="$router.back()" />
          <q-toolbar-title>
            RAJ Food Express
            <div class="text-caption">Active now</div>
          </q-toolbar-title>
        </q-toolbar>
      </q-header>

      <div class="chat-area">
        <div v-for="message in messages" :key="message.msg_id" :class="message.sender_id == currentId ? 'd-flex padding-top-bottom message-right' : 'd-flex padding-top-bottom message-left'">
          <div class="message">
            <q-card flat bordered class="message-content">
              <q-card-section class="q-pa-sm">{{ message.content }}</q-card-section>
              <q-card-section class="time-stamp q-pt-none q-px-sm q-pb-xs">
                {{ formatTime(message.created_at) }}
              </q-card-section>
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

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
  data(){
    return{
      message: '',
      currentId: 0,
      messages: [],
      messageResult: '',
    }
  },
  methods:{
    getId(){
      const token = localStorage.getItem('token');
      this.currentId = token;
    },
    async readMessages(){
      const token = localStorage.getItem('token');

      const response = await axios.get("http://localhost/raj-express/backend/controller/messageController/readController.php", {
        headers: {
          "Authorization": token,
        }
      });
      this.messages = response.data.messages;
    },
    back() {
      this.$router.back();
    },
    async sendMessage (){
      const token = localStorage.getItem('token');

      const messageData = {
        sender_id: token,
        receiver_id: 1, // 1 is the id of admin nothing more
        content: this.message
      };

      const response = await fetch("http://localhost/raj-express/backend/controller/messageController/sendController.php", {
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
        this.readMessages();
        this.message = '';
      } else {
        throw new Error(result.error || "Message not sent!");
      }
    },
    loadMessage(){
      setInterval(() => {
        this.readMessages();
      }, 1000);
    },
    formatTime(timestamp) {
      if (!timestamp) return '';
      const date = new Date(timestamp);
      return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    },
  },
  created(){
    this.readMessages();
    this.getId();
    this.loadMessage();
  }
}
</script>

<style scoped>
.chat-container {
  width: 100%;
  height: 80vh;
  display: flex;
  flex-direction: column;
  border: 1px solid #ddd;
  position: relative;
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
  max-width: 80;
  border-radius: 12px;
  background: white;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.chat-area {
  flex-grow: 1;
  overflow-y: auto;
  background-color: white;
  padding-bottom: 1px;
  height: calc(100vh - 130px);
}

.message-input {
  background-color: #f0f0f0;
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  z-index: 1000;

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

.text-caption {
  font-size: 1rem;
}

.time-stamp {
  font-size: 1rem;
  color: #666;
  text-align: right;
}

.q-card-section {
  padding: 8px;
}

.message-right .message-content {
  background: pink;  /* Light green background for sent messages */
}

.message-left .message-content {
  background: pink;    /* White background for received messages */
}
.message-content{
  font-size: 1.2rem;
}
</style>
