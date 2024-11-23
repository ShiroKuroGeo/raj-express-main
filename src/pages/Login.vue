<template>
  <q-layout>
    <q-page-container>
      <q-page class="flex flex-center">
        <div class="q-pa-md q-ma-md" style="max-width: 500px; width: 100%">
          <div class="q-card-section row justify-center">
            <q-img src="/icons/logo.png" alt="Logo" class="circle-img"></q-img>
          </div>

          <div class="q-card-section">
            <q-form class="q-gutter-md" @submit.prevent="login">
              <p style="margin-bottom: 0">Email</p>
              <q-input
                v-model="email"
                label="Email"
                type="email"
                dense
                outlined
                style="margin-top: 0"
              ></q-input>

              <p style="margin-bottom: 0">Password</p>
              <q-input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                dense
                outlined
                style="margin-top: 0"
              >
                <template v-slot:append>
                  <q-icon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    @click="togglePasswordVisibility"
                    class="cursor-pointer"
                  />
                </template>
              </q-input>

              <div class="q-mt-md" style="font-style: italic">
                <p>
                  Forgot Password?
                  <q-btn flat @click="goToResetPassword">Click Here</q-btn>
                </p>
              </div>

              <div class="column items-center">
                <q-btn
                  type="submit"
                  label="Sign In"
                  color="primary"
                  class="q-mt-md"
                ></q-btn>
              </div>

              <div class="q-mt-md" style="font-style: italic">
                <p>
                  Don't have an account?
                  <q-btn flat @click="goToSignUp">Click Here</q-btn>
                </p>
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import axios from 'axios';

export default {
  name: 'SignInPage',
  data(){
    return{
      email: '',
      password: '',
      showPassword: ''
    }
  },
  methods:{
    async login() {

      try {
        const loginData = { email: this.email, password: this.password };

        const response = await axios.post('http://localhost/raj-express/backend/auth/signin.php', loginData, {
          headers: {
            'Content-Type': 'application/json',
          },
        });

        if (response.data.success) {
          this.$q.notify({
            type: 'positive',
            message: 'Login successful!',
          });

          localStorage.setItem('customer', JSON.stringify({
            id: response.data.user_id,
            firstName: response.data.first_name,
            lastName: response.data.last_name,
            userType: response.data.user_type,
          }));
          
          localStorage.setItem('token', response.data.user_id);

          // Redirect based on user type
          if (response.data.user_type === 'admin') {
            this.$router.push('/admin-dashboard');
          } else if (response.data.user_type === 'customer') {
            this.$router.push('/home');
          } else {
            this.$q.notify({
              type: 'warning',
              message: 'Unknown user type. Please contact support.',
            });
          }

        } else {
          throw new Error(response.data.error || 'Login failed');
        }
      } catch (error) {
        // Error handling with notifications
        if (error.response) {
          this.$q.notify({
            type: 'negative',
            message: error.response.data.error || 'An error occurred during login. Please try again.',
          });
        } else if (error.request) {
          this.$q.notify({
            type: 'negative',
            message: 'No response received from the server. Please try again later.',
          });
        } else {
          this.$q.notify({
            type: 'negative',
            message: 'An error occurred while setting up the request. Please try again.',
          });
        }
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
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

