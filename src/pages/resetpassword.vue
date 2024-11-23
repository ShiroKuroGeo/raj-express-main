<template>
  <q-layout>
    <q-page-container>
      <q-page class="flex flex-center">
        <div class="q-pa-md q-ma-md" style="max-width: 500px; width: 100%">
          <div class="q-card-section row justify-center">
            <q-img src="/icons/logo.png" alt="Logo" class="circle-img"></q-img>
          </div>

          <div class="q-card-section">
            <q-form class="q-gutter-md" @submit.prevent="changePassword">
              <p style="margin-bottom: 0">Enter Email for forgot password!</p>
              <q-input v-model="newPassword" label="Enter New Password" type="password" dense outlined style="margin-top: 0" ></q-input>
              <q-input v-model="confirmPassword" label="Confirm Password" type="password" dense outlined style="margin-top: 0" ></q-input>
              <div class="column items-center">
                <q-btn type="submit" label="Send" color="primary" class="q-mt-md" ></q-btn>
              </div>
            </q-form>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>


<style scoped>
.circle-img {
  width: 30%;
  height: 30%;
  object-fit: cover; 
  border-radius: 50%;
}
</style>


<script>
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import axios from 'axios';

export default {
  name: 'changePasswordPage',
  data(){
    return{
      newPassword: null,
      confirmPassword: null,
    }
  },
  methods:{
    async changePassword () {
      
      try {
        const token = this.$route.params.token;
        const formData = {
          newPassword: this.newPassword,
          token: token
        };
        const response = await fetch('http://localhost/raj-express/backend/controller/gmailController/reset_password.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(formData),
        });

        console.log('Response Status:', response.status); 
        if (!response.ok) {
          throw new Error(`Network response was not ok. Status: ${response.status}`);
        }

        const data = await response.json();
        console.log(data);
      } catch (error) {
        console.error('There was a problem with the fetch operation:', error);
      }
    },

    goToResetPassword() {
      this.$router.push('/reset-password');
    },
    goToSignUp() {
      this.$router.push('/register');
    },
  },
};
</script>

